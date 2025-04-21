<?php class CommentController implements Controller
{
    private $comment_schema;

    public function __construct(CommentSchema $comment_schema)
    {
        $this->comment_schema = $comment_schema;
    }

    public function create($recipe_id): void {}
    public function fetch($recipe_id): void {}

    #[\Override]
    public function dispatch($method, array $path): void
    {
        if (!isset($path) || !isset($method)) {
            http_response_code(400);
            header("Content-Type: application/json");
            echo json_encode(["error" => "Invalid request"]);
            return;
        }

        if (
            filter_var($path[0], FILTER_VALIDATE_INT) === false ||
            count($path) != 2 ||
            $path[1] !== "comments"
        ) {
            http_response_code(400);
            header("Content-Type: application/json");
            echo json_encode(["error" => "Bad request"]);
            return;
        }

        $recipe_id = $path[0];
        if ($method === "GET") {
            $this->fetch($recipe_id);
        } elseif ($method === "POST") {
            $this->create($recipe_id);
        } else {
            http_response_code(405);
            header("Content-Type: application/json");
            echo json_encode(["error" => "Method not allowed"]);
        }
    }
}
