<?php

class Validator
{
    /**
     * @param mixed $email
     * @return mixed
     */
    public static function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    /**
     * @param mixed $password
     */
    public static function validatePassword($password): bool
    {
        //password has to contain at least one uppercase letter, one lowercase letter, one number and has to be at least 8 characters long
        return strlen($password) >= 8 &&
            preg_match("/[A-Z]/", $password) &&
            preg_match("/[a-z]/", $password) &&
            preg_match("/[0-9]/", $password);
    }
    /**
     * @param mixed $username
     * @return int|bool
     */
    public static function validateUsername(&$username): bool
    {
        $username = strip_tags($username);
        $username = trim($username);
        $username = strtolower($username);

        //username has to contain only letters, numbers and underscores and has to be between 5 and 20 characters long
        return preg_match('/^[a-zA-Z0-9_]{5,20}$/', $username);
    }

    /**
     * @param mixed $photo
     * @return bool
     */
    public static function validatePhoto($photo): bool
    {
        return is_array($photo) &&
            isset($photo["id"]) &&
            isset($photo["url"]) &&
            isset($photo["user_id"]) &&
            isset($photo["username"]) &&
            isset($photo["is_main"]) &&
            isset($photo["created_at"]);
    }

    /**
     * @param mixed $photos
     * @return bool
     */
    public static function validatePhotos($photos): bool
    {
        if (!isset($photos)) {
            return false;
        }
        $is_valid = is_array($photos);
        if (empty($photos)) {
            return $is_valid;
        } else {
            foreach ($photos as $photo) {
                $is_valid = $is_valid && self::validatePhoto($photo);
            }
            return $is_valid;
        }
    }

    /**
     * @param mixed $ingredient
     * @return bool
     */
    public static function validateIngredient($ingredient): bool
    {
        return is_array($ingredient) &&
            isset($ingredient["quantity"]) &&
            isset($ingredient["name"]) &&
            isset($ingredient["type"]);
    }

    /**
     * @param mixed $ingredients
     * @return bool
     * @param array<int,mixed> $steps
     * @param string $name
     */
    public static function validateLanguage(
        $ingredients,
        array $steps,
        string $name
    ): bool {
        //to check that if the ingredients exist in in a language, the steps and the name also exist in the same language (e.g. can't have ingredients in French only and steps or name in English only)
        if (!isset($steps) || !is_array($steps) || empty($steps)) {
            return false;
        }
        if (!isset($name) || empty($name) || !is_string($name)) {
            return false;
        }
        if (!isset($ingredients)) {
            return false;
        }
        $is_valid = is_array($ingredients);
        if (empty($ingredients)) {
            return $is_valid;
        } else {
            foreach ($ingredients as $ingredient) {
                $is_valid = $is_valid && self::validateIngredient($ingredient);
            }
            return $is_valid;
        }
    }

    /**
     * @param mixed $comment
     * @return bool
     */
    //comment is passed by reference to sanitize its content
    public static function validateComment(&$comment): bool
    {
        $is_valid =
            is_array($comment) &&
            isset($comment["id"]) &&
            isset($comment["content"]) &&
            isset($comment["user_id"]) &&
            isset($comment["created_at"]) &&
            isset($comment["username"]);
        if ($is_valid) {
            $comment["content"] = strip_tags($comment["content"]);
            $comment["content"] = htmlspecialchars(
                $comment["content"],
                ENT_QUOTES,
                "UTF-8"
            );
            $comment["content"] = trim($comment["content"]);
        }
        return $is_valid;
    }

    /**
     * @param mixed $comments
     * @return bool
     */
    public static function validateComments($comments): bool
    {
        if (!isset($comments)) {
            return false;
        }
        $is_valid = is_array($comments);
        if (empty($comments)) {
            return $is_valid;
        } else {
            foreach ($comments as &$comment) {
                $is_valid = $is_valid && self::validateComment($comment);
            }
            return $is_valid;
        }
    }

