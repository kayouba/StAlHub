<h2>Entrez le code OTP reçu par SMS</h2>
<?php if (!empty($error)): ?>
  <p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
<form method="post" action="/otp/verify">
  <input type="text" name="otp" placeholder="123456" required autofocus>
  <button type="submit">Valider</button>
</form>
