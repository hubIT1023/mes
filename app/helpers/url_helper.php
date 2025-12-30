<?php

//url_helper.php

function base_url(string $path = ''): string
{
    $baseUrl = $_SERVER['BASE_URL'] ?? '';
    return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
}
