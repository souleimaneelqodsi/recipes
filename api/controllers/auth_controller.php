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
            if (
                !isset($_POST["username"]) ||
                !isset($_POST["email"]) ||
                !isset($_POST["password"]) ||
                empty($_POST["username"]) ||
                empty($_POST["email"]) ||
                empty($_POST["password"])
            ) {
                http_response_code(400);
                header("Content-Type: application/json");
                echo json_encode(["error" => "Missing required fields"]);
                return;
            }
            $username = $_POST["username"];
            $email = strtolower($_POST["email"]);
            $password = $_POST["password"];
            if (!Validator::validateUsername($username)) {
                throw new InvalidUsernameException();
            }
            if (!Validator::validateEmail($email)) {
                throw new InvalidEmailException();
            }
            $this->user_schema->duplicate_handler($username, $email);
            $hashed_password = $this->auth_schema->register($password);
            $this->user_schema->create($username, $email, $hashed_password);
            Session::set("user_id", $this->user_schema->getId());
            Session::set("username", $this->user_schema->getUsername());
            Session::set("email", $this->user_schema->getEmail());
            Session::set("role", $this->user_schema->getRole());
            http_response_code(201);
            header("Content-Type: application/json");
            echo json_encode($this->user_schema->toArray());
        } catch (Exception $e) {
            http_response_code($e->getCode() !== 0 ? $e->getCode() : 400);
            header("Content-Type: application/json");
            echo json_encode(["error" => $e->getMessage()]);
            return;
        }
    }
    public function login(): void {}
    public function logout(): void {}

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
            if (count((array) $path) == 1) {
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
                        http_response_code(404);
                        header("Content-Type: application/json");
                        echo json_encode(["error" => "Not found"]);
                        break;
                }
            } else {
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
