<?php

class RecipeSchema
{
    public $id = null;
    public $name = null;
    public $nameFR = null;
    public $author = null;
    public array $without = [];
    public array $ingredients = [];
    public array $ingredientsFR = [];
    public array $steps = [];
    public array $stepsFR = [];
    public array $timers = [];
    public $imageURL;
    public $originalURL;
    public $likes = 0;
    public $status = "draft";
    public array $comments = [];
    public array $photos = [];
    public $total_time = 0;

    private JSONHandler $json_handler;

    public function __construct(JSONHandler $json_handler)
    {
        $this->json_handler = $json_handler;
    }
    /**
     * @return array     */
    public function search(): array {}
    /**
     * @return array     */
    public function getById(string $recipe_id): array {}
    /**
     * @return array     */
    public function create(): array {}
    /**
     * @return array     */
    public function update(string $recipe_id): array {}
    /**
     * @return array     */
    public function delete(string $recipe_id): array {}
    /**
     * @return array     */
    public function like(string $recipe_id): array {}
    /**
     * @return array     */
    public function translate(string $recipe_id): array {}
    /**
     * @return array     */
    public function setPhoto(string $recipe_id): array {}
}
