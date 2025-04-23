<?php

class RecipeSchema
{
    private JSONHandler $json_handler;
    private const DATA_FILE = "recipes.json";

    public function __construct(JSONHandler $json_handler)
    {
        $this->json_handler = $json_handler;
    }

    /**
     * @return array     */
    public function getAll(): array
    {
        try {
            $recipes = $this->json_handler->readData(self::DATA_FILE);
            return $recipes;
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }

    /**
     * @return array     */
    public function getById(string $recipe_id): array
    {
        try {
            $recipes = $this->getAll();
            $recipe_index = array_search(
                $recipe_id,
                array_column($recipes, "id")
            );
            if ($recipe_index !== false) {
                return $recipes[$recipe_index];
            }
            print "Recipe not found";
            return [];
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }

    /**
     * @return array     */
    private function search_term(string $search_term): array
    {
        //define a score of correspondence
        //tokenize the whole json and count the number of matches
        //term frequency : occurrences/
        try {
            $search_term = strtolower($search_term);
            $recipes = $this->getAll();
            //DF
            $document_frequency = 0;
            //TF-IDF
            $score_by_document = [];

            foreach ($recipes as $recipe) {
                //TOKENIZATION
                if (!Validator::validateRecipe($recipe)) {
                    error_log("Invalid recipe data" . $recipe["id"]);
                    continue;
                }
                $search_terms =
                    strtolower($recipe["name"]) .
                    " " .
                    strtolower($recipe["nameFR"]) .
                    " " .
                    strtolower($recipe["Author"]) .
                    " ";
                $ingredients_set = isset($recipe["ingredients"]);
                $ingredientsFR_set = isset($recipe["ingredientsFR"]);
                if ($ingredients_set) {
                    foreach ($recipe["ingredients"] as $ingredient) {
                        $search_terms .= $ingredient["name"] . " ";
                    }
                    $search_terms .= implode(" ", $recipe["steps"]) . " ";
                }
                if ($ingredientsFR_set) {
                    foreach ($recipe["ingredientsFR"] as $ingredient) {
                        $search_terms .= $ingredient["name"] . " ";
                    }
                    $search_terms .= implode(" ", $recipe["stepsFR"]) . " ";
                }

                $search_terms = strtolower($search_terms);
                $search_terms = preg_replace(
                    "/[[:punct:]]/",
                    "",
                    $search_terms
                );
                $search_tokens = preg_split(
                    "/\s+/",
                    $search_terms,
                    -1,
                    PREG_SPLIT_NO_EMPTY
                );
                $word_count = count($search_tokens);
                $words_occurrences = array_count_values($search_tokens);
                $word_frequency = $words_occurrences[$search_term] ?? 0;
                $term_frequency =
                    $word_count > 0 ? $word_frequency / $word_count : 0;
                $score_by_document[$recipe["id"]] = $term_frequency;
                //DF (if the score is greater than 0, the element exists in this non-empty document/recipe)
                if ($term_frequency > 0) {
                    $document_frequency++;
                }
            }
            //IDF
            $inverse_document_frequency = log(
                count($recipes) / (1 + $document_frequency)
            );
            //TF-IDF
            $score_by_document = array_map(
                fn($tf_score): float => $tf_score * $inverse_document_frequency,
                $score_by_document
            );
            //returning best search results...
            arsort($score_by_document);
            return $score_by_document;
        } catch (Exception $e) {
            error_log("error:" . $e->getMessage());
            throw $e;
        }
    }
    /**
     * @return void
     */
    public function search(string $query): array
    {
        //lowercase
        $query = strtolower($query);
        //remove punctuation
        $query = preg_replace("/[[:punct:]]/", "", $query);
        //split into words
        $query = preg_split("/\s+/", $query, -1, PREG_SPLIT_NO_EMPTY);
        $query_scores = [];
        //words useless to search
        $stop_words = [
            //fr
            "et",
            "de",
            "la",
            "le",
            "les",
            "un",
            "une",
            "des",
            "au",
            "aux",
            "à",
            "a",
            "avec",
            "pour",
            "sur",
            //en
            "and",
            "or",
            "of",
            "the",
            "the",
            "with",
            "to",
            "on",
        ];
        $query = array_filter(
            $query,
            fn($term) => !in_array($term, $stop_words, true)
        );
        foreach ($query as $term) {
            $query_scores[$term] = $this->search_term($term);
        }
        $final_scores = [];
        foreach ($query_scores as $term => $score_set) {
            foreach ($score_set as $recipe_id => $score) {
                if (!isset($final_scores[$recipe_id])) {
                    $final_scores[$recipe_id] = 0;
                }
                $final_scores[$recipe_id] += $score;
            }
        }
        arsort($final_scores);
        return array_keys($final_scores);
    }

    /**
     * @return array     * @param array<int,mixed> $without
     * @param array<int,mixed> $ingredients
     * @param array<int,mixed> $steps
     * @param array<int,mixed> $timers
     */
    public function create(array $recipeData): array
    {
        if (
            !isset($recipeData["fr"]) ||
            !isset($recipeData["user_id"]) ||
            !isset($recipeData["name"]) ||
            !isset($recipeData["without"]) ||
            !is_array($recipeData["without"]) ||
            !isset($recipeData["ingredients"]) ||
            !is_array($recipeData["ingredients"]) ||
            !isset($recipeData["steps"]) ||
            !is_array($recipeData["steps"]) ||
            !isset($recipeData["timers"]) ||
            !isset($recipeData["imageURL"]) ||
            !isset($recipeData["total_time"])
        ) {
            throw new Exception("Invalid recipe data");
        }
        $recipe = [
            "id" => Utils::uuid4(),
            "name" => "",
            "nameFR" => "",
            "Author" => $recipeData["user_id"],
            "Without" => $recipeData["without"],
            "ingredients" => [],
            "ingredientsFR" => [],
            "steps" => [],
            "stepsFR" => [],
            "timers" => [],
            "imageURL" => $recipeData["imageURL"],
            //transforming the name of the recipe into a dash-separated slug before passing it to the URL
            //the code to convert to slug is AI-generated! -> regex (and php) skill issues...
            "originalURL" =>
                "recipes/" .
                trim(
                    preg_replace(
                        "/-+/",
                        "-",
                        preg_replace(
                            "/[^a-z0-9-]+/",
                            "-",
                            strtolower(
                                iconv(
                                    "UTF-8",
                                    "ASCII//TRANSLIT//IGNORE",
                                    $recipeData["fr"]
                                        ? $recipeData["nameFR"]
                                        : $recipeData["name"]
                                ) ?? ""
                            )
                        )
                    ),
                    "-"
                ),
            "likes" => 0,
            "status" => "draft",
            "comments" => [],
            "photos" => [],
            "total_time" => $recipeData["total_time"],
            "created_at" => time(),
        ];
        if ($recipeData["fr"]) {
            $recipe["nameFR"] = $recipeData["name"];
            $recipe["stepsFR"] = $recipeData["steps"];
            $recipe["ingredientsFR"] = $recipeData["ingredients"];
        } else {
            $recipe["name"] = $recipeData["name"];
            $recipe["steps"] = $recipeData["steps"];
            $recipe["ingredients"] = $recipeData["ingredients"];
        }
        if (Validator::validateRecipe($recipe)) {
            try {
                $all_recipes = $this->getAll();
                array_push($all_recipes, $recipe);
                $this->json_handler->writeData(self::DATA_FILE, $all_recipes);
            } catch (Exception $e) {
                error_log("Error writing recipe data: " . $e->getMessage());
                throw new Exception(
                    "Error writing recipe data: " . $e->getMessage()
                );
            }
            return $recipe;
        } else {
            error_log("Invalid recipe data");
            throw new Exception("Invalid recipe data");
        }
    }

    /**
     * @return array     */
    public function update(string $recipe_id, array $updateData): array
    {
        try {
            // $recipe = $this->getById($recipe_id);
            // if ($recipe === []) {
            //     throw new Exception("Recipe not found");
            // }
            // here, I'm not using getById() because I need to have an array with all the recipes
            if (
                !isset($updateData) ||
                !is_array($updateData) ||
                empty($updateData) ||
                // array_key_exists("id", $updateData) ||
                // array_key_exists("Author", $updateData) ||
                // array_key_exists("originalURL", $updateData) ||
                // array_key_exists("created_at", $updateData) ||
                array_diff(
                    array_keys($updateData),
                    //allowed:
                    [
                        "name",
                        "nameFR",
                        "Without",
                        "ingredients",
                        "ingredientsFR",
                        "steps",
                        "stepsFR",
                        "timers",
                        "imageURL",
                        "status",
                        "comments",
                        "photos",
                        "total_time",
                    ]
                ) !== []
            ) {
                throw new Exception(
                    "Invalid updated recipe data: contains either inexistent or forbidden attributes"
                );
            }
            $allRecipes = $this->getAll();
            $final_recipe = [];
            for ($i = 0; $i < count($allRecipes); $i++) {
                if ($allRecipes[$i]["id"] == $recipe_id) {
                    $final_recipe = $allRecipes[$i];
                    foreach ($updateData as $key => $value) {
                        //final check, you never know...
                        if (!in_array($key, array_keys($final_recipe), true)) {
                            throw new Exception(
                                "Invalid updated recipe data: contains either inexistent or forbidden attributes"
                            );
                        }
                        $final_recipe[$key] = $value;
                    }
                    if (!Validator::validateRecipe($final_recipe)) {
                        throw new Exception("Invalid updated recipe data");
                    }
                    $allRecipes[$i] = $final_recipe;
                    $this->json_handler->writeData(
                        self::DATA_FILE,
                        $allRecipes
                    );
                }
            }

            if (empty($final_recipe)) {
                throw new Exception("Recipe not found");
            }
            return $final_recipe;
        } catch (Exception $e) {
            error_log("Error updating recipe data: " . $e->getMessage());
            throw new Exception(
                "Error updating recipe data: " . $e->getMessage()
            );
        }
    }
    /**
     * @return void     */
    public function delete(string $recipe_id): array
    {
        try {
            $all_recipes = $this->getAll();

            $recipe_index = array_search(
                $recipe_id,
                array_column($all_recipes, "id")
            );
            if ($recipe_index !== false) {
                unset($all_recipes[$recipe_index]);
                //reindexing
                $all_recipes = array_values($all_recipes);
                $this->json_handler->writeData(self::DATA_FILE, $all_recipes);
                return $all_recipes[$recipe_index];
            } else {
                throw new Exception("error deleting recipe:Recipe not found");
            }
        } catch (Exception $e) {
            error_log("Error deleting recipe data: " . $e->getMessage());
            throw new Exception(
                "Error deleting recipe data: " . $e->getMessage()
            );
        }
    }
    // /**
    //  * @return void     */
    // public function like(string $recipe_id): void
    // {
    //     try {
    //         $this->update($recipe_id, [
    //             "likes" => $this->getById($recipe_id)["likes"] + 1,
    //         ]);
    //     } catch (Exception $e) {
    //         error_log("Error updating recipe data: " . $e->getMessage());
    //         throw new Exception(
    //             "Error updating recipe data: " . $e->getMessage()
    //         );
    //     }
    // }
    //it was possible to compose update and getById but it's not efficient

    /**
     * @return void // Ou peut-être retourner le nouveau nombre de likes ?
     */
    public function like(string $recipe_id): array
    {
        try {
            $all_recipes = $this->getAll();
            $recipe_index = array_search(
                $recipe_id,
                array_column($all_recipes, "id")
            );
            if ($recipe_index !== false) {
                $current_likes = $all_recipes[$recipe_index]["likes"];
                $all_recipes[$recipe_index]["likes"] = $current_likes + 1;
                $this->json_handler->writeData(self::DATA_FILE, $all_recipes);
                return $all_recipes[$recipe_index];
            } else {
                throw new Exception("Recipe not found for liking");
            }
        } catch (Exception $e) {
            error_log("Error liking recipe: " . $e->getMessage());
            throw new Exception("Error liking recipe: " . $e->getMessage());
        }
    }
    /**
     * @return array     */
    public function translate(string $recipe_id, array $translation): array
    {
        try {
            $all_recipes = $this->getAll();
            $recipe_index = array_search(
                $recipe_id,
                array_column($all_recipes, "id")
            );
            if ($recipe_index !== false) {
                if (
                    // array_keys($translation) !== [
                    //     "name",
                    //     "ingredients",
                    //     "steps",
                    // ]
                    //could've tested like this but it's strict on the order of the keys...
                    array_diff(array_keys($translation), [
                        "name",
                        "ingredients",
                        "steps",
                    ]) === []
                ) {
                    throw new Exception(
                        "Invalid translation data: missing or invalid keys"
                    );
                }
                $recipe = $all_recipes[$recipe_index];
                //the code might seem permissive/simplist at first sight but we consider the json structure to have never been manipulated externally to the API (other than by the API itself with its Validator), so there are preexisting guarantees we can rely on...
                $fr = !empty($recipe["nameFR"]);
                if (!empty($recipe["name"]) && $fr) {
                    throw new Exception("Recipe already translated");
                }
                if ($fr) {
                    if (
                        count($recipe["ingredientsFR"]) !==
                            count($translation["ingredients"]) ||
                        count($recipe["stepsFR"]) !==
                            count($translation["steps"])
                    ) {
                        error_log(
                            "Non-respect de l'exigence fonctionnelle du sujet du projet: le nombre d'ingrédients et d'étapes doit être le même dans les 2 langues"
                        );
                        throw new Exception(
                            "Invalid translation: ingredient or step count mismatch"
                        );
                    }
                    $recipe["name"] = $translation["name"];
                    $recipe["ingredients"] = $translation["ingredients"];
                    $recipe["steps"] = $translation["steps"];
                } else {
                    if (
                        count($recipe["ingredients"]) !==
                            count($translation["ingredients"]) ||
                        count($recipe["steps"]) !== count($translation["steps"])
                    ) {
                        error_log(
                            "Non-respect de l'exigence fonctionnelle du sujet du projet: le nombre d'ingrédients et d'étapes doit être le même dans les 2 langues"
                        );
                        throw new Exception(
                            "Invalid translation: ingredient or step count mismatch"
                        );
                    }
                    $recipe["nameFR"] = $translation["name"];
                    $recipe["ingredientsFR"] = $translation["ingredients"];
                    $recipe["stepsFR"] = $translation["steps"];
                }
                if (!Validator::validateRecipe($recipe)) {
                    throw new Exception(
                        "Translation error: Final recipe has invalid data"
                    );
                }
                $all_recipes[$recipe_index] = $recipe;
                $this->json_handler->writeData(self::DATA_FILE, $all_recipes);
                return $recipe;
            } else {
                throw new Exception("Recipe not found for translation");
            }
        } catch (Exception $e) {
            error_log("Error translating recipe: " . $e->getMessage());
            throw new Exception(
                "Error translating recipe: " . $e->getMessage()
            );
        }
    }
    /**
     * @return array     */
    public function setPhoto(string $recipe_id, string $photo_id): array
    {
        try {
            if (empty($photo_id)) {
                throw new Exception("Photo ID is empty");
            }
            $all_recipes = $this->getAll();
            $recipe_index = array_search(
                $recipe_id,
                array_column($all_recipes, "id")
            );
            if ($recipe_index === false) {
                throw new Exception("Recipe not found");
            }
            $recipe = $all_recipes[$recipe_index];
            $photo_index = array_search(
                $photo_id,
                array_column($recipe["photos"], "id")
            );
            if ($photo_index === false) {
                throw new Exception("Photo not found");
            }
            $photo = $recipe["photos"][$photo_index];
            if ($recipe["imageURL"] === $photo["url"]) {
                throw new Exception("Photo already set as main recipe image");
            }
            $recipe["imageURL"] = $photo["url"];
            if (!Validator::validateRecipe($recipe)) {
                throw new Exception(
                    "Error after setting photo for recipe: result is not valid"
                );
            }
            $all_recipes[$recipe_index] = $recipe;
            $this->json_handler->writeData(self::DATA_FILE, $all_recipes);
            return $recipe;
        } catch (Exception $e) {
            error_log("Error setting photo for recipe: " . $e->getMessage());
            throw new Exception(
                "Error setting photo for recipe: " . $e->getMessage()
            );
        }
    }
}
