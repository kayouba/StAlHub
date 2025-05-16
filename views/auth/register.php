<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un compte – StalHub</title>
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

        .register-container {
            background-color: #fff;
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
            font-size: 14px;
        }

        .register-container button {
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

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="register-container">
        <h2>Créer un compte</h2>
        <form method="POST" action="/stalhub/register/post">
            <input type="text" name="first_name" placeholder="Prénom" required>
            <input type="text" name="last_name" placeholder="Nom" required>
            <input type="email" name="email" placeholder="Adresse e-mail" required>
            <input type="text" name="phone_number" placeholder="Téléphone" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit">Créer le compte</button>
        </form>
        <a class="back-link" href="/stalhub/login">← Déjà inscrit ? Se connecter</a>
    </div>

</body>
</html>
