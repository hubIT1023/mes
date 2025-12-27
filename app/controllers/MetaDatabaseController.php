<?php
require_once __DIR__ . '/../models/MetaDatabaseModel.php';

class MetaDatabaseController {
    public function showForm() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['tenant_id'])) {
            header("Location: /mes/signin");
            exit;
        }

        $model = new MetaDatabaseModel();
        $tenant_id = $_SESSION['tenant_id'];
        $metadata = $model->getMetadata($tenant_id);

        // Build full array for col_1 to col_16
        $labels = [];
        for ($i = 1; $i <= 16; $i++) {
            $col = "col_$i";
            $labels[$col] = [
                'label' => $metadata[$col]['label'] ?? $col,
                'description' => $metadata[$col]['description'] ?? ''
            ];
        }

        require_once __DIR__ . '/../views/forms_bid/meta_database.php';
    }

    public function saveMetadata() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['tenant_id'])) {
            header("Location: /mes/signin");
            exit;
        }

        $tenant_id = $_SESSION['tenant_id'];
        $model = new MetaDatabaseModel();

        // Prepare data
        $updates = [];
        for ($i = 1; $i <= 16; $i++) {
            $col = "col_$i";
            $label = trim($_POST["label_$i"] ?? $col);
            $desc = trim($_POST["desc_$i"] ?? '');

            // Skip if identical to defaults
            if ($label === $col && $desc === '') continue;

            $updates[] = [
                'org_id' => $tenant_id,
                'col_number' => $col,
                'label' => $label,
                'description' => $desc
            ];
        }

        $result = $model->saveMetadata($tenant_id, $updates);

        if ($result) {
            $_SESSION['success'] = "Metadata saved successfully!";
        } else {
            $_SESSION['error'] = "Failed to save metadata.";
        }

        header("Location: /mes/meta-database");
        exit;
    }
}