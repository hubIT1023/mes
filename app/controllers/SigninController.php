<?php
// SigninController.php

require_once __DIR__ . '/../models/SigninModel.php';

class SigninController {
    private $model;

    public function __construct() {
        $this->model = new SigninModel();
    }

    // -------------------------------
    // GET /signin
    // -------------------------------
    public function signin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['tenant'])) {
            header("Location: /mes/hub_portal");
            exit;
        }

        // Check "Remember Me" cookie
        if (isset($_COOKIE['remember_token'])) {
            $tenant = $this->model->verifyRememberToken($_COOKIE['remember_token']);
            if ($tenant) {
                $_SESSION['tenant'] = [
                    'org_id'   => $tenant['org_id'],
                    'org_name' => $tenant['org_name'],
                    'email'    => $tenant['email']
                ];
                $_SESSION['tenant_id'] = $tenant['org_id'];
                $_SESSION['tenant_name'] = $tenant['org_name'];
                header("Location: /mes/hub_portal");
                exit;
            }
        }

        require __DIR__ . '/../views/signin.php';
    }

    // -------------------------------
    // POST /signin
    // -------------------------------
    public function authenticate() {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $remember = isset($_POST['remember']);

        if (empty($email) || empty($password)) {
            header("Location: /mes/signin?error=Missing email or password");
            exit;
        }

        try {
            $tenant = $this->model->verifyCredentials($email, $password);

            if ($tenant) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }

                // 🔒 Regenerate session ID on login (prevents fixation)
                session_regenerate_id(true); // ✅ SAFE HERE — session is active

                $_SESSION['tenant'] = [
                    'org_id'   => $tenant['org_id'],
                    'org_name' => $tenant['org_name'],
                    'email'    => $tenant['email']
                ];
                $_SESSION['tenant_id'] = $tenant['org_id'];
                $_SESSION['tenant_name'] = $tenant['org_name'];

                // "Remember Me" feature
                if ($remember) {
                    $token = bin2hex(random_bytes(32)); // 64-char secure token
                    $this->model->storeRememberToken($tenant['org_id'], $token);

                    setcookie(
                        'remember_token',
                        $token,
                        [
                            'expires'  => time() + (30 * 24 * 60 * 60), // 30 days
                            'path'     => '/',
                            'secure'   => false, // Set to true in production with HTTPS
                            'httponly' => true,
                            'samesite' => 'Lax'
                        ]
                    );
                }

                header("Location: /mes/hub_portal");
                exit;
            } else {
                header("Location: /mes/signin?error=Invalid email or password");
                exit;
            }
        } catch (Exception $e) {
            header("Location: /mes/signin?error=" . urlencode($e->getMessage()));
            exit;
        }
    }

    // -------------------------------
    // GET /hub_portal
    // -------------------------------
    public function hubPortal() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['tenant'])) {
            header("Location: /mes/signin?error=Please log in first");
            exit;
        }

        require_once __DIR__ . '/../middleware/AuthMiddleware.php';
        AuthMiddleware::checkAuth();

        require __DIR__ . '/../views/hub_portal.php';
    }

    // -------------------------------
    // GET /signout → FIXED LOGOUT
    // -------------------------------
    public function signout() {
        // Only start session if needed
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 1. Clear "Remember Me" token from DB (if user was logged in)
        if (isset($_SESSION['tenant']['org_id'])) {
            $this->model->clearRememberToken($_SESSION['tenant']['org_id']);
        }

        // 2. Clear all session data
        $_SESSION = [];

        // 3. Delete "remember_token" cookie
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);

        // 4. Delete PHP session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // 5. Destroy session on server
        session_destroy();

        // ❌ REMOVED: session_regenerate_id(true) — NOT NEEDED ON LOGOUT

        // 6. Redirect
        header("Location: /mes/signin?success=Signed out successfully");
        exit;
    }
}