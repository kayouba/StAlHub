<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Connexion – StalHub</title>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&family=Open+Sans&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/stalhub/public/css/login.css">
</head>
<body>

  <div class="login-container">
    <img src="/stalhub/assets/img/stalhub-logo.png" alt="Logo StalHub">
    <h2>Connexion à StalHub</h2>

        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($_GET['timeout'])): ?>
            <div class="error" style="color: #ffd966;">
                Vous avez été déconnecté après une période d'inactivité.
            </div>
        <?php endif; ?>


    <form method="POST" action="/stalhub/login/post">
      <input type="email" name="email" placeholder="Adresse e-mail" required>
      <input type="password" name="password" placeholder="Mot de passe" required>
      <button type="submit">Se connecter</button>
    </form>

    <a class="forgot-link" href="/stalhub/forgot-password">Mot de passe oublié ?</a>
    <a class="register-link" href="/stalhub/register">Pas encore de compte ? S'inscrire</a>
  </div>

</body>
</html>
