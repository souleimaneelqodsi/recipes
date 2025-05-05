<?php class CommentController implements BaseController
{
    private $comment_schema;
    private $user_schema;

    public function __construct(
        CommentSchema $comment_schema,
        UserSchema $user_schema
    ) {
        $this->comment_schema = $comment_schema;
        $this->user_schema = $user_schema;
    }

    public function create(string $recipe_id): void
    {
        try {
            if (!Session::isLoggedIn()) {
                http_response_code(401);
                header("Content-Type: application/json");
                echo json_encode([
                    "error" => "You must be logged in to create a comment",
                ]);
                return;
            }
            if (empty($recipe_id)) {
                http_response_code(400);
                header("Content-Type: application/json");
                echo json_encode(["error" => "Recipe ID is required"]);
                return;
            }
            $data = Utils::getJSONBody();
            if (!isset($data["content"]) || empty($data["content"])) {
                http_response_code(400);
                header("Content-Type: application/json");
                echo json_encode(["error" => "Comment is required"]);
                return;
            }
            $comment = $this->comment_schema->create(
                $recipe_id,
                $data["content"]
            );
            $comment_usr = $this->user_schema->addComment($comment);
            if (empty($comment)) {
                http_response_code(404);
                header("Content-Type: application/json");
                echo json_encode(["error" => "Recipe not found"]);
                return;
            }
            if (empty($comment_usr)) {
                http_response_code(404);
                header("Content-Type: application/json");
                echo json_encode(["error" => "User not found"]);
                return;
            }
            http_response_code(201);
            header("Content-Type: application/json");
            echo json_encode($comment);
        } catch (Exception $e) {
            http_response_code(500);
            header("Content-Type: application/json");
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    #[\Override]
    public function dispatch($method, array $path): void
    {
        if (empty($path) || !isset($method)) {
            http_response_code(400);
            header("Content-Type: application/json");
            echo json_encode(["error" => "Invalid request"]);
            return;
        }

        if (
            count((array) $path) != 2 ||
            $path[1] !== "comments" ||
            $path[0] === ""
        ) {
            http_response_code(400);
            header("Content-Type: application/json");
            echo json_encode(["error" => "Bad request"]);
            return;
        }

        $recipe_id = $path[0];
        if ($method === "POST") {
            $this->create($recipe_id);
        } else {
            http_response_code(405);
            header("Content-Type: application/json");
            echo json_encode(["error" => "Method not allowed"]);
        }
    }
}
