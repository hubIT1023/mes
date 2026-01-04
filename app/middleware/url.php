<?php
// /app/middleware/url.php

function is_active(string $path): string {
    $current = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    return $path === $current ? 'active' : '';
}

function determineSelectedPage(array $pages): ?int {
    if (!empty($_GET['page_id'])) {
        $id = (int) $_GET['page_id'];
        return $id > 0 ? $id : null;
    }
    if (isset($_SESSION['last_page_id'])) {
        return (int) $_SESSION['last_page_id'];
    }
    return !empty($pages) ? (int) array_key_first($pages) : null;
}