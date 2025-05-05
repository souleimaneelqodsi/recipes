<?php
class Router
{
    private static function not_found(): void
    {
        error_log("Could not parse the path: bad endpoint");
        http_response_code(400);
        header("Content-Type: application/json");
        echo json_encode(["error" => "Endpoint not found"]);
    }

    public static function dispatch(): void
    {
        $method = $_SERVER["REQUEST_METHOD"];
        $uri = $_SERVER["REQUEST_URI"];
        $requestPath = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
        $uri = str_replace(API_BASE_PATH, "", $requestPath);
        $clean_uri = trim($uri, "/");
        $path = explode("/", $clean_uri);
        $path = array_slice($path, 2);
        $json_handler = new JSONHandler(API_BASE_PATH . "/data");
        $sliced_path = array_slice($path, 1);
        $user_model = null;
        if (Session::isLoggedIn()) {
            $user_model = new UserSchema(
                $json_handler,
                Session::get("username"),
                Session::get("email")
            );
        } else {
            $user_model = new UserSchema(
                $json_handler,
                "guest",
                "guest@example.com"
            );
        }
        switch ($path[0]) {
            case "recipes":
                if (isset($path[2]) && !empty($path[2])) {
                    if ($path[2] === "comments") {
                        $recipe_schema = new RecipeSchema($json_handler);
                        $comments_model = new CommentSchema(
                            $json_handler,
                            $recipe_schema
                        );
                        $user_model = new UserSchema(
                            $json_handler,
                            "guest",
                            "guest@example.com"
                        );
                        $comments_controller = new CommentController(
                            $comments_model,
                            $user_model
                        );
                        $comments_controller->dispatch($method, $sliced_path);
                        break;
                    } elseif ($path[2] === "photos" && $method === "POST") {
                        $recipe_schema = new RecipeSchema($json_handler);
                        $photos_model = new PhotoSchema(
                            $json_handler,
                            $recipe_schema
                        );
                        $photos_controller = new PhotoController(
                            $photos_model,
                            $user_model,
                            $recipe_schema
                        );
                        $photos_controller->dispatch($method, $sliced_path);
                        break;
                    } elseif ($path[2] === "photos" && $method === "PATCH") {
                        $recipe_model = new RecipeSchema($json_handler);
                        $photos_controller = new RecipeController(
                            $recipe_model,
                            $user_model
                        );
                        $photos_controller->dispatch($method, $sliced_path);
                        break;
                    } else {
                        self::not_found();
                    }
                }
                $recipe_model = new RecipeSchema($json_handler);
                $recipe_controller = new RecipeController(
                    $recipe_model,
                    $user_model
                );
                $recipe_controller->dispatch($method, $sliced_path);
                break;
            case "users":
                $user_controller = new UserController($user_model);
                $user_controller->dispatch($method, $sliced_path);
                break;
            case "auth":
                $auth_model = new AuthSchema();
                $user_model = new UserSchema(
                    $json_handler,
                    "guest",
                    "guest@example.com"
                );
                $auth_controller = new AuthController($auth_model, $user_model);
                $auth_controller->dispatch($method, $sliced_path);
                break;
            default:
                self::not_found();
                break;
        }
    }
}
