<?php class PhotoController implements BaseController
{
    private PhotoSchema $photo_schema;
    private UserSchema $user_schema;
    private RecipeSchema $recipe_schema;

    public function __construct(
        PhotoSchema $photo_schema,
        UserSchema $user_schema,
        RecipeSchema $recipe_schema
    ) {
        $this->photo_schema = $photo_schema;
        $this->user_schema = $user_schema;
        $this->recipe_schema = $recipe_schema;
    }
    public function upload(string $recipe_id): void
    {
        try {
            if (!Session::isLoggedIn()) {
                http_response_code(401);
                header("Content-Type: application/json");
                echo json_encode([
                    "error" => "You must be logged in to upload photos",
                ]);
                return;
            }
            if (
                !$this->recipe_schema->isAuthor(
                    Session::getCurrentUser()->getId(),
                    $recipe_id
                ) &&
                Session::getUserRole() !== "Administrateur" &&
                Session::getUserRole() !== "Chef"
            ) {
                http_response_code(403);
                header("Content-Type: application/json");
                echo json_encode([
                    "error" => "You can only upload photos to your own recipes",
                ]);
                return;
            }

            $data = Utils::getJSONBody();
            if (!isset($data["url"])) {
                http_response_code(400);
                header("Content-Type: application/json");
                echo json_encode(["error" => "Missing URL"]);
                return;
            }
            $photo = $this->photo_schema->upload($recipe_id, $data["url"]);
            if (empty($photo)) {
                http_response_code(404);
                header("Content-Type: application/json");
                echo json_encode(["error" => "Photo upload: recipe not found"]);
                return;
            }
            $photo_usr = $this->user_schema->addPhoto($photo);
            if (empty($photo_usr)) {
                http_response_code(404);
                header("Content-Type: application/json");
                echo json_encode(["error" => "Photo upload: user not found"]);
                return;
            }
            http_response_code(201);
            header("Content-Type: application/json");
            echo json_encode($photo);
        } catch (Exception $e) {
            error_log(
                "Error uploading photo for " .
                    $recipe_id .
                    ": " .
                    $e->getMessage()
            );
            http_response_code(500);
            header("Content-Type: application/json");
            echo json_encode(["error" => "Internal server error"]);
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
        if ($method !== "POST") {
            http_response_code(405);
            header("Content-Type: application/json");
            echo json_encode(["error" => "Method not allowed"]);
            return;
        }
        if (count((array) $path) != 2) {
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
