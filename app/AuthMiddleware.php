<?php
// /app/middleware/AuthMiddleware.php

class AuthMiddleware
{
    public static function checkAuth(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['tenant_id'])) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/';
            header("Location: /mes/signin?error=" . urlencode("Please log in to continue"));
            exit;
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    public static function getTenantId(): int
    {
        return (int) ($_SESSION['tenant_id'] ?? 0);
    }

    public static function getTenantName(): string
    {
        return $_SESSION['tenant_name'] ?? 'Unknown';
    }

    public static function getCsrfToken(): string
    {
        return $_SESSION['csrf_token'] ?? '';
    }
}