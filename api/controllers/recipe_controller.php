<?php class RecipeController implements Controller
{
    private $recipe_schema;

    public function __construct(RecipeSchema $recipe_schema)
    {
        $this->recipe_schema = $recipe_schema;
    }

    // success: 200 OK, nothing : 204, error : 400, other error:500
    public function search(): void {}
    //success: 200 OK, unauthenticated : 401, error : 400, not found:404, other error:500
    public function getById(string $recipe_id): void {}
    //nothing: 204, success:200, OK, error : 400, other error:500
    public function getAll(string $recipe_id): void {}
    // success: 201 OK, error:400, other error:500
    public function create(): void {}
    // success: 200 OK, error:400, other error:500
    public function update(string $recipe_id): void {}
    // success:200; error:400, not found:404, forbidden:403, other error:500
    public function delete(string $recipe_id): void {}
    // success:200; error:400, not found:404, forbidden:403, unauthorized:401, other error:500
    public function like(string $recipe_id): void {}
    // success:200; error:400, not found:404, forbid    den:403, unauthorized:401, other error:500
    public function translate(string $recipe_id): void {}
    // success:200; error:400, not found:404, unauthorized:401, other error:500
    public function setPhoto(string $recipe_id): void {}

    // bad request:400, method not allowed:405
    // comment override tag if you run this on PHP <8)
    #[\Override]
    public function dispatch($method, array $path): void
    {
        if (empty($method)) {
            http_response_code(400);
            header("Content-Type: application/json");
            echo json_encode(["error" => "Invalid method or path"]);
            return;
        }

        switch ($method) {
            case "GET":
                // getAll case
                if (empty($path)) {
                    $this->getAll();
                    break;
                }
                // search case
                if (empty($path) && isset($_GET["search"])) {
                    $this->search(); // the search term will be extracted from the GET request
                    break;
                }
                // getById case
                if ($path[0] !== "" && count($path) === 1) {
                    $this->getById($path[0]);
                    break;
                }
                http_response_code(400);
                header("Content-Type: application/json");
                echo json_encode(["error" => "Bad request"]);
                break;
            case "POST":
                // like case
                if (
                    $path[0] !== "" &&
                    $path[1] === "like" &&
                    count($path) === 2
                ) {
                    $this->like($path[0]);
                    break;
                }
                // create case
                if (empty($path)) {
                    $this->create();
                    break;
                }
                http_response_code(400);
                header("Content-Type: application/json");
                echo json_encode(["error" => "Bad request"]);
                break;
            case "PUT":
                // update case
                if ($path[0] !== "" && count($path) === 1) {
                    $this->update($path[0]);
                    break;
                }
                http_response_code(400);
                header("Content-Type: application/json");
                echo json_encode(["error" => "Bad request"]);
                break;
            case "PATCH":
                if ($path[0] !== "" && count($path) === 2) {
                    //case set photo
                    if ($path[1] === "photos") {
                        $this->setPhoto($path[0]);
                        //case translate
                    } elseif ($path[1] === "translate") {
                        $this->translate($path[0]);
                    }
                    break;
                }
                http_response_code(400);
                header("Content-Type: application/json");
                echo json_encode(["error" => "Bad request"]);
                break;
            case "DELETE":
                // case delete
                if (count($path) === 1 && $path[0] !== "") {
                    $this->delete($path[0]);
                    break;
                }
                http_response_code(400);
                header("Content-Type: application/json");
                echo json_encode(["error" => "Bad request"]);
                break;
            default:
                http_response_code(405);
                header("Content-Type: application/json");
                echo json_encode(["error" => "Invalid method"]);
                break;
        }
    }
}
