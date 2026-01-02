<?php
//SigninModel.php

require_once __DIR__ . '/../config/Database.php';

class SigninModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function verifyCredentials($email, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM organizations WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        return false;
    }

    public function storeRememberToken($org_id, $token) {
        $hashedToken = hash('sha256', $token);
        $stmt = $this->conn->prepare("
            UPDATE organizations 
            SET remember_token = :token 
            WHERE org_id = :org_id
        ");
        $stmt->execute(['token' => $hashedToken, 'org_id' => $org_id]);
    }

    public function verifyRememberToken($token) {
        $hashedToken = hash('sha256', $token);
        $stmt = $this->conn->prepare("
            SELECT * FROM organizations 
            WHERE remember_token = :token
        ");
        $stmt->execute(['token' => $hashedToken]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function clearRememberToken($org_id) {
        $stmt = $this->conn->prepare("
            UPDATE organizations 
            SET remember_token = NULL 
            WHERE org_id = :org_id
        ");
        $stmt->execute(['org_id' => $org_id]);
    }
}