    /**
     * @param mixed $recipe
     */
    public static function validateRecipe($recipe): bool
    {
        return isset($recipe["id"]) &&
            (self::validateLanguage(
                $recipe["ingredients"],
                $recipe["steps"],
                $recipe["name"]
            ) ||
                self::validateLanguage(
                    $recipe["ingredientsFR"],
                    $recipe["stepsFR"],
                    $recipe["nameFR"]
                )) &&
            isset($recipe["likes"]) &&
            isset($recipe["status"]) &&
            in_array($recipe["status"], ["draft", "published"]) &&
            isset($recipe["Author"]) &&
            is_array($recipe["Without"]) &&
            (isset($recipe["Without"])
                ? (!empty($recipe["Without"])
                    ? array_diff($recipe["Without"], [
                            "NoGluten",
                            "NoMilk",
                            "Vegan",
                            "Vegetarian",
                        ]) == []
                    : true)
                : false) &&
            isset($recipe["imageURL"]) &&
            isset($recipe["originalURL"]) &&
            isset($recipe["comments"]) &&
            self::validateComments($recipe["comments"]) &&
            self::validatePhotos($recipe["photos"]) &&
            isset($recipe["total_time"]) &&
            isset($recipe["created_at"]);
    }

    public static function validateRecipeUser($recipe): bool
    {
        return isset($recipe["id"]) &&
            (isset($recipe["name"]) || isset($recipe["nameFR"])) &&
            isset($recipe["imageURL"]) &&
            isset($recipe["likes"]);
    }

    public static function validateRecipesUser($recipes): bool
    {
        if (!isset($recipes)) {
            return false;
        }
        $is_valid = is_array($recipes);
        if (empty($recipes)) {
            return $is_valid;
        } else {
            foreach ($recipes as $recipe) {
                $is_valid = $is_valid && self::validateRecipesUser($recipe);
            }
            return $is_valid;
        }
    }

    /**
     * @param mixed $photo
     * @return bool
     */
    public static function validatePhotoUser($photo): bool
    {
        return is_array($photo) &&
            isset($photo["id"]) &&
            isset($photo["recipe_id"]) &&
            isset($photo["recipe_name"]) &&
            isset($photo["url"]) &&
            isset($photo["is_main"]) &&
            isset($photo["created_at"]);
    }

    /**
     * @param mixed $photos
     * @return bool
     */
    public static function validatePhotosUser($photos): bool
    {
        if (!isset($photos)) {
            return false;
        }
        $is_valid = is_array($photos);
        if (empty($photos)) {
            return $is_valid;
        } else {
            foreach ($photos as $photo) {
                $is_valid = $is_valid && self::validatePhotoUser($photo);
            }
            return $is_valid;
        }
    }

    /**
     * @param mixed $comment
     * @return bool
     */
    public static function validateCommentUser($comment): bool
    {
        $is_valid =
            is_array($comment) &&
            isset($comment["id"]) &&
            isset($comment["user_id"]) &&
            isset($comment["username"]) &&
            isset($comment["content"]) &&
            isset($comment["created_at"]);
        if ($is_valid) {
            $comment["content"] = strip_tags($comment["content"]);
            $comment["content"] = htmlspecialchars(
                $comment["content"],
                ENT_QUOTES,
                "UTF-8"
            );
            $comment["content"] = trim($comment["content"]);
        }
        return $is_valid;
    }

    /**
     * @param mixed $comments
     * @return bool
     */
    public static function validateCommentsUser($comments): bool
    {
        if (!isset($comments)) {
            return false;
        }
        $is_valid = is_array($comments);
        if (empty($comments)) {
            return $is_valid;
        } else {
            foreach ($comments as &$comment) {
                $is_valid = $is_valid && self::validateCommentUser($comment);
            }
            return $is_valid;
        }
    }

    /**
     * @param mixed $user
     */
    public static function validateUser($user): bool
    {
        return isset($user["email"]) &&
            isset($user["username"]) &&
            self::validateEmail($user["email"]) &&
            self::validateUsername($user["username"]) &&
            isset($user["password"]) &&
            isset($user["likes"]) &&
            is_array($user["likes"]) &&
            isset($user["role"]) &&
            in_array($user["role"], [
                "Cuisinier",
                "Chef",
                "Traducteur",
                "Administrateur",
                "DemandeChef",
                "DemandeTraducteur",
            ]) &&
            isset($user["created_at"]) &&
            self::validateRecipesUser($user["recipes"]) &&
            self::validateCommentsUser($user["comments"]) &&
            self::validatePhotosUser($user["photos"]);
    }

    /**
     * @param mixed $token
     */
    //not sure about this...
    public static function validateToken($token): bool
    {
        return isset($token["id"]) && isset($token["token"]);
    }
}
