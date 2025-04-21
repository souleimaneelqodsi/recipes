<?php

class CommentSchema
{
    public $user_id = null;
    public $username = null;
    public $content = null;
    public $created_at = null;
    private $json_handler;

    public function __construct($json_handler)
    {
        $this->json_handler = $json_handler;
    }

    public function create($data, $recipe_id): void {}
    public function fetch($recipe_id): array {}
}
