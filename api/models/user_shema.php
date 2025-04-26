<?php

class UserSchema
{
    public $id;
    public $username;
    public $email;
    public $role = "Cuisiner";
    public $created_at;
    public array $recipes = [];
    public array $comments = [];
    public array $photos = [];
    public array $likes = [];

    private JSONHandler $json_handler;
    private const DATA_FILE = "users.json";

    public function __construct(JSONHandler $json_handler, string $user_id)
    {
        $this->id = $user_id;
        $this->json_handler = $json_handler;
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
    public function create(): array
    {
        return [];
    }
    /**
     * @return array
     */
    public function update(string $user_id): array
    {
        return [];
    }
    /**
     * @return array
     */
    public function updateRole(string $user_id): array
    {
        return [];
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
