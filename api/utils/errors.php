<?php

class UsernameAlreadyExistsException extends Exception
{
    public function __construct(
        $message = "Username already exists",
        $code = 409
    ) {
        parent::__construct($message, $code);
    }
}

class EmailAlreadyExistsException extends Exception
{
    public function __construct($message = "Email already exists", $code = 409)
    {
        parent::__construct($message, $code);
    }
}
