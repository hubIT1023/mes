<?php include __DIR__ . '/layouts/header1.php'; ?>

<h1><?= htmlspecialchars($title ?? 'About') ?></h1>
<p><?= htmlspecialchars($content ?? '') ?></p>

<?php include __DIR__ . '/layouts/footer.php'; ?>