<?php

define("API_BASE_PATH", ".");

spl_autoload_register(function ($class) {
    error_log("Attempting to autoload: " . $class);

    $classFile = null;

    if (str_ends_with($class, "Exception")) {
        $classFile = API_BASE_PATH . "/utils/errors.php";
    } elseif (str_ends_with($class, "Controller")) {
        $baseName = strtolower(substr($class, 0, -10));
        $classFile =
            API_BASE_PATH . "/controllers/" . $baseName . "_controller.php";
    } elseif (str_ends_with($class, "Schema")) {
        $baseName = strtolower(substr($class, 0, -6));
        $classFile = API_BASE_PATH . "/models/" . $baseName . "_schema.php";
    } else {
        $map = [
            "JSONHandler" => API_BASE_PATH . "/utils/json_handler.php",
            "Utils" => API_BASE_PATH . "/utils/utils.php",
            "Session" => API_BASE_PATH . "/utils/session.php",
            "Validator" => API_BASE_PATH . "/utils/validator.php",
            "Router" => API_BASE_PATH . "/router.php",
        ];

        if (isset($map[$class])) {
            $classFile = $map[$class];
        }
    }

    if ($classFile !== null && file_exists($classFile)) {
        require_once $classFile;
    } else {
        error_log(
            "Could not load file for class: " .
                $class .
                " (looked for: " .
                $classFile .
                ")"
        );
    }
});

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error: [$errno] $errstr - $errfile:$errline");
    http_response_code(500);
    header("Content-Type: application/json");
    echo json_encode(["error" => "Server Error: $errstr"]);
    exit();
});

set_exception_handler(function ($e) {
    error_log(
        "Uncaught Exception: " .
            $e->getMessage() .
            " in " .
            $e->getFile() .
            ":" .
            $e->getLine()
    );
    http_response_code(500);
    header("Content-Type: application/json");
    echo json_encode(["error" => "Server Exception: " . $e->getMessage()]);
    exit();
});

Session::start();

if (getenv("APP_ENV") !== "production") {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
} else {
    error_reporting(0);
    ini_set("display_errors", 0);
}

Router::dispatch();
