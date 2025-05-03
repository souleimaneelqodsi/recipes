<?php

class AuthSchema
{
    private JSONHandler $json_handler;

    public function __construct(JSONHandler $json_handler)
    {
        $this->json_handler = $json_handler;
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
    public function register(): array
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
