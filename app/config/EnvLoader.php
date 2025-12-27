<?php
// config/EnvLoader.php
/**
 * .env loader
 */
class EnvLoader {
    public static function load($path) {
        if (!file_exists($path)) {
            throw new Exception(".env file not found at: $path");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) continue; // skip comments

            [$name, $value] = array_map('trim', explode('=', $line, 2));

            // remove quotes if present
            $value = trim($value, "'\"");

            putenv("$name=$value");
            $_ENV[$name] = $value;
        }
    }
}