<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Réinitialisation du mot de passe – StalHub</title>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&family=Open+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/stalhub/public/css/reset_password.css">

</head>
<body>

  <div class="reset-container">
    <h2>🔐 Nouveau mot de passe</h2>

    <?php if (!empty($error)): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="/stalhub/reset-password/post">
      <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
      <input type="password" name="password" placeholder="Nouveau mot de passe" required>
      <button type="submit">Réinitialiser le mot de passe</button>
    </form>
  </div>

</body>
</html>
