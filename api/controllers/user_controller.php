<?php class UserController implements BaseController
{
    private UserSchema $user_schema;

    public function __construct(UserSchema $user_schema)
    {
        $this->user_schema = $user_schema;
    }

    public function getAll(): void
    {
        try {
            $users = $this->user_schema->getAll();
            if (empty($users)) {
                http_response_code(204);
                header("Content-Type: application/json");
                echo json_encode([]);
                return;
            }
            http_response_code(200);
            header("Content-Type: application/json");
            echo json_encode($users);
        } catch (Exception $e) {
            http_response_code(500);
            header("Content-Type: application/json");
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    public function getById(string $user_id): void
    {
        try {
            $usr = $this->user_schema->getById($user_id);
            if (empty($usr)) {
                http_response_code(404);
                header("Content-Type: application/json");
                echo json_encode(["error" => "User not found"]);
                return;
            }
            http_response_code(200);
            header("Content-Type: application/json");
            echo json_encode($usr);
        } catch (Exception $e) {
            http_response_code(500);
            header("Content-Type: application/json");
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    public function updateRole(string $user_id, string $role): void
    {
        try {
            $updated_usr = $this->user_schema->updateRole($user_id, $role);
            if (empty($updated_usr)) {
                http_response_code(404);
                header("Content-Type: application/json");
                echo json_encode(["error" => "User not found"]);
                return;
            }
            http_response_code(200);
            header("Content-Type: application/json");
            echo json_encode(["message" => "User role updated"]);
        } catch (Exception $e) {
            http_response_code(500);
            header("Content-Type: application/json");
            echo json_encode(["error" => $e->getMessage()]);
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
        if ($method === "GET") {
            if (empty($path)) {
                $this->getAll();
            } else {
                if (count((array) $path) == 1 && $path[0] !== "") {
                    $this->getById($path[0]);
                } else {
                    http_response_code(400);
                    header("Content-Type: application/json");
                    echo json_encode(["error" => "Invalid user ID"]);
                    return;
                }
            }
        } elseif ($method === "PATCH") {
            $data = Utils::getJSONBody();
            if ($data === null) {
                http_response_code(400);
                header("Content-Type: application/json");
                echo json_encode(["error" => "Bad request"]);
                return;
            }
            if (
                $path[0] !== "" &&
                count((array) $path) == 2 &&
                $path[1] === "role"
            ) {
                $this->updateRole($path[0], $data["role"]);
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
