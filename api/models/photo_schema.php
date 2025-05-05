<?php

class PhotoSchema
{
    public $id;
    public $url;
    public $user_id;
    public $is_main;
    public $created_at;

    private const DATA_FILE = "recipes.json";
    private JSONHandler $json_handler;
    private RecipeSchema $recipe_schema;

    public function __construct(
        JSONHandler $json_handler,
        RecipeSchema $recipe_schema
    ) {
        $this->json_handler = $json_handler;
        $this->recipe_schema = $recipe_schema;
    }

    public function upload(string $recipe_id, string $url): array
    {
        try {
            if (empty($recipe_id)) {
                throw new Exception("Recipe ID is empty");
            }
            $url = strtolower(trim($url));
            if (
                empty($url) ||
                filter_var($url, FILTER_VALIDATE_URL) === false
            ) {
                throw new Exception("Photo upload: Invalid URL");
            }

            $image_extensions = ["jpg", "jpeg", "png", "gif", "webp"];
            $url_path = parse_url($url, PHP_URL_PATH);
            $extension = pathinfo($url_path, PATHINFO_EXTENSION);
            if (!in_array(strtolower($extension), $image_extensions)) {
                throw new Exception("URL does not point to a valid image type");
            }
            $all_recipes = $this->recipe_schema->getAll();
            $recipe_index = array_search(
                $recipe_id,
                array_column($all_recipes, "id")
            );
            if ($recipe_index === false) {
                error_log("Recipe not found");
                return [];
            }
            $recipe = $all_recipes[$recipe_index];
            $current_usr = Session::getCurrentUser();
            $new_photo = [
                "id" => Utils::uuid4(),
                "url" => $url,
                "user_id" => $current_usr->getId(),
                "is_main" => empty($recipe["photos"]),
                "created_at" => time(),
            ];
            $recipe["photos"][] = $new_photo;
            if (empty($recipe["photos"])) {
                $recipe["imageURL"] = $url;
            }
            $all_recipes[$recipe_index] = $recipe;
            $this->json_handler->writeData(self::DATA_FILE, $all_recipes);
            return $new_photo;
        } catch (Exception $e) {
            error_log(
                "Error uploading photo for " .
                    $recipe_id .
                    ": " .
                    $e->getMessage()
            );
            throw $e;
        }
    }
}
