<?php

class Utils
{
    public static function uuid4(): string
    {
        //uuid expression ai-generated (had no idea how to do it in php)
        return sprintf(
            "%s-%s-%s-%s-%s",
            bin2hex(random_bytes(4)),
            bin2hex(random_bytes(2)),
            bin2hex(chr((ord(random_bytes(1)) & 0x0f) | 0x40)), // v4
            bin2hex(chr((ord(random_bytes(1)) & 0x3f) | 0x80)), // DCE variant
            bin2hex(random_bytes(6))
        );
    }

    public static function getJSONBody(): ?array
    {
        $requestBody = file_get_contents("php://input");

        if ($requestBody === false || $requestBody === "") {
            throw new Exception("Invalid request body");
        }

        $data = json_decode($requestBody, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON Decode Error: " . json_last_error_msg());
            throw new Exception("Invalid JSON format");
        }

        if (!is_array($data)) {
            throw new Exception("Invalid JSON format");
        }

        return $data;
    }
}
