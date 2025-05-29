<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Signature confirmée</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background: white;
            padding: 2rem 3rem;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
        }

        h2 {
            color: #003a70;
            font-family: 'Orbitron', sans-serif;
            margin-bottom: 1rem;
        }

        p {
            font-size: 16px;
            color: #333;
            margin-bottom: 2rem;
        }

        .checkmark {
            font-size: 3rem;
            color: green;
            margin-bottom: 1rem;
        }

        .link-button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #003a70;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background-color 0.3s ease;
        }

        .link-button:hover {
            background-color: #005fa3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="checkmark">✅</div>
        <h2>Merci !</h2>
        <p>La convention a été signée avec succès par l’entreprise.</p>

        <!-- Vérification de l'existence du fichier et affichage du lien -->
        <?php if (!empty($document['file_path'])): ?>
            <?php 
            // Récupérer le chemin sans préfixe supplémentaire
            $filePath = $document['file_path'];
            ?>
            <!-- Affichage du lien pour voir la convention signée -->
            <a class="link-button" href="/stalhub/document/view?file=<?= urlencode($filePath) ?>" target="_blank">
                📄 Voir la convention signée
            </a>
        <?php else: ?>
            <p>Le fichier signé est introuvable. Veuillez réessayer plus tard.</p>
        <?php endif; ?>
    </div>
</body>
</html>
