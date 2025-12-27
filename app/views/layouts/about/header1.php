<?php
// app/views/layouts/header.php
$baseUrl = '/mes'; // change if your folder name is different
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>hubIT.online</title>
  <link rel="stylesheet" href="<?= $baseUrl ?>/app/Assets/css/style.css">
</head>
<body>

<div class="logo">
  <img src="<?= $baseUrl ?>/app/Assets/img/hubIT_logo-v5.png" alt="hubIT.online Logo" height="70">
</div>

<?php include __DIR__ . '/nav.php'; ?>
<hr>