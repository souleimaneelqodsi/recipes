<?php class UserController implements BaseController
{
    private UserSchema $user_schema;

    public function __construct(UserSchema $user_schema)
    {
        $this->user_schema = $user_schema;
    }

    public function getAll(): void {}
    public function getById(string $user_id): void {}
    public function create(): void {}
    public function update(string $user_id): void {}
    public function updateRole(string $user_id): void {}

    #[\Override]
    public function dispatch($method, array $path): void
    {
        if (!isset($path) || !isset($method)) {
            http_response_code(400);
            header("Content-Type: application/json");
            echo json_encode(["error" => "Invalid request"]);
            return;
        }
        if ($method === "GET") {
            if (empty($path)) {
                $this->getAll();
            } else {
                if (count((array)$path) == 1 && $path[0] !== "") {
                    $this->getById($path[0]);
                } else {
                    http_response_code(400);
                    header("Content-Type: application/json");
                    echo json_encode(["error" => "Invalid user ID"]);
                    return;
                }
            }
        } elseif ($method === "POST") {
            if (empty($path)) {
                $this->create();
            } else {
                http_response_code(400);
                header("Content-Type: application/json");
                echo json_encode(["error" => "Invalid request"]);
                return;
            }
        } elseif ($method === "PUT") {
            if (count((array)$path) == 1 && $path[0] !== "") {
                $this->update($path[0]);
                return;
            }
            if ($path[0] !== "" && count((array)$path) == 2 && $path[1] === "role") {
                $this->updateRole($path[0]);
                return;
            }
            http_response_code(400);
            header("Content-Type: application/json");
            echo json_encode(["error" => "Invalid request"]);
        } else {
            http_response_code(405);
            header("Content-Type: application/json");
            echo json_encode(["error" => "Method not allowed"]);
            return;
        }
    }
}
