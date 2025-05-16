<?php $step3 = $step3 ?? []; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>StalHub - Tableau de bord</title>
    <link rel="stylesheet" href="/stalhub/public/css/step4.css">
</head>

<?php include __DIR__ . '/../components/sidebar.php'; ?>

<main class="request-container">
    <h1>Nouvelle Demande</h1>

    <div class="steps">
        <div class="step completed">✔</div>
        <div class="step completed">✔</div>
        <div class="step completed">✔</div>
        <div class="step active"><span>4</span> Documents</div>
        <div class="step"><span>5</span> Résumé</div>
    </div>

    <form action="/stalhub/student/request/step5" method="POST" enctype="multipart/form-data" class="request-form">
        <h2>Téléversement des documents</h2>

        <label>CV <small>(PDF, max 2 Mo)</small></label>
        <input type="file" name="cv" accept=".pdf" required>

        <label>Attestation d'assurance <small>(PDF ou image)</small></label>
        <input type="file" name="insurance" accept=".pdf,.jpg,.jpeg,.png" required>

        <label>Autre justificatif 
            <small>(carte étudiante, pièce d’identité, etc.)</small>
        </label>
        <input type="file" name="justification" accept=".pdf,.jpg,.jpeg,.png">

        <div class="form-actions">
            <a href="/stalhub/student/request/step3" class="button">← Retour</a>
            <button type="submit">Continuer</button>
        </div>
    </form>
</main>
