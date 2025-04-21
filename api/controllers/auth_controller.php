<?php class AuthController implements Controller
{
    private $auth_schema;

    public function __construct($auth_schema)
    {
        $this->auth_schema = $auth_schema;
    }
    #[\Override]
    public function dispatch($method, array $path): void {}

    public function login(): void {}
    public function register(): void {}
    public function logout(): void {}
}
