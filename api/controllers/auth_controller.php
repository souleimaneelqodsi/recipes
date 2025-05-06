<?php class AuthController implements BaseController
{
    private $auth_schema;
    private $user_schema;

    public function __construct(
        AuthSchema $auth_schema,
        UserSchema $user_schema
    ) {
        $this->auth_schema = $auth_schema;
        $this->user_schema = $user_schema;
    }
    public function register(): void
    {
        try {
            if (Session::isLoggedIn()) {
                http_response_code(400);
                header("Content-Type: application/json");
                echo json_encode([
                    "error" => "User already logged in",
                ]);
                return;
            }
            $data = Utils::getJSONBody();
            if (
                !isset($data["username"]) ||
                !isset($data["email"]) ||
                !isset($data["password"]) ||
                empty($data["username"]) ||
                empty($data["email"]) ||
                empty($data["password"])
            ) {
                http_response_code(400);
                header("Content-Type: application/json");
                echo json_encode(["error" => "Missing required fields"]);
                return;
            }

            $username = $data["username"];
            $email = strtolower($data["email"]);
            $password = $data["password"];

            if (!Validator::validateUsername($username)) {
                throw new InvalidUsernameException();
            }

            if (!Validator::validateEmail($email)) {
                throw new InvalidEmailException();
            }

            $this->user_schema->duplicate_handler($username, $email);

            $hashed_password = $this->auth_schema->register($password);

            $createdUserData = $this->user_schema->create(
                $username,
                $email,
                $hashed_password
            );
            if (empty($createdUserData) || !isset($createdUserData["id"])) {
                throw new Exception("User creation failed internally.", 500);
            }
            $newUserId = $createdUserData["id"];

            Session::set("user_id", $newUserId);
            Session::set("username", $createdUserData["username"]);
            Session::set("email", $createdUserData["email"]);
            Session::set("roles", $createdUserData["roles"]);

            http_response_code(201);
            header("Content-Type: application/json");

            $responseBody = json_encode($createdUserData);
            echo $responseBody;
        } catch (Exception $e) {
            $errorCode = $e->getCode() !== 0 ? $e->getCode() : 500;
            error_log(
                "REGISTER_ERROR: Exception caught during registration. Code: {$errorCode}, Message: " .
                    $e->getMessage()
            );

            if (!headers_sent()) {
                error_log(
                    "REGISTER_ERROR: Setting HTTP response code to {$errorCode}."
                );
                http_response_code($errorCode);
                header("Content-Type: application/json");
            } else {
                error_log(
                    "REGISTER_ERROR: Headers already sent, cannot set response code {$errorCode}."
                );
            }

            $errorResponse = json_encode(["error" => $e->getMessage()]);
            error_log(
                "REGISTER_ERROR: Sending JSON error response: {$errorResponse}"
            );
            echo $errorResponse;
            return;
        }
    }
    public function login(): void
    {
        try {
            if (Session::isLoggedIn()) {
                http_response_code(400);
                header("Content-Type: application/json");
                echo json_encode([
                    "error" => "User already logged in",
                ]);
                return;
            }
            $data = Utils::getJSONBody();
            if (
                !isset($data["username"]) ||
                !isset($data["password"]) ||
                empty($data["username"]) ||
                empty($data["password"])
            ) {
                http_response_code(400);
                header("Content-Type: application/json");
                echo json_encode([
                    "error" => "Missing required fields: username and password",
                ]);
                return;
            }
            $username = $data["username"];
            $password = $data["password"];
            $user = $this->user_schema->getByUsername($username);
            if (empty($user)) {
                throw new IncorrectUsernameException();
            }
            $this->user_schema->fromArray($user);
            $hash = $this->user_schema->getPassword();
            $this->auth_schema->login($password, $hash);
            Session::start();
            Session::set("user_id", $this->user_schema->getId());
            Session::set("username", $this->user_schema->getUsername());
            Session::set("email", $this->user_schema->getEmail());
            Session::set("role", $this->user_schema->getRole());
            http_response_code(200);
            header("Content-Type: application/json");
            echo json_encode([
                "status" => "success",
                "userId" => $this->user_schema->getId(),
                "username" => $this->user_schema->getUsername(),
                "email" => $this->user_schema->getEmail(),
            ]);
        } catch (Exception $e) {
            http_response_code($e->getCode() !== 0 ? $e->getCode() : 500);
            header("Content-Type: application/json");
            echo json_encode(["error" => $e->getMessage()]);
            return;
        }
    }
    public function logout(): void
    {
        try {
            if (!Session::isLoggedIn()) {
                http_response_code(401);
                header("Content-Type: application/json");
                echo json_encode(["error" => "The user isn't logged in"]);
                return;
            }
            Session::destroy();
            http_response_code(200);
            header("Content-Type: application/json");
            echo json_encode(["status" => "user logged out successfully"]);
        } catch (Exception $e) {
            error_log("500 LOGOUT: failed to logout: " . $e->getMessage());
            http_response_code(500);
            header("Content-Type: application/json");
            echo json_encode(["error" => $e->getMessage()]);
            return;
        }
    }

    #[\Override]
    public function dispatch($method, array $path): void
    {
        if (!isset($path) || !isset($method)) {
            http_response_code(400);
            header("Content-Type: application/json");
            echo json_encode(["error" => "Invalid request"]);
            return;
        }
        if ($method === "POST") {
            if (count((array) $path) == 1 && $path !== "") {
                switch ($path[0]) {
                    case "register":
                        $this->register();
                        break;
                    case "login":
                        $this->login();
                        break;
                    case "logout":
                        $this->logout();
                        break;
                    default:
                        http_response_code(400);
                        header("Content-Type: application/json");
                        echo json_encode(["error" => "Auth: Bad request"]);
                        break;
                }
            } else {
                error_log(
                    "Auth: Invalid path -> the extracted URI param is 2-word long"
                );
                http_response_code(400);
                header("Content-Type: application/json");
                echo json_encode(["error" => "Invalid request"]);
            }
        } else {
            http_response_code(405);
            header("Content-Type: application/json");
            echo json_encode(["error" => "Method not allowed"]);
        }
    }
}
