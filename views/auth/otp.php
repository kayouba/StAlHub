<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Vérification du code OTP – StalHub</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", sans-serif;
            background-color: #004A7C;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            background-color: #fff;
            padding: 40px;
            border-radius: 10px;
            width: 380px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        .login-container img {
            width: 180px;
            margin-bottom: 20px;
        }

        .login-container h2 {
            color: #004A7C;
            margin-bottom: 25px;
        }

        .login-container input {
            width: 100%;
            padding: 12px;
            margin-bottom: 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        .login-container button {
            width: 100%;
            padding: 12px;
            background-color: #004A7C;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .login-container button:hover {
            background-color: #00345a;
        }

        .register-link {
            display: block;
            margin-top: 15px;
            font-size: 14px;
            color: #004A7C;
            text-decoration: none;
        }

        .register-link:hover {
            text-decoration: underline;
        }

        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 16px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: #004A7C;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <img src="/stalhub/assets/img/universite-bordeaux.png" alt="Université de Bordeaux">
        <h2>Vérification du code OTP</h2>

        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/stalhub/otp/verify">
            <label for="code">Code OTP</label>
            <input type="text" id="code" name="code" required>
            <button type="submit">Valider</button>
        </form>

    </div>

</body>
</html>
