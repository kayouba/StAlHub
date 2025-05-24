<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réinitialisation du mot de passe – StalHub</title>
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
        .reset-container {
            background-color: #fff;
            color: #333;
            padding: 40px;
            border-radius: 10px;
            width: 380px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .reset-container h2 {
            color: #004A7C;
            text-align: center;
            margin-bottom: 25px;
        }
        .reset-container input {
            width: 100%;
            padding: 12px;
            margin-bottom: 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        .reset-container button {
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
        .reset-container button:hover {
            background-color: #00345a;
        }
        .error {
            color: #e53935;
            font-size: 14px;
            text-align: center;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <h2>Nouveau mot de passe</h2>

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