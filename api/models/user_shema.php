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

    private JSONHandler $json_handler;

    public function __construct(JSONHandler $json_handler)
    {
        $this->json_handler = $json_handler;
    }
    /**
     * @return void
     */
    public function getAll(): array {}
    public function getById(string $user_id): void {}
    /**
     * @return void
     */
    public function create(): array {}
    public function update(string $user_id): void {}
    public function updateRole(string $user_id): void {}
}
