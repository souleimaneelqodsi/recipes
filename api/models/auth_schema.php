<?php

class AuthSchema
{
    private $json_handler;
    private $user_data;

    public function __construct($json_handler, $user_schema)
    {
        $this->json_handler = $json_handler;
        $this->user_data = $user_schema;
    }

    public function login() {}

    public function register() {}

    public function logout() {}
}
