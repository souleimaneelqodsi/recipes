<?php
class Router
{
    private static function not_found(): void
    {
        http_response_code(404);
        header("Content-Type: application/json");
        echo json_encode(["error" => "Not found"]);
    }

    public static function dispatch(): void
    {
        $method = $_SERVER["REQUEST_METHOD"];
        $uri = $_SERVER["REQUEST_URI"];
        // méthode fragile :
        // $uri = str_replace("recipes/api/", "", $uri);
        // $path = explode("/", $uri);
        $requestPath = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
        $uri = str_replace(API_BASE_PATH, "", $requestPath);
        $clean_uri = trim($uri, "/");
        $path = explode("/", $clean_uri);
        $json_handler = new JSONHandler(__DIR__ . "/data");
        $sliced_path = array_slice($path, 1);
        switch ($path[0]) {
            case "recipes":
                if (isset($path[2]) && !empty($path[2])) {
                    if ($path[2] === "comments") {
                        $comments_model = new CommentSchema($json_handler);
                        $comments_controller = new CommentController(
                            $comments_model
                        );
                        $comments_controller->dispatch($method, $sliced_path);
                    } elseif ($path[2] === "photos") {
                        $photos_model = new PhotoSchema($json_handler);
                        $photos_controller = new PhotoController($photos_model);
                        $photos_controller->dispatch($method, $sliced_path);
                    } else {
                        Router::not_found();
                    }
                    break;
                }
                $recipe_model = new RecipeSchema($json_handler);
                $recipe_controller = new RecipeController($recipe_model);
                $recipe_controller->dispatch($method, $sliced_path);
                break;
            case "users":
                $user_model = new UserSchema($json_handler);
                $user_controller = new UserController($user_model);
                $user_controller->dispatch($method, $sliced_path);
                break;
            case "auth":
                $user_model = new UserSchema($json_handler);
                $auth_model = new AuthSchema($json_handler, $user_model);
                $auth_controller = new AuthController($auth_model);
                $auth_controller->dispatch($method, $sliced_path);
                break;
            default:
                Router::not_found();
                break;
        }
    }
}
