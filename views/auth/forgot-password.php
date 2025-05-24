<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mot de passe oublié</title>
</head>
<body>
    <h2>🔑 Mot de passe oublié</h2>

    <?php if (!empty($error)): ?>
        <p style="color:red"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <p style="color:green"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="POST" action="/stalhub/forgot-password/post">
        <label for="email">Email :</label>
        <input type="email" name="email" required>
        <br><br>
        <button type="submit">📩 Envoyer le lien de réinitialisation</button>
    </form>

    <p><a href="/stalhub/login">Retour à la connexion</a></p>
</body>
</html>