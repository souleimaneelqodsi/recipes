<?php

class CommentSchema
{
    public $user_id = null;
    public $username = null;
    public $content = null;
    public $created_at = null;

    private JSONHandler $json_handler;

    public function __construct(JSONHandler $json_handler)
    {
        $this->json_handler = $json_handler;
    }
    /**
     * @return array
     */
    public function create(string $recipe_id): array {}
    /**
     * @return array
     */
    public function fetch(string $recipe_id): array {}
}
