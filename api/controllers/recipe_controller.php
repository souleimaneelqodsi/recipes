<?php class RecipeController implements Controller{
    /*private $recipes;
    public function __construct(){
        $this->recipes = array();
    }


    private static function getRecipeById( $id ){
    
    }
    private static function setRecipe( $id ){
    
    }
    private static function delRecipe( $id ){
    
    }*/

    public static function dispatch($method, $uri, $path){

    }

    // CODE IDEA FOR DISPATCH
    /*                if (!isset($path_elements[0]) || empty($path_elements[0])) {
                    if($method == 'GET'){
                        RecipeController::all();
                    }
                } else {
                    if (gettype($path_elements[0]) === 'integer') {
                        switch ($method) {
                            case 'GET':
                                RecipeController::getRecipe($path_elements[2]);
                                break;
                            case 'PUT':
                                RecipeController::setRecipe($path_elements[2]);
                                break;
                            case 'DELETE':
                                RecipeController::delRecipe($path_elements[2]);
                                break;
                            default:
                                throw new ErrorException("This endpoint does not exist.");
                        }
                    } else {
                        if()
                    }
                } */

}