<?php
// app/middleware/AuthMiddleware.php

class AuthMiddleware {
    public static function checkAuth() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if tenant session exists
        if (!isset($_SESSION['tenant'])) {
            // Optional: store last requested URL for redirect after login
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header("Location: /mes/signin?error=Please log in to continue");
            exit;
        }
    }
	
		if (empty($_SESSION['csrf_token'])) {
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}
}