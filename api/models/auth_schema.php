<?php

class AuthSchema
{
    /**
     * @return string
     */
    public function register(string $password): string
    {
        if (!Validator::validatePassword($password)) {
            throw new InvalidPasswordException();
        }
        return password_hash($password, PASSWORD_DEFAULT);
    }
    /**
     * @return void
     * @param mixed $password
     * @param mixed $hash
     */
    public function login(string $password, string $hash): void
    {
        if (!password_verify($password, $hash)) {
            throw new IncorrectPasswordException();
        }
    }
}
