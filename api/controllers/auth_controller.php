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
    public function login(): void {}
    public function register(): void {}
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
