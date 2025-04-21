<?php class PhotoController implements Controller
{
    private PhotoSchema $photo_schema;

    public function __construct(PhotoSchema $photo_schema)
    {
        $this->photo_schema = $photo_schema;
    }
    public function upload(string $recipe_id): void {}

    #[\Override]
    public function dispatch($method, array $path): void
    {
        if (!isset($path) || !isset($method)) {
            http_response_code(400);
            header("Content-Type: application/json");
            echo json_encode(["error" => "Invalid request"]);
            return;
        }
        if ($method !== "POST") {
            http_response_code(405);
            header("Content-Type: application/json");
            echo json_encode(["error" => "Method not allowed"]);
            return;
        }
        if (count($path) != 2) {
            http_response_code(400);
            header("Content-Type: application/json");
            echo json_encode(["error" => "Invalid request"]);
            return;
        }
        if ($path[1] !== "photos" || $path[0] === "") {
            http_response_code(400);
            header("Content-Type: application/json");
            echo json_encode(["error" => "Invalid request"]);
            return;
        }
        $this->upload($path[0]);
    }
}
