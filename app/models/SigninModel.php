<?php
require_once __DIR__ . '/../config/Database.php';

class SigninModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function verifyCredentials($email, $password) {
        // Normalize email (optional but recommended)
        $email = strtolower(trim($email));

        $stmt = $this->db->prepare("SELECT org_id, org_name, email, password_hash FROM organizations WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        return false;
    }

    public function storeRememberToken($org_id, $token) {
        // Use a cryptographically stronger hash? SHA-256 is fine, but consider:
        // - Adding salt (not needed here since token is already random)
        // - Or just store as-is? But hashing is safer if DB leaks.
        $hashedToken = hash('sha256', $token);

        $stmt = $this->db->prepare("
            UPDATE organizations 
            SET remember_token = :token,
                updated_at = NOW()  -- Optional: track last update
            WHERE org_id = :org_id
        ");
        return $stmt->execute(['token' => $hashedToken, 'org_id' => $org_id]);
    }

    public function verifyRememberToken($token) {
        if (empty($token) || !is_string($token)) {
            return false;
        }

        $hashedToken = hash('sha256', $token);
        $stmt = $this->db->prepare("
            SELECT org_id, org_name, email 
            FROM organizations 
            WHERE remember_token = :token
              AND remember_token IS NOT NULL
        ");
        $stmt->execute(['token' => $hashedToken]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function clearRememberToken($org_id) {
        if (empty($org_id)) {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE organizations 
            SET remember_token = NULL,
                updated_at = NOW()
            WHERE org_id = :org_id
        ");
        return $stmt->execute(['org_id' => $org_id]);
    }
}