<?php

class UserSchema implements JsonSerializable
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

    public function jsonSerialize(): mixed
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
    private function fromArray(array $data): void
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
            error_log("User not found" . $e->getMessage());
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
                array_column($all_users, "id")
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
            $usr_array = $user->jsonSerialize();
            if (Validator::validateUser($usr_array)) {
                $this = $user;
                $user_array = $this->jsonSerialize();
                $all_users = $this->getAll();
                array_push($all_users, $user_array);
                $this->json_handler->writeData(
                    UserSchema::DATA_FILE,
                    $all_users
                );
                return $user_array;
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
    public function update(array $updates): array
    {
        try {
            if (!Validator::validateUser($updates)) {
                error_log("Invalid user data");
                throw new Exception("Invalid user data");
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
            $all_users[$usr_index] = $updates;
            $this->json_handler->writeData(UserSchema::DATA_FILE, $all_users);
            $this->fromArray($updates);
            return $updates;
        } catch (Exception $e) {
            error_log("User update failed");
            throw $e;
        }
    }
    /**
     * @return array
     */
    public function updateRole(string $user_id, string $role): array
    {
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
            $current_usr_array = $this->jsonSerialize();
            $current_usr_array["role"] = $role;
            if (!Validator::validateUser($current_usr_array)) {
                error_log("Invalid role");
                throw new Exception("Invalid role");
            }
            $all_users[$usr_index]["role"] = $role;
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
