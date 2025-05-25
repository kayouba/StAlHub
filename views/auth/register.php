<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Créer un compte – StalHub</title>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&family=Open+Sans&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/stalhub/public/css/register.css">
</head>

<body>
  <div class="register-container">
    <h2>Créer un compte</h2>

    <?php if (!empty($error)): ?>
      <div class="error">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="/stalhub/register/post">
      <input type="text" name="first_name" placeholder="Prénom" required value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
      <input type="text" name="last_name" placeholder="Nom" required value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
      <input type="email" name="email" placeholder="Adresse e-mail" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      <input type="text" name="phone_number" placeholder="Téléphone" required value="<?= htmlspecialchars($_POST['phone_number'] ?? '') ?>">
      <input type="password" name="password" placeholder="Mot de passe" required>

      <div class="password-hint">
        Le mot de passe doit contenir au moins 8 caractères, une majuscule et un caractère spécial.
      </div>

      <label>
        <input type="checkbox" name="rgpd_consent" required>
        J’ai lu et j’accepte les <a href="/stalhub/mentions-legales" target="_blank">mentions légales</a>
      </label>

      <button type="submit">Créer le compte</button>
    </form>

    <a class="forgot-link" href="/stalhub/forgot-password">Mot de passe oublié ?</a>
    <a class="back-link" href="/stalhub/login">Déjà inscrit ? Se connecter</a>
  </div>
</body>
</html>
