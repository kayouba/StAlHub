<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mot de passe oublié – StalHub</title>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&family=Open+Sans&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Open Sans', sans-serif;
      background: radial-gradient(circle at top left, #001F3F, #000d1a 80%);
      color: white;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      overflow: hidden;
    }

    .reset-container {
      background: rgba(0, 31, 63, 0.75);
      backdrop-filter: blur(14px);
      border: 1px solid rgba(255, 255, 255, 0.08);
      box-shadow: 0 0 20px rgba(0, 204, 255, 0.3);
      border-radius: 20px;
      padding: 50px 40px;
      width: 100%;
      max-width: 440px;
      box-sizing: border-box;
      animation: fadeIn 1.2s ease-out;
    }

    .reset-container h2 {
      font-family: 'Orbitron', sans-serif;
      font-size: 22px;
      color: #00cfff;
      text-align: center;
      margin-bottom: 25px;
    }

    .reset-container input[type="email"] {
      width: 100%;
      padding: 14px;
      margin-bottom: 20px;
      border: none;
      border-radius: 10px;
      background: rgba(255, 255, 255, 0.08);
      color: white;
      font-size: 15px;
      outline: none;
      transition: 0.3s ease;
    }

    .reset-container input::placeholder {
      color: #ccc;
    }

    .reset-container input:focus {
      background: rgba(255, 255, 255, 0.15);
      box-shadow: 0 0 8px rgba(0, 204, 255, 0.4);
    }

    .reset-container button {
      width: 100%;
      padding: 14px;
      border: none;
      border-radius: 50px;
      font-weight: bold;
      font-size: 16px;
      background: linear-gradient(135deg, #00cfff, #005dab);
      color: white;
      cursor: pointer;
      transition: 0.3s ease;
      box-shadow: 0 0 15px rgba(0, 204, 255, 0.4);
    }

    .reset-container button:hover {
      background: linear-gradient(135deg, #00e6ff, #0074d9);
      box-shadow: 0 0 25px rgba(0, 204, 255, 0.8);
    }

    .message {
      font-size: 14px;
      padding: 10px;
      border-radius: 8px;
      margin-bottom: 20px;
      text-align: center;
    }

    .message.error {
      background-color: #ff4b4b;
      color: white;
    }

    .message.success {
      background-color: #28a745;
      color: white;
    }

    .back-link {
      display: block;
      text-align: center;
      margin-top: 20px;
      font-size: 14px;
      color: #add8ff;
      text-decoration: none;
    }

    .back-link:hover {
      color: #ffffff;
      text-decoration: underline;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @media (max-width: 480px) {
      .reset-container {
        padding: 40px 20px;
        width: 90%;
      }

      .reset-container h2 {
        font-size: 20px;
      }
    }
  </style>
</head>
<body>

  <div class="reset-container">
    <h2>🔑 Mot de passe oublié</h2>

    <?php if (!empty($error)): ?>
      <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
      <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" action="/stalhub/forgot-password/post">
      <input type="email" name="email" placeholder="Adresse e-mail" required>
      <button type="submit">📩 Envoyer le lien de réinitialisation</button>
    </form>

    <a class="back-link" href="/stalhub/login">← Retour à la connexion</a>
  </div>

</body>
</html>
