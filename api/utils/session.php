<?php class Session
{



    public static function start()
    {
        if (session_status() == PHP_SESSION_NONE) {
            return;
        }
        session_start();
    }

    public static function destroy()
    {
        if (session_status() == PHP_SESSION_ACTIVE) {
            return;
        }
        session_unset();
        session_destroy();
    }
}
