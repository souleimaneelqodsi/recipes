<?php

class PhotoSchema
{
    public $id;
    public $url;
    public $user_id;
    public $is_main;
    public $created_at;

    private JSONHandler $json_handler;

    public function __construct(JSONHandler $json_handler)
    {
        $this->json_handler = $json_handler;
    }

    public function upload(string $recipe_id): void {}
}
