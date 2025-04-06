<?php

spl_autoload_register(function ($class) {
    $lowerClass = strtolower($class);
    if (str_contains($class, 'Controller')) {
        require_once 'controllers/' . substr($lowerClass, 0, strpos($lowerClass, "controller")) . "_controller.php";
        return;
    }
    if (str_contains($lowerClass, 'schema')) {
        require_once 'models/' . substr($lowerClass, 0, strpos($lowerClass, "schema")) . "_schema.php";
        return;
    }
    switch ($class) {
        case "JsonHandler":
            require_once "utils/json_handler.php";
            break;
        case "Session":
            require_once "utils/session.php";
            break;
        case "Validator":
            require_once "utils/validator.php";
            break;
        case "Route":
            require_once "route.php";
            break;
        case "Router":
            require_once "router.php";
            break;
    }
});

Session::start();

if (getenv('APP_ENV') !== 'production') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}


$router = new Router();

