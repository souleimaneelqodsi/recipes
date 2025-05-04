<?php class RecipeController implements BaseController
{
    private $recipe_schema;
    private UserSchema $user_schema;

    public function __construct(
        RecipeSchema $recipe_schema,
        UserSchema $user_schema
    ) {
        $this->recipe_schema = $recipe_schema;
        $this->user_schema = $user_schema;
    }

    private function handleUnauthorized(): bool
    {
        if (!Session::isLoggedIn()) {
            http_response_code(401);
            header("Content-Type: application/json");
            echo json_encode([
                "error" => "Unauthorized: user is not logged in",
            ]);
            return true;
        }
        return false;
    }

    public function search(): void
    {
        try {
            $recipes = $this->recipe_schema->search($_GET["search"]);
            if (empty($recipes)) {
                error_log("empty recipes.json");
                http_response_code(204);
            } else {
                http_response_code(200);
                header("Content-Type: application/json");
                echo json_encode($recipes);
            }
        } catch (Exception $e) {
            http_response_code(500);
            header("Content-Type: application/json");
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    public function getById(string $id): void
    {
        try {
            // if ($this->handleUnauthorized()) {
            //     error_log("GET BY ID ERROR: Unauthorized");
            //     return;
            // }
            $recipe = $this->recipe_schema->getById($id);
            if (empty($recipe)) {
                http_response_code(404);
            } else {
                http_response_code(200);
                header("Content-Type: application/json");
                echo json_encode($recipe);
            }
        } catch (Exception $e) {
            http_response_code(500);
            header("Content-Type: application/json");
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    public function getAll(): void
    {
        try {
            $recipes = $this->recipe_schema->getAll();
            if (empty($recipes)) {
                http_response_code(204);
                header("Content-Type: application/json");
                echo json_encode([]);
            } else {
                http_response_code(200);
                header("Content-Type: application/json");
                echo json_encode($recipes);
            }
        } catch (Exception $e) {
            http_response_code(500);
            header("Content-Type: application/json");
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    public function create(): void
    {
        try {
            if ($this->handleUnauthorized()) {
                return;
            }
            if (
                Session::getUserRole() !== "Chef" &&
                Session::getUserRole() !== "Administrateur"
            ) {
                http_response_code(403);
                header("Content-Type: application/json");
                echo json_encode([
                    "error" =>
                        "Forbidden: User is neither cook nor administrator",
                ]);
                return;
            }
            $data = Utils::getJSONBody();
            if ($data == null) {
                http_response_code(400);
                header("Content-Type: application/json");
                echo json_encode([
                    "error" => "Bad Request: No data provided",
                ]);
                return;
            }
            $recipe = $this->recipe_schema->create($data);
            $sub_data = ["id" => $data["id"]];
            key_exists("name", $data)
                ? ($sub_data["name"] = $data["name"])
                : ($sub_data["nameFR"] = $data["nameFR"]);
            $sub_data["imageURL"] = $data["imageURL"];
            $sub_data["likes"] = 0;
            $owner_recipe = $this->user_schema->addRecipe($sub_data);
            if (empty($owner_recipe)) {
                http_response_code(400);
                header("Content-Type: application/json");
                echo json_encode([
                    "error" =>
                        "Failed to add recipe to the user's recipes: bad input.",
                ]);
                return;
            }
            http_response_code(201);
            header("Content-Type: application/json");
            echo json_encode($recipe);
        } catch (Exception $e) {
            http_response_code(500);
            header("Content-Type: application/json");
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    public function update(string $id, array $data): void
    {
        try {
            if ($this->handleUnauthorized()) {
                return;
            }
            if (
                Session::getUserRole() !== "Administrateur" &&
                !$this->recipe_schema->isAuthor(
                    Session::getCurrentUser()->getId(),
                    $id
                )
            ) {
                http_response_code(403);
                header("Content-Type: application/json");
                echo json_encode([
                    "error" =>
                        "Forbidden: User is neither author nor administrator",
                ]);
                return;
            }
            $recipe = $this->recipe_schema->update($id, $data);
            if (empty($recipe)) {
                http_response_code(400);
                header("Content-Type: application/json");
                echo json_encode([
                    "error" => "Bad Request: recipe not found or bad entry",
                ]);
                return;
            }
            $name = key_exists("name", $data) ? $data["name"] : null;
            $nameFR = key_exists("nameFR", $data) ? $data["nameFR"] : null;
            $recipe_obj = $this->recipe_schema->getById($id);
            if (!empty($recipe_obj)) {
                $author_id = $recipe_obj["Author"];
                $this->user_schema->editRecipe(
                    $id,
                    $author_id,
                    name: $name,
                    nameFR: $nameFR
                );
            } else {
                throw new Exception(
                    "Could not edit the recipe in the owner profile"
                );
            }
            http_response_code(200);
            header("Content-Type: application/json");
            echo json_encode($recipe);
        } catch (Exception $e) {
            http_response_code(500);
            header("Content-Type: application/json");
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    public function delete(string $recipe_id): void
    {
        try {
            if ($this->handleUnauthorized()) {
                return;
            }
            if (Session::getUserRole() !== "Administrateur") {
                http_response_code(403);
                header("Content-Type: application/json");
                echo json_encode([
                    "error" => "Forbidden: User is not an administrator",
                ]);
                return;
            }
            $recipe = $this->recipe_schema->delete($recipe_id);
            if (empty($recipe)) {
                http_response_code(404);
                header("Content-Type: application/json");
                echo json_encode([
                    "error" => "Not found: Recipe not found",
                ]);
                return;
            }
            $delete_recipe = $this->user_schema->deleteRecipe(
                $recipe_id,
                $this->recipe_schema->getById($recipe_id)["Author"]
            );
            if (empty($delete_recipe)) {
                http_response_code(404);
                header("Content-Type: application/json");
                echo json_encode([
                    "error" => "Not found: User or recipe not found",
                ]);
                return;
            }
            http_response_code(200);
            header("Content-Type: application/json");
            echo json_encode($recipe);
        } catch (Exception $e) {
            http_response_code(500);
            header("Content-Type: application/json");
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    public function like(string $recipe_id): void
    {
        try {
            if ($this->handleUnauthorized()) {
                return;
            }
            $recipe = $this->recipe_schema->like($recipe_id);
            $user = $this->user_schema->like($recipe_id);
            $recipe_obj = $this->recipe_schema->getById($recipe_id);
            $this->user_schema->editRecipe(
                $recipe_id,
                $recipe_obj["Author"],
                likes: $recipe_obj["likes"]
            );
            if (empty($recipe)) {
                http_response_code(404);
                header("Content-Type: application/json");
                echo json_encode([
                    "error" => "Not found: Recipe not found",
                ]);
                return;
            }
            if (empty($user)) {
                http_response_code(403);
                header("Content-Type: application/json");
                echo json_encode([
                    "error" => "Forbidden: User already liked this recipe",
                ]);
                return;
            }
            http_response_code(200);
            header("Content-Type: application/json");
            echo json_encode($recipe);
        } catch (Exception $e) {
            http_response_code(500);
            header("Content-Type: application/json");
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    public function unlike(string $recipe_id): void
    {
        try {
            if ($this->handleUnauthorized()) {
                return;
            }
            $recipe = $this->recipe_schema->unlike($recipe_id);
            $user = $this->user_schema->unlike($recipe_id);
            $recipe_obj = $this->recipe_schema->getById($recipe_id);
            $this->user_schema->editRecipe(
                $recipe_id,
                $recipe_obj["Author"],
                likes: $recipe_obj["likes"]
            );
            if (empty($recipe)) {
                http_response_code(404);
                header("Content-Type: application/json");
                echo json_encode([
                    "error" => "Not found: Recipe not found",
                ]);
                return;
            }
            if (empty($user)) {
                http_response_code(403);
                header("Content-Type: application/json");
                echo json_encode([
                    "error" => "Forbidden: Recipe not liked",
                ]);
                return;
            }
            http_response_code(200);
            header("Content-Type: application/json");
            echo json_encode($recipe);
        } catch (Exception $e) {
            http_response_code(500);
            header("Content-Type: application/json");
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    public function translate(string $recipe_id, array $translation): void
    {
        try {
            if ($this->handleUnauthorized()) {
                return;
            }
            $recipe = $this->recipe_schema->translate($recipe_id, $translation);
            if (empty($recipe)) {
                http_response_code(404);
                header("Content-Type: application/json");
                echo json_encode([
                    "error" => "Not found: Recipe not found",
                ]);
                return;
            }
            http_response_code(200);
            header("Content-Type: application/json");
            echo json_encode($recipe);
        } catch (Exception $e) {
            http_response_code(500);
            header("Content-Type: application/json");
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    public function setPhoto(string $recipe_id, string $photo_id): void
    {
        try {
            if ($this->handleUnauthorized()) {
                return;
            }
            if (
                !$this->recipe_schema->isAuthor(
                    $recipe_id,
                    Session::getCurrentUser()->getId()
                ) &&
                Session::getUserRole() !== "Administrateur"
            ) {
                http_response_code(403);
                header("Content-Type: application/json");
                echo json_encode([
                    "error" =>
                        "Forbidden: You are neither the author of this recipe nor an administrator",
                ]);
                return;
            }
            $url = "";
            $recipe = $this->recipe_schema->setPhoto(
                $recipe_id,
                $photo_id,
                $url
            );
            if (empty($recipe)) {
                http_response_code(404);
                header("Content-Type: application/json");
                echo json_encode([
                    "error" => "Not found: Photo not found",
                ]);
                return;
            }
            $recipe_obj = $this->recipe_schema->getById($recipe_id);
            $author_id = $recipe_obj["Author"];
            $this->user_schema->editRecipe(
                $recipe_id,
                $author_id,
                imageURL: $url
            );
            http_response_code(200);
            header("Content-Type: application/json");
            echo json_encode($recipe);
        } catch (InvalidArgumentException $e) {
            //when the recipe is already published, 418 just for the joke...
            http_response_code(418);
            header("Content-Type: application/json");
            echo json_encode(["error" => $e->getMessage()]);
            return;
        } catch (Exception $e) {
            http_response_code(500);
            header("Content-Type: application/json");
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    public function publish(string $recipe_id): void
    {
        try {
            if ($this->handleUnauthorized()) {
                return;
            }
            $recipe = $this->recipe_schema->publish($recipe_id);
            if (Session::getUserRole() !== "Administrateur") {
                http_response_code(403);
                header("Content-Type: application/json");
                echo json_encode([
                    "error" =>
                        "Forbidden: You are not administrator, you cannot publish a recipe.",
                ]);
                return;
            }
            if (empty($recipe)) {
                http_response_code(404);
                header("Content-Type: application/json");
                echo json_encode([
                    "error" =>
                        "Not found: Recipe not found or is already published.",
                ]);
                return;
            }
            http_response_code(200);
            header("Content-Type: application/json");
            echo json_encode($recipe);
        } catch (Exception $e) {
            http_response_code(500);
            header("Content-Type: application/json");
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    // comment override tag if you run this on PHP <8)
    #[\Override]
    public function dispatch($method, array $path): void
    {
        if (empty($method)) {
            error_log("RECIPES dispatch: empty method");
            http_response_code(400);
            header("Content-Type: application/json");
            echo json_encode(["error" => "Invalid method or path"]);
            return;
        }

        switch ($method) {
            case "GET":
                error_log("RECIPES DISPATCH: GET CASE");
                // search case
                if (empty($path) && isset($_GET["search"])) {
                    error_log("SEARCH METHOD CALLED");
                    $this->search();
                    return;
                }
                // getAll case
                if (empty($path)) {
                    error_log("SEARCH GETALL CALLED");
                    $this->getAll();
                    return;
                }
                // getById case
                if ($path[0] !== "" && count((array) $path) === 1) {
                    error_log("SEARCH GETBYID CALLED");
                    $this->getById($path[0]);
                    return;
                }
                error_log(
                    "FAILED TO PARSE GET CASES IN RECIPECONTROLLER DISPATCH"
                );
                http_response_code(400);
                header("Content-Type: application/json");
                echo json_encode(["error" => $path]);
                break;
            case "POST":
                // create case
                if (empty($path)) {
                    $this->create();
                    return;
                }
                // like case
                if (
                    $path[0] !== "" &&
                    $path[1] === "like" &&
                    count((array) $path) === 2
                ) {
                    $this->like($path[0]);
                    return;
                }
                http_response_code(400);
                header("Content-Type: application/json");
                echo json_encode(["error" => "Bad request"]);
                break;
            case "PATCH":
                $data = Utils::getJSONBody();
                if ($data === null) {
                    http_response_code(400);
                    header("Content-Type: application/json");
                    echo json_encode(["error" => "Bad request"]);
                    return;
                }
                //update case
                if ($path[0] !== "" && count((array) $path) === 1) {
                    $this->update($path[0], $data);
                    return;
                }
                if ($path[0] !== "" && count((array) $path) === 2) {
                    //case set photo
                    if ($path[1] === "photos") {
                        if (!isset($data["photo_id"])) {
                            http_response_code(400);
                            header("Content-Type: application/json");
                            echo json_encode([
                                "error" =>
                                    "Missing photo id or misspelled parameter: photo_id",
                            ]);
                            return;
                        }
                        $this->setPhoto($path[0], $data["photo_id"]);
                        //case translate
                    } elseif ($path[1] === "translate") {
                        $this->translate($path[0], $data);
                    }
                    //case publish
                    elseif ($path[1] === "validate") {
                        $this->publish($path[0]);
                    }
                    return;
                }
                http_response_code(400);
                header("Content-Type: application/json");
                echo json_encode(["error" => "Bad request"]);
                break;
            case "DELETE":
                // case delete
                if (count((array) $path) === 1 && $path[0] !== "") {
                    $this->delete($path[0]);
                    return;
                }
                if (
                    count((array) $path) === 2 &&
                    $path[0] !== "" &&
                    $path[1] === "like"
                ) {
                    $this->unlike($path[0]);
                    return;
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
