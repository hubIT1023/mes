<?php include __DIR__ . '/layouts/header.php'; ?>

<h1>Contact Us</h1>

<?php if (!empty($error)): ?>
  <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if (!empty($success)): ?>
  <p style="color:green;"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<form method="POST" action="<?= $baseUrl ?>/contact">
  <label>Name:</label><br>
  <input type="text" name="name" required><br><br>

  <label>Email:</label><br>
  <input type="email" name="email" required><br><br>

  <label>Message:</label><br>
  <textarea name="message" required></textarea><br><br>

  <button type="submit">Send</button>
</form>

<?php include __DIR__ . '/layouts/footer.php'; ?>
