<?php

spl_autoload_register(function ($class) {
    error_log("Attempting to autoload: " . $class);

    $classFile = null;

    if (str_ends_with($class, "Controller")) {
        $baseName = strtolower(substr($class, 0, -10));
        $classFile = __DIR__ . "/controllers/" . $baseName . "_controller.php";
    } elseif (str_ends_with($class, "Schema")) {
        $baseName = strtolower(substr($class, 0, -6));
        $classFile = __DIR__ . "/models/" . $baseName . "_schema.php";
    } else {
        $map = [
            "JSONHandler" => __DIR__ . "/utils/json_handler.php",
            "Utils" => __DIR__ . "/utils/utils.php",
            "Session" => __DIR__ . "/utils/session.php",
            "Validator" => __DIR__ . "/utils/validator.php",
            "Router" => __DIR__ . "/router.php",
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

define("API_BASE_PATH", dirname($_SERVER["SCRIPT_NAME"]));

Session::start();

if (getenv("APP_ENV") !== "production") {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
} else {
    error_reporting(0);
    ini_set("display_errors", 0);
}

Router::dispatch();
