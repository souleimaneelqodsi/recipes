<?php

class AuthSchema
{
    private JSONHandler $json_handler;
    private UserSchema $user_model;

    public function __construct(
        JSONHandler $json_handler,
        UserSchema $user_schema
    ) {
        $this->json_handler = $json_handler;
        $this->user_model = $user_schema;
    }
    /**
     * @return array
     */
    public function login(): array {}
    /**
     * @return array
     */
    public function register(): array {}
    /**
     * @return array
     */
    public function logout(): array {}
}
