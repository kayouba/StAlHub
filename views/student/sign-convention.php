<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Signer la convention</title>
    <link rel="stylesheet" href="/stalhub/public/css/request-view.css">
    <style>
        .signature-container {
            max-width: 900px;
            margin: 30px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
        }

        h1,
        h2 {
            text-align: center;
        }

        iframe {
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        #signature-area {
            text-align: center;
        }

        #signature-pad {
            margin-top: 10px;
            border-radius: 6px;
        }

        #clear-signature,
        #save-signature {
            margin: 10px 5px;
            padding: 10px 20px;
            font-size: 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        #clear-signature {
            background-color: #dc3545;
            color: white;
        }

        #save-signature {
            background-color: #28a745;
            color: white;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border-radius: 6px;
            text-decoration: none;
            text-align: center;
            margin-top: 30px;
        }

        #signature-message {
            margin-top: 10px;
            font-weight: bold;
        }

        .error {
            color: red;
            text-align: center;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.6/dist/signature_pad.umd.min.js"></script>
</head>

<body>
    <?php include __DIR__ . '/../components/sidebar.php'; ?>

    <main class="signature-container">
        <h1>Signature de la convention</h1>

        <?php if (isset($convention)): ?>
            <p style="text-align: center;">Veuillez lire attentivement la convention ci-dessous avant de signer :</p>
            <iframe src="/stalhub/document/view?file=<?= urlencode($convention['file_path']) ?>" width="100%" height="600px"></iframe>
            <div id="signature-area">
                <h2>Votre signature</h2>
                <div style="text-align: center; margin-bottom: 20px;">
                    <label for="signatory_name"><strong>Nom complet :</strong></label><br>
                    <input type="text" id="signatory_name" style="padding: 8px; width: 60%;" placeholder="Nom et prénom">
                </div>
                <canvas id="signature-pad" width="400" height="150" style="border:1px solid #ccc;"></canvas><br>
                <button id="clear-signature">🧽 Effacer</button>
                <button id="save-signature">✅ Enregistrer</button>
                <p id="signature-message"></p>
            </div>
        <?php endif; ?>
        <div style="text-align: center;">
            <a href="/stalhub/dashboard" class="button">← Retour au tableau de bord</a>
        </div>
    </main>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const canvas = document.getElementById("signature-pad");
            const signaturePad = new SignaturePad(canvas);
            const ratio = window.devicePixelRatio || 1;
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
            signaturePad.clear();

            document.getElementById("clear-signature").addEventListener("click", () => signaturePad.clear());

            document.getElementById("save-signature").addEventListener("click", () => {
                const name = document.getElementById("signatory_name").value.trim();

                if (!name) {
                    alert("Veuillez entrer votre nom.");
                    return;
                }

                if (signaturePad.isEmpty()) {
                    alert("Veuillez signer avant d’enregistrer !");
                    return;
                }

                fetch("/stalhub/signature/upload", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            request_id: <?= (int) $requestId ?>,
                            signatory_name: name,
                            image: signaturePad.toDataURL("image/png")
                        })
                    })
                    .then(res => res.text())
                    .then(msg => {
                        const message = document.getElementById("signature-message");
                        message.textContent = "✅ Signature bien enregistrée.";
                        message.style.color = "green";

                        // ✅ Cacher tout le bloc de signature
                        document.getElementById("signature-area").style.display = "none";
                    })
                    .catch(() => {
                        const message = document.getElementById("signature-message");
                        message.textContent = "Erreur lors de l'enregistrement.";
                        message.style.color = "red";
                    });
            });
        });
    </script>
</body>

</html>