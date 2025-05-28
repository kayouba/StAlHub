<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Signature de la Convention</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/stalhub/public/css/sign.css"> 
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

       <form method="POST" action="/stalhub/signature/convention/valider" onsubmit="return prepareSignature()">
            <input type="hidden" name="token" value="<?= htmlspecialchars($document['company_signature_token']) ?>">
            
            <label for="nom_signataire">Nom du signataire :</label>
            <input type="text" name="nom_signataire" id="nom_signataire" required>

            <!-- Zone de signature manuscrite -->
            <section>
                <label>Signature manuscrite :</label>
                <canvas id="signature-pad" width="100%" height="150" style="border:1px solid #ccc; max-width: 400px;"></canvas>

                <br>
                <button type="button" id="clear-signature">🧽 Effacer</button>
            </section>

            <input type="hidden" name="signature_image" id="signature_image">

            <br>
            <button type="submit">✅ Signer la convention</button>
        </form>

    </main>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.6/dist/signature_pad.umd.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const canvas = document.getElementById("signature-pad");
    const signaturePad = new SignaturePad(canvas);

    const clearBtn = document.getElementById("clear-signature");
    clearBtn.addEventListener("click", () => signaturePad.clear());

    window.prepareSignature = () => {
        if (signaturePad.isEmpty()) {
            return confirm("⚠️ Aucune signature manuscrite détectée. Voulez-vous continuer ?");
        }
        document.getElementById("signature_image").value = signaturePad.toDataURL("image/png");
        return true;
    };
});
</script>


</body>
</html>
