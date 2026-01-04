<?php
// /app/middleware/url.php

function is_active(string $path): string {
    $current = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    return $path === $current ? 'active' : '';
}

function determineSelectedPage(array $pages): ?int {
    // If no pages exist, nothing to select
    if (empty($pages)) {
        return null;
    }

    // 1. Check URL ?page_id= (only if valid)
    if (!empty($_GET['page_id'])) {
        $id = (int) $_GET['page_id'];
        if ($id > 0 && isset($pages[$id])) {
            return $id;
        }
    }

    // 2. Check session last_page_id (only if valid)
    if (isset($_SESSION['last_page_id'])) {
        $lastId = (int) $_SESSION['last_page_id'];
        if ($lastId > 0 && isset($pages[$lastId])) {
            return $lastId;
        }
    }

    // 3. Fallback to first page
    return (int) array_key_first($pages);
}