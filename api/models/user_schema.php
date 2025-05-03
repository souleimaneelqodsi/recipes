<?php

class UserSchema
{
    public $id;
    public $username;
    public $email;
    public $role;
    public $created_at;
    public array $recipes;
    public array $comments;
    public array $photos;
    public array $likes;

    private JSONHandler $json_handler;
    private const DATA_FILE = "users.json";

    public function __construct(
        JSONHandler $json_handler,
        string $username,
        string $email
    ) {
        $this->id = Utils::uuid4();
        $this->username = $username;
        $this->email = $email;
        $this->role = "Cuisinier";
        $this->created_at = time();
        $this->recipes = [];
        $this->comments = [];
        $this->photos = [];
        $this->likes = [];
        $this->json_handler = $json_handler;
    }

    /**
     * @param array<string,mixed> $data
     */
    public function toArray(): mixed
    {
        return [
            "id" => $this->id,
            "username" => $this->username,
            "email" => $this->email,
            "role" => $this->role,
            "created_at" => $this->created_at,
            "recipes" => $this->recipes,
            "comments" => $this->comments,
            "photos" => $this->photos,
            "likes" => $this->likes,
        ];
    }

    /**
     * @param array<string,mixed> $data
     */
    public function fromArray(array $data): void
    {
        $this->id = $data["id"];
        $this->username = $data["username"];
        $this->email = $data["email"];
        $this->role = $data["role"];
        $this->created_at = $data["created_at"];
        $this->recipes = $data["recipes"];
        $this->comments = $data["comments"];
        $this->photos = $data["photos"];
        $this->likes = $data["likes"];
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        try {
            $all_users = $this->json_handler->readData(self::DATA_FILE);
            return $all_users;
        } catch (Exception $e) {
            error_log("Failed to fetch users." . $e->getMessage());
            throw $e;
        }
    }

    /**
     * @return array
     */
    public function getById(string $user_id): array
    {
        try {
            $all_users = $this->getAll();
            $user_index = array_search(
                $user_id,
                array_column($all_users, "id"),
                true
            );
            if ($user_index === false) {
                error_log("User not found");
                return [];
            }
            $user = $all_users[$user_index];
            $this->fromArray($user);
            return $user;
        } catch (Exception $e) {
            error_log("User not found");
            throw $e;
        }
    }
    /**
     * @return array
     */
    public function create(string $username, string $email): array
    {
        try {
            $user = new UserSchema($this->json_handler, $username, $email);
            $usr_array = $user->toArray();
            $all_users = $this->getAll();
            $username_exists = array_search(
                $username,
                array_column($all_users, "username"),
                true
            );
            if ($username_exists !== false) {
                error_log("Username already exists");
                throw new UsernameAlreadyExistsException();
            }
            $email_exists = array_search(
                $email,
                array_column($all_users, "email"),
                true
            );
            if ($email_exists !== false) {
                error_log("Email already exists");
                throw new EmailAlreadyExistsException();
            }
            if (Validator::validateUser($usr_array)) {
                $this->fromArray($usr_array);
                array_push($all_users, $usr_array);
                $this->json_handler->writeData(
                    UserSchema::DATA_FILE,
                    $all_users
                );
                return $usr_array;
            } else {
                error_log("User creation failed: invalid user data");
                throw new Exception("Invalid user data");
            }
        } catch (Exception $e) {
            error_log("User creation failed");
            throw $e;
        }
    }
    /**
     * @return array
     */

