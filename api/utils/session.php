<?php class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set("session.cookie_httponly", 1);
            ini_set("session.use_only_cookies", 1);
            session_start();
        }
        if (
            !isset($_SESSION["last_regeneration"]) ||
            time() - Session::get("last_regeneration") > 300
        ) {
            session_regenerate_id(true);
            Session::set("last_regeneration", time());
        }
    }

    public static function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
    }

    public static function get(string $key): mixed
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return null;
        }
        return $_SESSION[$key] ?? null;
    }

    public static function set(string $key, mixed $value): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }
        if (!is_array($value)) {
            error_log("define " . $key . " as " . $value);
        } else {
            error_log("define " . $key . " as " . json_encode($value));
        }
        $_SESSION[$key] = $value;
    }

    public static function remove(string $key): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }
        unset($_SESSION[$key]);
    }

    public static function clear(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }
        session_unset();
    }

    public static function regenerateId(bool $deleteOldSession = true): bool
    {
        return session_regenerate_id($deleteOldSession);
    }

    public static function isLoggedIn(): bool
    {
        $status = session_status();
        error_log(
            "SESSION_DEBUG: Checking isLoggedIn. Session status: " . $status
        );

        if ($status !== PHP_SESSION_ACTIVE) {
            error_log(
                "SESSION_DEBUG: isLoggedIn check failed - session status is not PHP_SESSION_ACTIVE."
            );
            return false;
        }

        $isSet = isset($_SESSION["user_id"]);
        $userIdValue = $_SESSION["user_id"] ?? "NOT SET";
        error_log(
            "SESSION_DEBUG: isLoggedIn checking \$_SESSION['user_id']. Is set: " .
                ($isSet ? "true" : "false") .
                ". Value: " .
                $userIdValue
        );

        return $isSet;
    }

    public static function getCurrentUser(): UserSchema
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new Exception("Session is not active");
        }
        $json_handler = new JSONHandler(API_BASE_PATH . "/data");
        $user_schema = new UserSchema(
            $json_handler,
            $_SESSION["username"],
            $_SESSION["email"]
        );
        try {
            $userData = $user_schema->getById($_SESSION["user_id"]);
            if (empty($userData)) {
                throw new Exception("User not found in session");
            }
            $user_schema->fromArray($userData);
            return $user_schema;
        } catch (Exception $e) {
            error_log(
                "User retrieval error during session initialization: " .
                    $e->getMessage()
            );
            throw $e;
        }
    }

    public static function getUserRoles(): array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new Exception("Session is not active");
        }
        $roles = self::get("roles");
        return $roles ?? ["Cuisinier"];
    }

    public static function hasRole(string $role): bool
    {
        $roles = self::getUserRoles();
        return in_array($role, $roles);
    }

    public static function getUserRole(): string
    {
        $roles = self::getUserRoles();
        return $roles[0] ?? "Cuisinier";
    }
}
