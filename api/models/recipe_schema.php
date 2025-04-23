<?php

class RecipeSchema
{
    //local model
    public $id = null;
    public $name = null;
    public $nameFR = null;
    public $author = null;
    public array $without = [];
    public array $ingredients = [];
    public array $ingredientsFR = [];
    public array $steps = [];
    public array $stepsFR = [];
    public array $timers = [];
    public $imageURL = null;
    public $originalURL = null;
    public $likes = 0;
    public $status = "draft";
    public array $comments = [];
    public array $photos = [];
    public $total_time = 0;

    private JSONHandler $json_handler;
    private const DATA_FILE = "recipes.json";

    public function __construct(JSONHandler $json_handler)
    {
        $this->json_handler = $json_handler;
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
            $recipes = $this->json_handler->readData(self::DATA_FILE);
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
        return $final_scores;
    }

    /**
     * @return array     */
    public function getById(string $recipe_id): array
    {
        try {
            $recipes = $this->json_handler->readData(self::DATA_FILE);
            $recipe = $recipes[$recipe_id];
            return $recipe;
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }
    /**
     * @return array     */
    public function create(): array
    {
        $recipe_id = uniqid();
        $recipe = [
            "id" => $recipe_id,
            "title" => "",
            "description" => "",
            "ingredients" => [],
            "instructions" => [],
            "tags" => [],
            "likes" => 0,
            "views" => 0,
            "created_at" => time(),
            "updated_at" => time(),
        ];
        $this->json_handler->writeData(self::DATA_FILE, $recipe);
        return $recipe;
    }
    /**
     * @return array     */
    public function update(string $recipe_id): array {}
    /**
     * @return array     */
    public function delete(string $recipe_id): array {}
    /**
     * @return array     */
    public function like(string $recipe_id): array {}
    /**
     * @return array     */
    public function translate(string $recipe_id): array {}
    /**
     * @return array     */
    public function setPhoto(string $recipe_id): array {}
}
