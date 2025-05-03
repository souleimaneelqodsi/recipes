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

class InvalidEmailException extends Exception
{
    public function __construct($message = "Invalid email", $code = 400)
    {
        parent::__construct($message, $code);
    }
}

class InvalidUsernameException extends Exception
{
    public function __construct(
        $message = "Invalid username: username has to contain only letters, numbers and underscores and has to be between 5 and 20 characters long",
        $code = 400
    ) {
        parent::__construct($message, $code);
    }
}

class InvalidPasswordException extends Exception
{
    public function __construct(
        $message = "Invalid password: password has to contain at least one uppercase letter, one lowercase letter, one number and has to be at least 8 characters long",
        $code = 400
    ) {
        parent::__construct($message, $code);
    }
}

class IncorrectPasswordException extends Exception
{
    public function __construct(
        $message = "Invalid credentials: Incorrect password",
        $code = 401
    ) {
        parent::__construct($message, $code);
    }
}

class IncorrectUsernameException extends Exception
{
    public function __construct(
        $message = "Invalid credentials: Incorrect username",
        $code = 401
    ) {
        parent::__construct($message, $code);
    }
}
