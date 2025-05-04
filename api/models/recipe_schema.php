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
                array_column($recipes, "id"),
                true
            );
            if ($recipe_index !== false) {
                return $recipes[$recipe_index];
            }
            return [];
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }

    /**
     * @return array     */
    private function getStopWords(): array
    {
        $stop_words = [];
        try {
            $stop_words_eng = $this->json_handler->readData(
                "stop_words_english.json"
            );
            $stop_words_fr = $this->json_handler->readData(
                "stop_words_french.json"
            );
            $stop_words = array_merge($stop_words_eng, $stop_words_fr);
            error_log("Could load stop word");
        } catch (Exception $e) {
            error_log("Couldn't load stop words : " . $e->getMessage());
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
                "vers",
                "a",
                "en",
                "avec",
                "pour",
                "avant",
                "dessous",
                "dessus",
                "après",
                "mais",
                "pendant",
                "sur",
                "dans",
                "ou",
                "tout",
                "tous",
                //en
                "and",
                "or",
                "of",
                "the",
                "for",
                "with",
                "to",
                "but",
                "on",
                "onto",
                "in",
                "using",
                "all",
                "about",
                "at",
                "before",
                "after",
            ];
        }
        return $stop_words;
    }

    public function getDrafts(): array
    {
        try {
            $all_recipes = $this->getAll();
            return array_filter(
                $all_recipes,
                fn($recipe) => $recipe["status"] === "draft"
            );
        } catch (Exception $e) {
            error_log("Error getting draft recipes: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * @return array
     */
    public function getPublished(): array
    {
        try {
            $all_recipes = $this->getAll();
            return array_filter(
                $all_recipes,
                fn($recipe) => $recipe["status"] === "published"
            );
        } catch (Exception $e) {
            error_log("Error getting published recipes: " . $e->getMessage());
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
                //remove irrelevant stop words for better efficiency
                $stop_words = $this->getStopWords();
                $search_tokens = array_filter(
                    $search_tokens,
                    fn($token) => !in_array(strtolower($token), $stop_words)
                );
                $word_count = count($search_tokens);
                $words_occurrences = array_count_values($search_tokens);
                //how many times the word appears exactly
                $word_frequency = $words_occurrences[$search_term] ?? 0;
                //now we have to do a traversal of the array to make a fuzzy search with each word of the array
                foreach ($search_tokens as $token) {
                    if (strlen($token) < 3) {
                        continue;
                    }
                    //PREG_SPLIT_NO_EMPTY used in preg_split before guarantees no token is empty so strlen(token) > 0, safe for log here
                    $threshold = round(log(strlen($token)));
                    //bonus of 1 if rounded log of the token  is greater or equal to 1, in order to be less strict and improve the relevance of the results
                    // $threshold = $log_threshold >= 3 ? $log_threshold + 1 : 1;
                    // removed it because it was possible source of imbalanced thresholds
                    // Ensure we don't cause warnings with strings > 255 chars
                    if (strlen($token) > 255 || strlen($search_term) > 255) {
                        if (stripos($token, $search_term) !== false) {
                            $word_frequency++;
                        }
                        continue;
                    }
                    $distance = levenshtein($search_term, $token);

                    if ($distance <= $threshold) {
                        $word_frequency++;
                    }
                }

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
            //IDF is gonna be equal to 0 if the number of recipes is equal to the number of documents in which the search term appears + 1, log(1) = 0
            //I don't know how to fix this problem so I prefer that IDF doesn't make the whole result null, I treat this case by replacing it with 1 to cancel its effect
            $inverse_document_frequency =
                count($recipes) > $document_frequency + 1 &&
                $document_frequency > 0
                    ? $inverse_document_frequency
                    : 1;
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
     * @return array
     */
    public function search(string $query): array
    {
        //lowercase
        $query = strtolower($query);
        //remove punctuation
        $query = preg_replace("/[[:punct:]]/", "", $query);
        //split into words
        $query = preg_split("/\s+/", $query, -1, PREG_SPLIT_NO_EMPTY);
        //words useless to search
        // $stop_words = $this->getStopWords();
        //removed this because some stop words can resemble tokens in recipes...
        // $query = array_filter(
        //     $query,
        //     fn($term) => !in_array(strtolower($term), $stop_words)
        // );

        $query_scores = [];

        foreach ($query as $term) {
            $query_scores[$term] = $this->search_term($term);
        }
        $final_scores = [];
        foreach ($query_scores as $term => $score_set) {
            foreach ($score_set as $recipe_id => $score) {
                if ($score > 0) {
                    if (!isset($final_scores[$recipe_id])) {
                        $final_scores[$recipe_id] = $score;
                    } else {
                        $final_scores[$recipe_id] += $score;
                    }
                }
            }
        }

        arsort($final_scores);
        return array_keys($final_scores);
    }

    /**
     * @return array
     * @param array<int,mixed> $recipeData
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
     * @param array<int,mixed> $updateData
     * @return array
     */
    public function update(string $recipe_id, array $updateData): array
    {
        try {
            if (
                !isset($updateData) ||
                !is_array($updateData) ||
                empty($updateData) ||
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
                        "total_time",
                    ]
                ) !== []
            ) {
                error_log(
                    "Invalid updated recipe data: contains either inexistent or forbidden attributes"
                );
                return [];
            }
            $allRecipes = $this->getAll();
            $final_recipe = [];
            for ($i = 0; $i < count($allRecipes); $i++) {
                if ($allRecipes[$i]["id"] == $recipe_id) {
                    $final_recipe = $allRecipes[$i];
                    foreach ($updateData as $key => $value) {
                        //final check, you never know...
                        if (!in_array($key, array_keys($final_recipe), true)) {
                            error_log(
                                "Invalid updated recipe data: contains either inexistent or forbidden attributes"
                            );
                            return [];
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
                error_log("Recipe not found");
                return [];
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
     * @return array     */
    public function delete(string $recipe_id): array
    {
        try {
            $all_recipes = $this->getAll();

            $recipe_index = array_search(
                $recipe_id,
                array_column($all_recipes, "id"),
                true
            );
            if ($recipe_index !== false) {
                $deleted_recipe = $all_recipes[$recipe_index];
                unset($all_recipes[$recipe_index]);
                //reindexing
                $all_recipes = array_values($all_recipes);
                $this->json_handler->writeData(self::DATA_FILE, $all_recipes);
                return $deleted_recipe;
            } else {
                error_log("error deleting recipe:Recipe not found");
                return [];
            }
        } catch (Exception $e) {
            error_log("Error deleting recipe data: " . $e->getMessage());
            throw new Exception(
                "Error deleting recipe data: " . $e->getMessage()
            );
        }
    }

    /**
     * @return array
     */
    public function like(string $recipe_id): array
    {
        try {
            $all_recipes = $this->getAll();
            $recipe_index = array_search(
                $recipe_id,
                array_column($all_recipes, "id"),
                true
            );
            if ($recipe_index !== false) {
                $current_likes = $all_recipes[$recipe_index]["likes"];
                $all_recipes[$recipe_index]["likes"] = $current_likes + 1;
                $this->json_handler->writeData(self::DATA_FILE, $all_recipes);
                return $all_recipes[$recipe_index];
            } else {
                error_log("Recipe not found for liking");
                return [];
            }
        } catch (Exception $e) {
            error_log("Error liking recipe: " . $e->getMessage());
            throw new Exception("Error liking recipe: " . $e->getMessage());
        }
    }
    /**
     * @return array
     */
    public function unlike(string $recipe_id)
    {
        try {
            $all_recipes = $this->getAll();
            $recipe_index = array_search(
                $recipe_id,
                array_column($all_recipes, "id"),
                true
            );
            if ($recipe_index !== false) {
                $current_likes = $all_recipes[$recipe_index]["likes"];
                if ($current_likes > 0) {
                    $all_recipes[$recipe_index]["likes"] = $current_likes - 1;
                }
                $this->json_handler->writeData(self::DATA_FILE, $all_recipes);
                return $all_recipes[$recipe_index];
            } else {
                error_log("Recipe not found for unliking");
                return [];
            }
        } catch (Exception $e) {
            error_log("Error unliking recipe: " . $e->getMessage());
            throw new Exception("Error unliking recipe: " . $e->getMessage());
        }
    }

    /**
     * @return array
     * @param array<int,mixed> $translation
     */
    public function translate(string $recipe_id, array $translation): array
    {
        try {
            $all_recipes = $this->getAll();
            $recipe_index = array_search(
                $recipe_id,
                array_column($all_recipes, "id"),
                true
            );
            if ($recipe_index !== false) {
                if (
                    array_diff(array_keys($translation), [
                        "name",
                        "ingredients",
                        "steps",
                    ]) !== []
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
                error_log("Recipe not found for translation");
                return [];
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
    public function setPhoto(
        string $recipe_id,
        string $photo_id,
        &$final_url
    ): array {
        try {
            if (empty($photo_id)) {
                throw new Exception("Photo ID is empty");
            }
            $all_recipes = $this->getAll();
            $recipe_index = array_search(
                $recipe_id,
                array_column($all_recipes, "id"),
                true
            );
            if ($recipe_index === false) {
                throw new Exception("Recipe not found");
            }
            $recipe = $all_recipes[$recipe_index];
            $photo_index = array_search(
                $photo_id,
                array_column($recipe["photos"], "id"),
                true
            );

            if ($photo_index === false) {
                error_log("Photo not found");
                return [];
            }
            $recipe["photos"][$photo_index]["is_main"] = true;
            $old_photo_index = array_search(
                $recipe["imageURL"],
                array_column($recipe["photos"], "url"),
                true
            );
            if ($old_photo_index === false) {
                error_log("Current photo couldn't be found");
                return [];
            }
            $recipe["photos"][$old_photo_index]["is_main"] = false;
            $new_main_photo = $recipe["photos"][$photo_index];
            if ($recipe["imageURL"] === $new_main_photo["url"]) {
                error_log("Image is already main");
                throw new InvalidArgumentException("Image is already main");
            }
            $recipe["imageURL"] = $new_main_photo["url"];
            $final_url = $new_main_photo["url"];
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
            throw $e;
        }
    }

    /**
     * @return array     */
    public function publish(string $recipe_id): array
    {
        try {
            $all_recipes = $this->getAll();
            $recipe_index = array_search(
                $recipe_id,
                array_column($all_recipes, "id"),
                true
            );
            if ($recipe_index === false) {
                error_log("Recipe not found");
                return [];
            }
            $recipe = $all_recipes[$recipe_index];
            if ($recipe["status"] == "draft") {
                $recipe["status"] = "published";
            } else {
                error_log("Recipe is already published");
                return [];
            }
            if (!Validator::validateRecipe($recipe)) {
                throw new Exception(
                    "Error after publishing recipe: result is not valid"
                );
            }
            $all_recipes[$recipe_index] = $recipe;
            $this->json_handler->writeData(self::DATA_FILE, $all_recipes);
            return $recipe;
        } catch (Exception $e) {
            error_log("Error publishing recipe: " . $e->getMessage());
            throw new Exception("Error publishing recipe: " . $e->getMessage());
        }
    }

    /**
     * @return array     */
    public function isAuthor(string $user_id, string $recipe_id): bool
    {
        try {
            $recipe = $this->getById($recipe_id);
            return $recipe["Author"] === $user_id;
        } catch (Exception $e) {
            error_log("Error checking author: " . $e->getMessage());
            throw new Exception("Error checking author: " . $e->getMessage());
        }
    }
}
