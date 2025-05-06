<?php

class Validator
{
    /**
     * Validates an email address.
     * @param mixed $email The email to validate.
     * @return bool True if the email is valid, false otherwise.
     */
    public static function validateEmail($email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validates a password based on complexity rules.
     * @param mixed $password The password to validate.
     * @return bool True if the password meets complexity requirements, false otherwise.
     */
    public static function validatePassword($password): bool
    {
        return is_string($password) &&
            strlen($password) >= 8 &&
            preg_match("/[A-Z]/", $password) &&
            preg_match("/[a-z]/", $password) &&
            preg_match("/[0-9]/", $password);
    }

    /**
     * Validates and sanitizes a username.
     * @param mixed $username The username to validate (passed by reference for sanitization).
     * @return bool True if the username is valid after sanitization, false otherwise.
     */
    public static function validateUsername(&$username): bool
    {
        if (!is_string($username)) {
            return false;
        }

        $username = strip_tags($username);
        $username = trim($username);
        $username = strtolower($username);

        return preg_match('/^[a-zA-Z0-9_]{5,20}$/', $username);
    }

    /**
     * Validates the structure of a photo object within a recipe context.
     * @param mixed $photo The photo data array.
     * @return bool True if the structure is valid, false otherwise.
     */
    public static function validatePhoto($photo): bool
    {
        return is_array($photo) &&
            isset($photo["id"]) &&
            is_string($photo["id"]) &&
            isset($photo["url"]) &&
            is_string($photo["url"]) &&
            isset($photo["user_id"]) &&
            is_string($photo["user_id"]) &&
            isset($photo["username"]) &&
            is_string($photo["username"]) &&
            isset($photo["is_main"]) &&
            is_bool($photo["is_main"]) &&
            isset($photo["created_at"]) &&
            is_numeric($photo["created_at"]);
    }

    /**
     * Validates an array of photo objects (within a recipe context).
     * @param mixed $photos The array of photo data.
     * @return bool True if the array and its elements are valid, false otherwise.
     */
    public static function validatePhotos($photos): bool
    {
        if (!isset($photos)) {
            return false;
        }
        if (!is_array($photos)) {
            return false;
        }

        if (empty($photos)) {
            return true;
        }

        foreach ($photos as $photo) {
            if (!self::validatePhoto($photo)) {
                error_log(
                    "Invalid photo structure found in recipe: " .
                        json_encode($photo)
                );
                return false;
            }
        }
        return true;
    }

    /**
     * Validates the structure of an ingredient object.
     * @param mixed $ingredient The ingredient data array.
     * @return bool True if the structure is valid, false otherwise.
     */
    public static function validateIngredient($ingredient): bool
    {
        return is_array($ingredient) &&
            isset($ingredient["quantity"]) &&
            is_string($ingredient["quantity"]) &&
            isset($ingredient["name"]) &&
            is_string($ingredient["name"]) &&
            isset($ingredient["type"]) &&
            is_string($ingredient["type"]);
    }

    /**
     * Validates if ingredients, steps, and name exist for a specific language within a recipe.
     * @param mixed $ingredients The ingredients array for the language.
     * @param mixed $steps The steps array for the language.
     * @param mixed $name The name string for the language.
     * @return bool True if all components are present and validly structured for the language, false otherwise.
     */
    public static function validateLanguage($ingredients, $steps, $name): bool
    {
        if (!isset($name) && !isset($ingredients) && !isset($steps)) {
            return false;
        }

        if (isset($name) || isset($ingredients) || isset($steps)) {
            if (!isset($name) || !is_string($name) || empty(trim($name))) {
                error_log(
                    "Validation failed: Name missing or invalid for a language."
                );
                return false;
            }
            if (!isset($steps) || !is_array($steps)) {
                error_log(
                    "Validation failed: Steps missing or not an array for a language."
                );
                return false;
            }
            if (!isset($ingredients) || !is_array($ingredients)) {
                error_log(
                    "Validation failed: Ingredients missing or not an array for a language."
                );
                return false;
            }

            if (!empty($ingredients)) {
                foreach ($ingredients as $ingredient) {
                    if (!self::validateIngredient($ingredient)) {
                        error_log(
                            "Validation failed: Invalid ingredient structure for a language."
                        );
                        return false;
                    }
                }
            }

            if (!empty($steps)) {
                foreach ($steps as $step) {
                    if (!is_string($step) || empty(trim($step))) {
                        error_log(
                            "Validation failed: Invalid step found (not a non-empty string)."
                        );
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Validates and sanitizes a comment object within a recipe context.
     * @param mixed $comment The comment data array (passed by reference).
     * @return bool True if the structure is valid, false otherwise.
     */

    public static function validateComment(&$comment): bool
    {
        $is_valid =
            is_array($comment) &&
            isset($comment["id"]) &&
            is_string($comment["id"]) &&
            isset($comment["content"]) &&
            is_string($comment["content"]) &&
            isset($comment["user_id"]) &&
            is_string($comment["user_id"]) &&
            isset($comment["created_at"]) &&
            is_numeric($comment["created_at"]) &&
            isset($comment["username"]) &&
            is_string($comment["username"]);

        if ($is_valid && !empty($comment["content"])) {
            $comment["content"] = strip_tags($comment["content"]);
            $comment["content"] = htmlspecialchars(
                $comment["content"],
                ENT_QUOTES,
                "UTF-8"
            );
            $comment["content"] = trim($comment["content"]);

            if (empty($comment["content"])) {
                error_log(
                    "Validation failed: Comment content became empty after sanitization."
                );
                return false;
            }
        } elseif (!$is_valid) {
            error_log("Invalid comment structure: " . json_encode($comment));
        } elseif (empty($comment["content"])) {
            error_log("Validation failed: Comment content is empty.");
        }
        return $is_valid;
    }

    /**
     * Validates an array of comment objects (within a recipe context).
     * @param mixed $comments The array of comment data.
     * @return bool True if the array and its elements are valid, false otherwise.
     */
    public static function validateComments($comments): bool
    {
        if (!isset($comments)) {
            error_log("Validation failed: comments key missing in recipe.");
            return false;
        }
        if (!is_array($comments)) {
            error_log("Validation failed: comments is not an array.");
            return false;
        }

        if (empty($comments)) {
            return true;
        }

        foreach ($comments as &$comment) {
            if (!self::validateComment($comment)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Validates the overall structure of a recipe object.
     * @param mixed $recipe The recipe data array.
     * @return bool True if the recipe structure is valid, false otherwise.
     */
    public static function validateRecipe($recipe): bool
    {
        if (!is_array($recipe)) {
            return false;
        }

        $required_keys = [
            "id",
            "Author",
            "Without",
            "likes",
            "status",
            "imageURL",
            "originalURL",
            "comments",
            "photos",
            "total_time",
            "created_at",
        ];
        foreach ($required_keys as $key) {
            if (!isset($recipe[$key])) {
                error_log("Validation failed: Recipe missing key '$key'.");
                return false;
            }
        }

        if (!is_string($recipe["id"]) || empty($recipe["id"])) {
            return false;
        }
        if (!is_string($recipe["Author"]) || empty($recipe["Author"])) {
            return false;
        }
        if (!is_numeric($recipe["likes"]) || $recipe["likes"] < 0) {
            return false;
        }
        if (!in_array($recipe["status"], ["draft", "published"])) {
            return false;
        }
        if (!is_array($recipe["Without"])) {
            return false;
        }
        if (!is_string($recipe["imageURL"])) {
            return false;
        }
        if (!is_string($recipe["originalURL"])) {
            return false;
        }
        if (!is_numeric($recipe["total_time"])) {
            return false;
        }
        if (!is_numeric($recipe["created_at"])) {
            return false;
        }

        $validWithout = ["NoGluten", "NoMilk", "Vegan", "Vegetarian"];
        if (!empty($recipe["Without"])) {
            if (array_diff($recipe["Without"], $validWithout) !== []) {
                error_log(
                    "Validation failed: Invalid value found in 'Without' array."
                );
                return false;
            }
        }

        $has_en =
            isset($recipe["name"]) ||
            isset($recipe["ingredients"]) ||
            isset($recipe["steps"]);
        $has_fr =
            isset($recipe["nameFR"]) ||
            isset($recipe["ingredientsFR"]) ||
            isset($recipe["stepsFR"]);

        if (!$has_en && !$has_fr) {
            error_log(
                "Validation failed: Recipe must have at least one language version (EN or FR)."
            );
            return false;
        }

        $is_en_valid = $has_en
            ? self::validateLanguage(
                $recipe["ingredients"] ?? null,
                $recipe["steps"] ?? null,
                $recipe["name"] ?? null
            )
            : true;

        $is_fr_valid = $has_fr
            ? self::validateLanguage(
                $recipe["ingredientsFR"] ?? null,
                $recipe["stepsFR"] ?? null,
                $recipe["nameFR"] ?? null
            )
            : true;

        if (!$is_en_valid || !$is_fr_valid) {
            return false;
        }

        if (!self::validateComments($recipe["comments"])) {
            return false;
        }
        if (!self::validatePhotos($recipe["photos"])) {
            return false;
        }

        return true;
    }

    /**
     * Validates the simplified recipe structure stored within a user object.
     * @param mixed $recipe The simplified recipe data array.
     * @return bool True if the structure is valid, false otherwise.
     */
    public static function validateRecipeUser($recipe): bool
    {
        return is_array($recipe) &&
            isset($recipe["id"]) &&
            is_string($recipe["id"]) &&
            ((isset($recipe["name"]) && is_string($recipe["name"])) ||
                (isset($recipe["nameFR"]) && is_string($recipe["nameFR"]))) &&
            isset($recipe["imageURL"]) &&
            is_string($recipe["imageURL"]) &&
            isset($recipe["likes"]) &&
            is_numeric($recipe["likes"]) &&
            $recipe["likes"] >= 0;
    }

    /**
     * Validates an array of simplified recipe objects (within a user object).
     * @param mixed $recipes The array of simplified recipe data.
     * @return bool True if the array and its elements are valid, false otherwise.
     */
    public static function validateRecipesUser($recipes): bool
    {
        if (!isset($recipes)) {
            error_log("Validation failed: recipes key missing in user.");
            return false;
        }
        if (!is_array($recipes)) {
            error_log("Validation failed: user recipes is not an array.");
            return false;
        }

        if (empty($recipes)) {
            return true;
        }

        foreach ($recipes as $recipe) {
            if (!self::validateRecipeUser($recipe)) {
                error_log(
                    "Invalid recipe structure found in user's recipes: " .
                        json_encode($recipe)
                );
                return false;
            }
        }
        return true;
    }

    /**
     * Validates the simplified photo structure stored within a user object.
     * @param mixed $photo The simplified photo data array.
     * @return bool True if the structure is valid, false otherwise.
     */
    public static function validatePhotoUser($photo): bool
    {
        return is_array($photo) &&
            isset($photo["id"]) &&
            is_string($photo["id"]) &&
            isset($photo["url"]) &&
            is_string($photo["url"]) &&
            isset($photo["is_main"]) &&
            is_bool($photo["is_main"]) &&
            isset($photo["created_at"]) &&
            is_numeric($photo["created_at"]);
    }

    /**
     * Validates an array of simplified photo objects (within a user object).
     * @param mixed $photos The array of simplified photo data.
     * @return bool True if the array and its elements are valid, false otherwise.
     */
    public static function validatePhotosUser($photos): bool
    {
        if (!isset($photos)) {
            error_log("Validation failed: photos key missing in user.");
            return false;
        }
        if (!is_array($photos)) {
            error_log("Validation failed: user photos is not an array.");
            return false;
        }

        if (empty($photos)) {
            return true;
        }

        foreach ($photos as $photo) {
            if (!self::validatePhotoUser($photo)) {
                error_log(
                    "Invalid photo structure found in user's photos: " .
                        json_encode($photo)
                );
                return false;
            }
        }
        return true;
    }

    /**
     * Validates the simplified comment structure stored within a user object.
     * @param mixed $comment The simplified comment data array (passed by reference).
     * @return bool True if the structure is valid, false otherwise.
     */
    public static function validateCommentUser(&$comment): bool
    {
        $is_valid =
            is_array($comment) &&
            isset($comment["id"]) &&
            is_string($comment["id"]) &&
            isset($comment["content"]) &&
            is_string($comment["content"]) &&
            isset($comment["created_at"]) &&
            is_numeric($comment["created_at"]);

        if ($is_valid && !empty($comment["content"])) {
            $original_content = $comment["content"];
            $comment["content"] = strip_tags($comment["content"]);
            $comment["content"] = htmlspecialchars(
                $comment["content"],
                ENT_QUOTES,
                "UTF-8"
            );
            $comment["content"] = trim($comment["content"]);
            if (empty($comment["content"])) {
                error_log(
                    "Validation failed: User comment content became empty after sanitization. Original: " .
                        $original_content
                );
            }
        } elseif (!$is_valid) {
            error_log(
                "Invalid comment structure in user's comments: " .
                    json_encode($comment)
            );
        } elseif (empty($comment["content"])) {
            error_log("Validation failed: User comment content is empty.");
        }
        return $is_valid;
    }

    /**
     * Validates an array of simplified comment objects (within a user object).
     * @param mixed $comments The array of simplified comment data.
     * @return bool True if the array and its elements are valid, false otherwise.
     */
    public static function validateCommentsUser($comments): bool
    {
        if (!isset($comments)) {
            error_log("Validation failed: comments key missing in user.");
            return false;
        }
        if (!is_array($comments)) {
            error_log("Validation failed: user comments is not an array.");
            return false;
        }

        if (empty($comments)) {
            return true;
        }

        foreach ($comments as &$comment) {
            if (!self::validateCommentUser($comment)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Validates the overall structure of a user object.
     * @param mixed $user The user data array.
     * @return bool True if the user structure is valid, false otherwise.
     */
    public static function validateUser($user): bool
    {
        if (!is_array($user)) {
            error_log("Validation failed: User data is not an array.");
            return false;
        }

        if (
            !isset($user["roles"]) ||
            !is_array($user["roles"]) ||
            empty($user["roles"])
        ) {
            error_log(
                "Validation failed: User roles are missing, not an array, or empty."
            );
            return false;
        }
        $validRoles = [
            "Cuisinier",
            "Chef",
            "Traducteur",
            "Administrateur",
            "DemandeChef",
            "DemandeTraducteur",
        ];
        foreach ($user["roles"] as $role) {
            if (!in_array($role, $validRoles)) {
                error_log("Validation failed: Invalid role found: " . $role);
                return false;
            }
        }

        if (
            in_array("Cuisinier", $user["roles"]) &&
            in_array("Administrateur", $user["roles"])
        ) {
            error_log(
                "Validation failed: User cannot be both Cuisinier and Administrateur."
            );
            return false;
        }
        if (
            in_array("Cuisinier", $user["roles"]) &&
            in_array("Chef", $user["roles"])
        ) {
            error_log(
                "Validation failed: User cannot be both Cuisinier and Chef."
            );
            return false;
        }

        $required_keys = [
            "id",
            "email",
            "username",
            "password",
            "likes",
            "created_at",
            "recipes",
            "comments",
            "photos",
        ];
        foreach ($required_keys as $key) {
            if (!isset($user[$key])) {
                error_log("Validation failed: User missing key '$key'.");
                return false;
            }
        }

        $username_copy = $user["username"];
        if (!self::validateEmail($user["email"])) {
            error_log(
                "Validation failed: Invalid email format for user: " .
                    $user["email"]
            );
            return false;
        }
        if (!self::validateUsername($username_copy)) {
            error_log(
                "Validation failed: Invalid username format for user: " .
                    $user["username"]
            );
            return false;
        }
        if (!is_string($user["password"]) || empty($user["password"])) {
            error_log(
                "Validation failed: User password missing or not a string."
            );
            return false;
        }
        if (!is_array($user["likes"])) {
            error_log("Validation failed: User likes is not an array.");
            return false;
        }
        if (!is_numeric($user["created_at"])) {
            error_log("Validation failed: User created_at is not numeric.");
            return false;
        }

        if (!self::validateRecipesUser($user["recipes"])) {
            error_log("Validation failed: User recipes array is invalid.");
            return false;
        }
        if (!self::validateCommentsUser($user["comments"])) {
            error_log("Validation failed: User comments array is invalid.");
            return false;
        }
        if (!self::validatePhotosUser($user["photos"])) {
            error_log("Validation failed: User photos array is invalid.");
            return false;
        }

        foreach ($user["likes"] as $like) {
            if (!is_string($like)) {
                error_log(
                    "Validation failed: Non-string value found in user likes array."
                );
                return false;
            }
        }

        return true;
    }

    /**
     * Validates a generic token structure (example).
     * @param mixed $token The token data array.
     * @return bool True if the structure is valid, false otherwise.
     */

    public static function validateToken($token): bool
    {
        return is_array($token) &&
            isset($token["id"]) &&
            isset($token["token"]);
    }
}
