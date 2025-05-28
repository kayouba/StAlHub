<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Signature de la Convention</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }

        /* Mini header style */
        header {
            background: linear-gradient(90deg, #001F3F, #003a70);
            color: white;
            padding: 1rem 2rem;
            font-family: 'Orbitron', sans-serif;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        header h1 {
            margin: 0;
            font-size: 20px;
        }

        main {
            padding: 2rem;
            max-width: 900px;
            margin: auto;
        }

        h2 {
            color: #003a70;
            margin-bottom: 1rem;
        }

        iframe {
            border: 1px solid #ccc;
            border-radius: 6px;
            width: 100%;
            height: 600px;
            margin-bottom: 2rem;
        }

        form {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 0.5rem;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            margin-bottom: 1.5rem;
        }

        button {
            background-color: #003a70;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #005fa3;
        }

        @media (max-width: 768px) {
            main {
                padding: 1rem;
            }

            iframe {
                height: 400px;
            }
        }
    </style>
</head>
<body>

    <!-- Mini Header -->
    <header>
        <h1>StAlHub – Signature de Convention</h1>
    </header>

    <main>
        <h2>Convention de stage</h2>
        <p>Merci de lire la convention ci-dessous, puis entrez votre nom pour la signer.</p>

        <iframe src="<?= htmlspecialchars($document['file_path']) ?>"></iframe>

        <form method="POST" action="/stalhub/signature/convention/valider">
            <input type="hidden" name="token" value="<?= htmlspecialchars($document['company_signature_token']) ?>">
            <label for="nom_signataire">Nom du signataire :</label>
            <input type="text" name="nom_signataire" id="nom_signataire" required>

            <button type="submit">✅ Signer la convention</button>
        </form>
    </main>

</body>
</html>
