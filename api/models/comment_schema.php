<?php

class CommentSchema
{
    public $user_id = null;
    public $username = null;
    public $content = null;
    public $created_at = null;

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
            $recipes = $this->recipe_schema->getPublished();
            $recipe_index = array_search(
                $recipe_id,
                array_column($recipes, "id")
            );
            if ($recipe_index === false) {
                error_log("Recipe not found");
                return [];
            }
            $current_usr = Session::getCurrentUser();

            $data = [
                "id" => Utils::uuid4(),
                "user_id" => Session::getCurrentUser()->getId(),
                "username",
            ];
            return [];
        } catch (Exception $e) {
            error_log("Comment creation error:" . $e->getMessage());
            throw $e;
        }
    }
    /**
     * @return array
     */
    public function fetch(string $recipe_id): array
    {
        return [];
    }
}
