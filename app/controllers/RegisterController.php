<?php
require_once __DIR__ . '/../models/RegisterModel.php';

class RegisterController {
    private $model;

    public function __construct() {
        $this->model = new RegisterModel();
    }

    // GET /register
    public function register() {
        require __DIR__ . '/../views/register.php';
    }

    // POST /register
    public function submit() {
        $org_name  = trim($_POST['org_name'] ?? '');
        $org_alias = trim($_POST['org_alias'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = trim($_POST['password'] ?? '');

        if (empty($org_name) || empty($email) || empty($password)) {
            header("Location: /mes/register?error=Missing required fields");
            exit;
        }

        try {
            $orgId = $this->model->registerOrganization($org_name, $org_alias, $email, $password);
            header("Location: /mes/register?success=Organization registered! Org ID=$orgId");
        } catch (Exception $e) {
            header("Location: /mes/register?error=" . urlencode($e->getMessage()));
        }
    }
}
