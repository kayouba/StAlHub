<h2>Vérification du code OTP</h2>

<?php if (!empty($error)): ?>
    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST" action="/stalhub/otp/verify">
    <label for="code">Code OTP</label><br>
    <input type="text" id="code" name="code" required><br><br>
    <button type="submit">Valider</button>
</form>
