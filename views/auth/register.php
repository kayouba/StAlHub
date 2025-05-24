<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un compte – StalHub</title>
    <style>
        body {
            background-color: #004A7C;
            color: white;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .register-container {
            background-color: #fff;
            color: #333;
            padding: 40px;
            border-radius: 10px;
            width: 380px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .register-container h2 {
            color: #004A7C;
            text-align: center;
            margin-bottom: 25px;
        }
        .register-container input {
            width: 100%;
            padding: 12px;
            margin-bottom: 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        .register-container button {
            width: 100%;
            padding: 12px;
            background-color: #004A7C;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }
        .register-container label {
            font-size: 14px;
            margin-bottom: 16px;
            display: block;
        }
        .register-container button:hover {
            background-color: #00345a;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
            color: #004A7C;
            text-decoration: none;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            font-size: 14px;
        }
        .password-hint {
            font-size: 12px;
            color: #666;
            margin-top: -12px;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Créer un compte</h2>

        <?php if (!empty($error)): ?>
            <div class="error">
                ⚠️ <?= htmlspecialchars($error) ?>
            </div>
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

        <p style="text-align: center; margin-top: 10px;">
            <a href="/stalhub/forgot-password" style="color: #004A7C; font-size: 14px;">Mot de passe oublié ?</a>
        </p>

        <a class="back-link" href="/stalhub/login">Déjà inscrit ? Se connecter</a>
    </div>
</body>
</html>
