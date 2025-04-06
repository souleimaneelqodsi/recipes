<?php
include 'controllers/recipe_controller.php';
class Router
{
    /*private $routes = array();
    public function __construct() {}

    public function addRoute($route)
    {
        array_push($this->routes);
    }

    public function getAllRoutes()
    {
        return $this->routes;
    }*/

    private static function not_found()
    {
        $doesnt_exist = new ErrorException("This endpoint does not exist.");
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
        throw $doesnt_exist;
    }

    static public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        //TODO: check if uri decomposition logic is correct
        $uri = str_replace('recipes/api/', '', $uri);
        $path = explode('/', $uri);
        $classname = '';
        switch ($path[0]) {
            case 'recipes':
                if (isset($path[2]) && !empty($path[2])) {
                    if ($path[2] === 'comments') $classname = 'CommentController';
                    else if ($path[2] == 'photos') $classname = 'PhotoController';
                    else Router::not_found();
                    break;
                }
                $classname = 'RecipeController';
                break;
            //TODO: add the rest of the cases
            default:
                Router::not_found();
                break;
        }
        if ($classname !== '') {
            $class = $classname;
            $class::dispatch($method, $uri, array_slice($path, 1));
        }
    }
}
