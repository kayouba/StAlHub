<h2>Connexion</h2>

<?php if (!empty($error)): ?>
    <p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST" action="/stalhub/login/post">
    <label>Email</label><br>
    <input type="email" name="email" required><br><br>
    <label>Mot de passe</label><br>
    <input type="password" name="password" required><br><br>
    <button type="submit">Se connecter</button>
</form>
