<?php

class PhotoSchema
{
    public $id;
    public $url;
    public $user_id;
    public $is_main;
    public $created_at;

    private $json_handler;

    public function __construct($json_handler)
    {
        $this->json_handler = $json_handler;
    }

    public function upload($recipe_id) {}
}
