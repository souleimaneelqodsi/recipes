<?php class UserController implements Controller
{
    private $user_schema;

    public function __construct($user_schema)
    {
        $this->user_schema = $user_schema;
    }
    #[\Override]
    public function dispatch($method, array $path): void {}

    public function getAll(): void {}
    public function create(array $data): void {}
    public function update(int $id, array $data): void {}
    public function getBydId($user_id): void {}
}
