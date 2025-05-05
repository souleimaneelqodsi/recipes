<?php

class CommentSchema
{
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
    /**
     * @return array
     */
    public function create(string $recipe_id, string $content): array
    {
        try {
            if (empty($content)) {
                throw new Exception("You cannot post an empty comment");
            }
            //sanitization
            $content = strip_tags($content);
            $content = htmlspecialchars($content, ENT_QUOTES, "UTF-8");
            $content = trim($content);
            $recipes_all = $this->recipe_schema->getAll();
            //could've used getPublished but this is better to avoid making two retrievals as the list of all recipes will be necessary to rewrite to the json file
            $recipes = array_filter(
                $recipes_all,
                fn($recp) => $recp["status"] === "published"
            );
            $recipe_index = array_search(
                $recipe_id,
                array_column($recipes, "id")
            );
            //this is made to verify that the recipe exists in the published recipes
            if ($recipe_index === false) {
                error_log("Recipe not found");
                return [];
            }
            //and now we search for the real index
            $recipe_index = array_search(
                $recipe_id,
                array_column($recipes_all, "id")
            );
            //you never know...
            if ($recipe_index === false) {
                error_log("Recipe not found");
                return [];
            }
            $current_usr = Session::getCurrentUser();
            $data = [
                "id" => Utils::uuid4(),
                "user_id" => $current_usr->getId(),
                "username" => $current_usr->getUsername(),
                "content" => $content,
                "created_at" => time(),
            ];
            $recipes_all[$recipe_index]["comments"][] = $data;
            $this->json_handler->writeData(self::DATA_FILE, $recipes);
            return $data;
        } catch (Exception $e) {
            error_log("Comment creation error:" . $e->getMessage());
            throw $e;
        }
    }
}
