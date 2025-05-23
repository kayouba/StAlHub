<?php $step4 = $step4 ?? [];  $currentStep = $currentStep ?? 1; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>StalHub - Tableau de bord</title>
    <link rel="stylesheet" href="/stalhub/public/css/request-documents.css">
</head>

<?php include __DIR__ . '/../components/sidebar.php'; ?>

<main class="request-container">
    <h1>Nouvelle Demande</h1>

    <?php include __DIR__ . '/steps.php'; ?>

<form action="/stalhub/student/request/step4" method="POST" enctype="multipart/form-data" class="request-form">

    <h2>Téléversement des documents</h2>

    <!-- CV -->
    <label>CV <small>(PDF, max 2 Mo)</small></label>
    <?php if (!empty($step4['cv'])): ?>
        <p>
            <a href="/stalhub/document/view?file=<?= urlencode($step4['cv']) ?>" target="_blank">Voir le CV actuel</a><br>
            <em class="note">Vous pouvez téléverser un nouveau fichier pour le remplacer.</em>
        </p>
    <?php endif; ?>
    <input type="file" name="cv" accept=".pdf">

    <!-- Assurance -->
    <label>Attestation d'assurance <small>(PDF ou image)</small></label>
    <?php if (!empty($step4['insurance'])): ?>
        <p>
            <a href="/stalhub/document/view?file=<?= urlencode($step4['insurance']) ?>" target="_blank">Voir l'assurance actuelle</a><br>
            <em class="note">Vous pouvez téléverser un nouveau fichier pour le remplacer.</em>
        </p>
    <?php endif; ?>
    <input type="file" name="insurance" accept=".pdf,.jpg,.jpeg,.png">

    <!-- Justificatif -->
    <label>Pièce d'identité <small>(carte d'identité, titre de séjour, etc.)</small></label>
    <?php if (!empty($step4['justification'])): ?>
        <p>
            <a href="/stalhub/document/view?file=<?= urlencode($step4['justification']) ?>" target="_blank">Voir la pièce actuelle</a><br>
            <em class="note">Vous pouvez téléverser un nouveau fichier pour le remplacer.</em>
        </p>
    <?php endif; ?>
    <input type="file" name="justification" accept=".pdf,.jpg,.jpeg,.png">


    <div class="form-actions">
        <a href="/stalhub/student/request/step3" class="button">← Retour</a>
        <button type="submit">Continuer</button>
    </div>
</form>

</main>
