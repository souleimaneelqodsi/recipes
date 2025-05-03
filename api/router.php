<?php
class Router
{
    private static function not_found(): void
    {
        http_response_code(400);
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
        error_log($clean_uri);
        $path = explode("/", $clean_uri);
        $path = array_slice($path, 2);
        error_log(json_encode($path));
        $json_handler = new JSONHandler(API_BASE_PATH . "/data");
        $sliced_path = array_slice($path, 1);
        error_log(json_encode($sliced_path));
        $user_model = new UserSchema(
            $json_handler,
            Session::get("username"),
            Session::get("email")
        );
        switch ($path[0]) {
            case "recipes":
                if (isset($path[2]) && !empty($path[2])) {
                    if ($path[2] === "comments") {
                        $comments_model = new CommentSchema($json_handler);
                        $comments_controller = new CommentController(
                            $comments_model
                        );
                        $comments_controller->dispatch($method, $sliced_path);
                        break;
                    } elseif ($path[2] === "photos" && $method === "POST") {
                        $photos_model = new PhotoSchema($json_handler);
                        $photos_controller = new PhotoController($photos_model);
                        $photos_controller->dispatch($method, $sliced_path);
                        break;
                    } elseif ($path[2] === "photos" && $method === "PATCH") {
                        $user_schema = new UserSchema(
                            $json_handler,
                            Session::get("username"),
                            Session::get("email")
                        );
                        $recipe_model = new RecipeSchema($json_handler);
                        $photos_controller = new RecipeController(
                            $recipe_model,
                            $user_schema
                        );
                        $photos_controller->dispatch($method, $sliced_path);
                        break;
                    } else {
                        Router::not_found();
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
                $auth_model = new AuthSchema($json_handler);
                $auth_controller = new AuthController($auth_model, $user_model);
                $auth_controller->dispatch($method, $sliced_path);
                break;
            default:
                Router::not_found();
                break;
        }
    }
}
