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
        // Log entry into the function
        error_log("REGISTER_DEBUG: Attempting user registration process.");

        try {
            error_log(
                "REGISTER_DEBUG: Checking if user is already logged in via Session::isLoggedIn()."
            );
            if (Session::isLoggedIn()) {
                // Log condition met and action
                error_log(
                    "REGISTER_DEBUG: User is already logged in. Aborting registration with 400 error."
                );
                http_response_code(400);
                header("Content-Type: application/json");
                echo json_encode([
                    "error" => "User already logged in",
                ]);
                return; // Exit function
            }
            error_log("REGISTER_DEBUG: User is not logged in. Proceeding.");

            // Log check for required fields
            error_log(
                "REGISTER_DEBUG: Checking existence and emptiness of POST fields: username, email, password."
            );
            $data = Utils::getJSONBody();
            if (
                !isset($data["username"]) ||
                !isset($data["email"]) ||
                !isset($data["password"]) ||
                empty($data["username"]) ||
                empty($data["email"]) ||
                empty($data["password"])
            ) {
                // Log condition met and action
                error_log(
                    "REGISTER_DEBUG: One or more required fields are missing or empty. Aborting registration with 400 error."
                );
                http_response_code(400);
                header("Content-Type: application/json");
                echo json_encode(["error" => "Missing required fields"]);
                return; // Exit function
            }
            error_log(
                "REGISTER_DEBUG: All required POST fields are present and not empty."
            );

            // Assign variables and log received data (mask password)
            $username = $data["username"];
            $email = strtolower($data["email"]);
            $password = $data["password"];
            // WARNING: Avoid logging raw passwords in production environments! Redacted here for safety.
            error_log(
                "REGISTER_DEBUG: Received fields processed: username='{$username}', email='{$email}', password='[REDACTED]'"
            );

            // Log before username validation
            error_log(
                "REGISTER_DEBUG: Attempting username validation for '{$username}'."
            );
            if (!Validator::validateUsername($username)) {
                // Log validation failure
                error_log(
                    "REGISTER_DEBUG: Username '{$username}' failed validation."
                );
                throw new InvalidUsernameException(); // Throw specific exception
            }
            // Log validation success
            error_log(
                "REGISTER_DEBUG: Username '{$username}' passed validation."
            );

            // Log before email validation
            error_log(
                "REGISTER_DEBUG: Attempting email validation for '{$email}'."
            );
            if (!Validator::validateEmail($email)) {
                // Log validation failure
                error_log(
                    "REGISTER_DEBUG: Email '{$email}' failed validation."
                );
                throw new InvalidEmailException(); // Throw specific exception
            }
            // Log validation success
            error_log("REGISTER_DEBUG: Email '{$email}' passed validation.");

            // Log before duplicate check
            error_log(
                "REGISTER_DEBUG: Checking for duplicate username/email via user_schema->duplicate_handler()."
            );
            $this->user_schema->duplicate_handler($username, $email);
            // Log duplicate check success
            error_log(
                "REGISTER_DEBUG: Duplicate check passed (no existing user found with same username/email)."
            );

            // Log before password hashing
            error_log(
                "REGISTER_DEBUG: Hashing password via auth_schema->register()."
            );
            $hashed_password = $this->auth_schema->register($password);
            // Log password hashing success
            error_log(
                "REGISTER_DEBUG: Password hashing completed successfully."
            );

            // Log before user creation in data store
            error_log(
                "REGISTER_DEBUG: Creating user record via user_schema->create() with username='{$username}', email='{$email}'."
            );
            // Assuming user_schema->create returns the created user array, including the ID
            $createdUserData = $this->user_schema->create(
                $username,
                $email,
                $hashed_password
            );
            if (empty($createdUserData) || !isset($createdUserData["id"])) {
                error_log(
                    "REGISTER_ERROR: UserSchema->create did not return expected user data or ID."
                );
                throw new Exception("User creation failed internally.", 500);
            }
            $newUserId = $createdUserData["id"]; // Get the actual ID returned
            // Log user creation success
            error_log(
                "REGISTER_DEBUG: User record created successfully. New User ID: {$newUserId}"
            );

            // Log before setting session variables
            // Session::start(); // REMOVED - Should be started globally in index.php
            error_log(
                "REGISTER_DEBUG: Setting session variables: user_id, username, email, role for User ID: {$newUserId}."
            );
            Session::set("user_id", $newUserId); // Use the definite ID
            Session::set("username", $createdUserData["username"]); // Use data returned from create
            Session::set("email", $createdUserData["email"]);
            Session::set("role", $createdUserData["role"]);
            // Log session setting success
            error_log("REGISTER_DEBUG: Session variables set successfully.");

            // Log before sending final success response
            error_log(
                "REGISTER_DEBUG: Preparing successful response (201 Created)."
            );
            http_response_code(201);
            header("Content-Type: application/json");
            // Use the data returned by create() for the response body for consistency
            $responseBody = json_encode($createdUserData);
            error_log(
                "REGISTER_DEBUG: Sending JSON response body: {$responseBody}"
            );
            echo $responseBody;
            error_log(
                "REGISTER_DEBUG: Registration process completed successfully and response sent."
            );
        } catch (Exception $e) {
            // Log the caught exception details
            $errorCode = $e->getCode() !== 0 ? $e->getCode() : 500; // Use exception code or default to 500
            error_log(
                "REGISTER_ERROR: Exception caught during registration. Code: {$errorCode}, Message: " .
                    $e->getMessage()
            );
            // Optional: Log stack trace for detailed debugging
            // error_log("REGISTER_ERROR: Stack Trace: " . $e->getTraceAsString());

            // Ensure headers aren't sent before setting the status code if possible
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

            // Send JSON error response
            $errorResponse = json_encode(["error" => $e->getMessage()]);
            error_log(
                "REGISTER_ERROR: Sending JSON error response: {$errorResponse}"
            );
            echo $errorResponse;
            return; // Exit after handling the error
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
            Session::set("role", "Administrateur");
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
