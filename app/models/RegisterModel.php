<?php
//RegisterModel
require_once __DIR__ . '/../config/Database.php';

class RegisterModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function registerOrganization($org_name, $org_alias, $email, $password) {
        // Check for duplicate email
        $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM organizations WHERE email = ?");
        $checkStmt->execute([$email]);
        if ($checkStmt->fetchColumn() > 0) {
            throw new Exception("Email already registered.");
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Insert organization and return UUID
        $sql = "INSERT INTO organizations (org_name, org_alias, email, password_hash)
                OUTPUT inserted.org_id
                VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$org_name, $org_alias, $email, $passwordHash]);

        return $stmt->fetchColumn();
    }
}
