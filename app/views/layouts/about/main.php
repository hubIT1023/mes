<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($title ?? 'hubIT.online') ?></title>
  <link rel="stylesheet" href="<?= $baseUrl ?>/app/Assets/css/style.css">
</head>
<body>
  <header>
    <div class="logo">
      <img src="<?= $baseUrl ?>/app/Assets/img/hubIT_logo-v5.png" alt="hubIT Logo" width="160">
    </div>
    <?php include __DIR__ . '/nav.php'; ?>
  </header>

  <main class="content">
    <?= $content ?>
  </main>

  <footer>
    <p>&copy; <?= date('Y') ?> hubIT.online — All rights reserved.</p>
  </footer>
</body>
</html>