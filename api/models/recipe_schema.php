<?php

class RecipeSchema
{
    public $id = null;
    public $name = null;
    public $nameFR = null;
    public $author = null;
    public array $without = [];
    public array $ingredients = [];
    public array $steps = [];
    public array $stepsFR = [];
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
    public function search(): array {}
    public function getById(int $recipe_id): array {}
    public function create(): array {}
    public function update(int $recipe_id): array {}
    public function delete(int $recipe_id): array {}
    public function like(int $recipe_id): array {}
    public function translate(int $recipe_id): array {}
    public function setPhoto(int $recipe_id): array {}
}
