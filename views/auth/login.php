<h2>Connexion</h2>
<?php if (!empty($error)): ?>
  <p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
<form method="post" action="/login/post">
  <label>Email :
    <input type="email" name="email" required autofocus>
  </label><br>
  <label>Mot de passe :
    <input type="password" name="password" required>
  </label><br>
  <button type="submit">Se connecter</button>
</form>
