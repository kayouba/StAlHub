<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion – StalHub</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", sans-serif;
            background: linear-gradient(135deg, #004A7C, #0e2b47);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            padding: 40px;
            border-radius: 15px;
            width: 380px;
            box-shadow: 0 0 30px rgba(0, 74, 124, 0.5);
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        .login-container img {
            width: 240px;
            margin-bottom: 20px;
            filter: drop-shadow(0 0 40px white);
        }

        .login-container h2 {
            font-size: 26px;
            margin-bottom: 25px;
            color: #ffffff;
            text-shadow: 0 0 5px rgba(255, 255, 255, 0.2);
        }

        .login-container input {
            width: 100%;
            padding: 14px;
            margin-bottom: 16px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            background: rgba(255, 255, 255, 0.15);
            color: white;
            outline: none;
            transition: all 0.3s ease;
        }

        .login-container input::placeholder {
            color: #ddd;
        }

        .login-container input:focus {
            background: rgba(255, 255, 255, 0.25);
            box-shadow: 0 0 8px rgba(255, 255, 255, 0.3);
        }

        .login-container button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(145deg, #00b7ff, #004a7c);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 0 15px rgba(0, 183, 255, 0.4);
        }

        .login-container button:hover {
            background: linear-gradient(145deg, #0083c7, #00385d);
            box-shadow: 0 0 20px rgba(0, 183, 255, 0.6);
        }

        .register-link {
            display: block;
            margin-top: 12px;
            font-size: 14px;
            color: #ffffff;
            text-decoration: none;
            transition: color 0.3s;
        }

        .register-link:hover {
            color: #00bfff;
            text-decoration: underline;
        }

        .error {
            color: #ff4b4b;
            font-size: 14px;
            margin-bottom: 16px;
        }

        .forgot-link {
            display: block;
            margin-top: 8px;
            font-size: 13px;
            color: #add8ff;
            text-decoration: none;
        }

        .forgot-link:hover {
            color: #ffffff;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <img src="/stalhub/assets/img/stalhub-logo.png" alt="Logo StalHub">
        <h2>Connexion à StalHub</h2>

        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
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