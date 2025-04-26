<?php class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set("session.cookie_httponly", 1);
            ini_set("session.use_only_cookies", 1);
            session_start();
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
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }
        return isset($_SESSION["user_id"]);
    }

    public static function getCurrentUser(): ?UserSchema
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return null;
        }
        $json_handler = new JSONHandler();
        $user_schema = new UserSchema($json_handler);

        return $user_schema->getById($_SESSION["user_id"] ?? null);
    }

    public static function getUserRole(): ?string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return null;
        }
        $user = self::getCurrentUser();
        return $user ? $user->role : null;
    }
}
