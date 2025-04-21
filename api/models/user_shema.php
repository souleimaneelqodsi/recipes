<?php

class UserSchema
{
    public $id;
    public $username;
    public $email;
    public $role = "Cuisiner";
    public $created_at;
    public array $recipes = [];
    public array $comments = [];
    public array $photos = [];

    private $json_handler;

    public function __construct($json_handler)
    {
        $this->json_handler = $json_handler;
    }

    public function getAll() {}
    public function getUserById($id) {}
    public function update($data) {}
    public function updateRole($role) {}
}
