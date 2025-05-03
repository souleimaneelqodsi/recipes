<?php

class AuthSchema
{
    private JSONHandler $json_handler;

    public function __construct(JSONHandler $json_handler)
    {
        $this->json_handler = $json_handler;
    }
    /**
     * @return string
     */
    public function register(string $password): string
    {
        if (!Validator::validatePassword($password)) {
            throw new InvalidPasswordException();
        }
        return password_hash($password, PASSWORD_DEFAULT);
        //sanitized input and hashed passsword
    }
    /**
     * @return array
     */
    public function login(): array
    {
        return [];
    }
    /**
     * @return array
     */
    public function logout(): array
    {
        return [];
    }
}