    public function addRecipe($recipeData): array
    {
        try {
            if (!Session::isLoggedIn()) {
                throw new Exception("User not logged in");
            }
            if (!Validator::validateRecipeUser($recipeData)) {
                error_log("Incorrect recipe inputData");
                return [];
            } else {
                $all_users = $this->getAll();
                $usr_index = array_search(
                    $this->id,
                    array_column($all_users, "id"),
                    true
                );
                if ($usr_index === false) {
                    throw new Exception("User not found");
                }
                array_push($all_users[$usr_index]["recipes"], $recipeData);
                $this->json_handler->writeData(
                    UserSchema::DATA_FILE,
                    $all_users
                );
                return $all_users[$usr_index];
            }
        } catch (Exception $e) {
            error_log("Recipe addition failed");
            throw $e;
        }
    }
    /**
     * @return array
     */
    public function editRecipe(
        string $recipe_id,
        string $user_id,
        $name = null,
        $nameFR = null,
        $imageURL = null,
        $likes = null
    ): array {
        try {
            if (
                $name === null &&
                $nameFR === null &&
                $imageURL == null &&
                $likes === null
            ) {
                throw new Exception("Invalid recipe editing function input.");
            } else {
                $input_params = [$name, $nameFR, $imageURL, $likes];
                foreach ($input_params as $input) {
                    if ($input === null) {
                        continue;
                    }
                    if (
                        (($input === $nameFR ||
                            $input === $name ||
                            $input === $imageURL) &&
                            empty($input)) ||
                        ($input === $likes && $input < 0)
                    ) {
                        throw new Exception(
                            "Invalid recipe editing function input."
                        );
                    }
                    $all_users = $this->getAll();
                    $usr_index = array_search(
                        $user_id,
                        array_column($all_users, "id"),
                        true
                    );
                    if ($usr_index === false) {
                        throw new Exception("User not found");
                    }
                    $usr = $all_users[$usr_index];
                    $usr_recipes = $usr["recipes"];
                    $recipe_index = array_search(
                        $recipe_id,
                        array_column($usr_recipes, "id"),
                        true
                    );
                    if ($recipe_index === false) {
                        throw new Exception("Recipe not found");
                    }
                    switch ($input) {
                        case $name:
                            $usr_recipes[$recipe_index]["name"] = $name;
                            break;
                        case $nameFR:
                            $usr_recipes[$recipe_index]["nameFR"] = $nameFR;
                            break;
                        case $imageURL:
                            $usr_recipes[$recipe_index]["imageURL"] = $imageURL;
                            break;
                        case $likes:
                            $usr_recipes[$recipe_index]["likes"] = $likes;
                            break;
                        default:
                            break;
                    }
                    // if ($input == $name) {
                    //     $usr_recipes[$recipe_index]["name"] = $name;
                    // } elseif ($input == $nameFR) {
                    //     $usr_recipes[$recipe_index]["nameFR"] = $nameFR;
                    // } elseif ($input == $imageURL) {
                    //     $usr_recipes[$recipe_index]["imageURL"] = $imageURL;
                    // } else {
                    //     $usr_recipes[$recipe_index]["likes"] = $likes;
                    // }
                    $usr["recipes"] = $usr_recipes;
                    $this->recipes = $usr_recipes;
                    $all_users[$usr_index] = $usr;
                    $this->json_handler->writeData(
                        UserSchema::DATA_FILE,
                        $all_users
                    );
                }
                //possible because in PHP variables don't have block scope
                return $all_users[$usr_index];
            }
        } catch (Exception $e) {
            error_log("Recipe addition failed");
            throw $e;
        }
    }
    /**
     * @return array
     */
    public function deleteRecipe($recipe_id): array
    {
        if (!Session::isLoggedIn()) {
            throw new Exception("User not logged in");
        }
        try {
            $all_users = $this->getAll();
            $usr_index = array_search(
                $this->id,
                array_column($all_users, "id"),
                true
            );
            if ($usr_index === false) {
                error_log("User not found");
                return [];
            }
            $usr_recipes = $all_users[$usr_index]["recipes"];
            $recipe_index = array_search(
                $recipe_id,
                array_column($usr_recipes, "id"),
                true
            );
            if ($recipe_index === false) {
                error_log("Recipe not found");
                return [];
            }
            unset($usr_recipes[$recipe_index]);
            $all_users[$usr_index]["recipes"] = $usr_recipes;
            $this->recipes = $usr_recipes;
            $this->json_handler->writeData(UserSchema::DATA_FILE, $all_users);
            return $all_users[$usr_index];
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }
    /**
     * @return array
     */
    public function addComment($comment_data): array
    {
        if (!Session::isLoggedIn()) {
            throw new Exception("User not logged in");
        }
        try {
            if (!Validator::validateCommentUser($comment_data)) {
                throw new Exception("Invalid input data");
            }
            $all_users = $this->getAll();
            $usr_index = array_search(
                $this->id,
                array_column($all_users, "id"),
                true
            );
            if ($usr_index === false) {
                error_log("User not found");
                return [];
            }
            $usr_comments = $all_users[$usr_index]["comments"];
            array_push($usr_comments, $comment_data);
            $all_users[$usr_index]["comments"] = $usr_comments;
            $this->comments = $usr_comments;
            $this->json_handler->writeData(UserSchema::DATA_FILE, $all_users);
            return $all_users[$usr_index];
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }
    /**
     * @return array
     */
    public function removeComment($comment_id): array
    {
        if (!Session::isLoggedIn()) {
            throw new Exception("User not logged in");
        }
        try {
            $all_users = $this->getAll();
            $usr_index = array_search(
                $this->id,
                array_column($all_users, "id"),
                true
            );
            if ($usr_index === false) {
                error_log("User not found");
                return [];
            }
            $usr_comments = $all_users[$usr_index]["comments"];
            $comment_index = array_search(
                $comment_id,
                array_column($usr_comments, "id"),
                true
            );
            if ($comment_index === false) {
                error_log("Comment not found");
                return [];
            }
            unset($usr_comments[$comment_index]);
            $all_users[$usr_index]["comments"] = $usr_comments;
            $this->comments = $usr_comments;
            $this->json_handler->writeData(UserSchema::DATA_FILE, $all_users);
            return $all_users[$usr_index];
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }
    /**
     * @return array
     */
    public function addPhoto($photo_data): array
    {
        if (!Session::isLoggedIn()) {
            throw new Exception("User not logged in");
        }
        try {
            if (!Validator::validatePhotoUser($photo_data)) {
                throw new Exception("Invalid input data");
            }
            $all_users = $this->getAll();
            $usr_index = array_search(
                $this->id,
                array_column($all_users, "id"),
                true
            );
            if ($usr_index === false) {
                error_log("User not found");
                return [];
            }
            $usr_photos = $all_users[$usr_index]["photos"];
            array_push($usr_photos, $photo_data);
            $all_users[$usr_index]["photos"] = $usr_photos;
            $this->photos = $usr_photos;
            $this->json_handler->writeData(UserSchema::DATA_FILE, $all_users);
            return $all_users[$usr_index];
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }
    /**
     * @return array
     */
    public function removePhoto($photo_id)
    {
        if (!Session::isLoggedIn()) {
            throw new Exception("User not logged in");
        }
        try {
            $all_users = $this->getAll();
            $usr_index = array_search(
                $this->id,
                array_column($all_users, "id"),
                true
            );
            if ($usr_index === false) {
                error_log("User not found");
                return [];
            }
            $usr_photos = $all_users[$usr_index]["photos"];
            $photo_index = array_search(
                $photo_id,
                array_column($usr_photos, "id"),
                true
            );
            if ($photo_index === false) {
                error_log("Photo not found");
                return [];
            }
            unset($usr_photos[$photo_index]);
            $all_users[$usr_index]["photos"] = $usr_photos;
            $this->photos = $usr_photos;
            $this->json_handler->writeData(UserSchema::DATA_FILE, $all_users);
            return $all_users[$usr_index];
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }

    /**
     * @return array
     */
    public function updateRole(string $user_id, string $role): array
    {
        if (
            !Session::isLoggedIn() ||
            Session::getUserRole() !== "Administrateur"
        ) {
            throw new Exception("User not logged in or not an administrator");
        }
        try {
            $all_users = $this->getAll();
            $usr_index = array_search(
                $user_id,
                array_column($all_users, "id"),
                true
            );
            if ($usr_index === false) {
                throw new Exception("User not found");
            }
            $current_usr_array = $this->toArray();
            $current_usr_array["role"] = $role;
            if (!Validator::validateUser($current_usr_array)) {
                error_log("Invalid role");
                throw new Exception("Invalid role");
            }
            $all_users[$usr_index]["role"] = $role;
            $this->role = $role;
            $this->json_handler->writeData(UserSchema::DATA_FILE, $all_users);
            return $current_usr_array;
        } catch (Exception $e) {
            error_log("User role update failed");
            throw $e;
        }
    }

    /**
     * @return array
     */
    public function like(string $recipe_id): array
    {
        if (!Session::isLoggedIn()) {
            throw new Exception("User not logged in");
        }
        try {
            $all_users = $this->getAll();
            $user_index = array_search(
                $this->id,
                array_column($all_users, "id"),
                true
            );
            if ($user_index === false) {
                throw new Exception("User not found");
            }
            $user = $all_users[$user_index];
            if (!in_array($recipe_id, $user["likes"], true)) {
                array_push($user["likes"], $recipe_id);
            } else {
                error_log("Recipe already liked");
                return [];
            }
            $all_users[$user_index] = $user;
            $this->likes = $user["likes"];
            $this->json_handler->writeData(self::DATA_FILE, $all_users);
            return $user;
        } catch (Exception $e) {
            throw $e;
        }
    }
    /**
     * @return array<mixed, mixed>
     */
    public function unlike(string $recipe_id): array
    {
        if (!Session::isLoggedIn()) {
            throw new Exception("User not logged in");
        }
        try {
            $all_users = $this->getAll();
            $user_index = array_search(
                $this->id,
                array_column($all_users, "id"),
                true
            );
            if ($user_index === false) {
                throw new Exception("User not found");
            }
            $user = $all_users[$user_index];
            if (in_array($recipe_id, $user["likes"], true)) {
                $recipe_index = array_search($recipe_id, $user["likes"], true);
                unset($user["likes"][$recipe_index]);
                $user["likes"] = array_values($user["likes"]);
                $all_users[$user_index] = $user;
                $this->likes = $user["likes"];
                $this->json_handler->writeData(self::DATA_FILE, $all_users);
                return $user;
            } else {
                error_log("Recipe not liked");
                return [];
            }
        } catch (Exception $e) {
            throw $e;
        }
    }
}
