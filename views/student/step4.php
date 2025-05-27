<?php $step4 = $step4 ?? []; $step3 = $_SESSION['step3'] ?? []; $currentStep = $currentStep ?? 1; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>StalHub - Tableau de bord</title>
    <link rel="stylesheet" href="/stalhub/public/css/request-documents.css">
    <style>
        label.required::after {
            content: " *";
            color: red;
        }
    </style>
</head>

<?php include __DIR__ . '/../components/sidebar.php'; ?>

<main class="request-container">
    <h1>Nouvelle Demande</h1>

    <?php include __DIR__ . '/steps.php'; ?>

    <form action="/stalhub/student/request/step4" method="POST" enctype="multipart/form-data" class="request-form">

        <h2>Téléversement des documents</h2>

        <!-- CV -->
        <label class="required">CV <small>(PDF, max 2 Mo)</small></label>
        <?php if (!empty($step4['cv'])): ?>
            <p>
                <a href="/stalhub/document/view?file=<?= urlencode($step4['cv']) ?>" target="_blank">Voir le CV actuel</a><br>
                <em class="note">Vous pouvez téléverser un nouveau fichier pour le remplacer.</em>
            </p>
        <?php endif; ?>
        <input type="file" name="cv" accept=".pdf" <?= empty($step4['cv']) ? 'required' : '' ?>>
        <!-- Assurance -->
        <label class="required">Attestation d'assurance <small>(PDF ou image)</small></label>
        <?php if (!empty($step4['insurance'])): ?>
            <p>
                <a href="/stalhub/document/view?file=<?= urlencode($step4['insurance']) ?>" target="_blank">Voir l'assurance actuelle</a><br>
                <em class="note">Vous pouvez téléverser un nouveau fichier pour le remplacer.</em>
            </p>
        <?php endif; ?>
        <input type="file" name="insurance" accept=".pdf,.jpg,.jpeg,.png" <?= empty($step4['insurance']) ? 'required' : '' ?>>
        <label class="required">Récapitulatif PStage</label>
        <input type="file" name="recap_pstage" accept=".pdf" required>

        <?php if (($step3['country'] ?? '') === 'Étranger'): ?>
            <hr>
            <h3>Documents supplémentaires pour un stage à l’étranger</h3>

            <label class="required">Attestation de sécurité sociale</label>
            <input type="file" name="social_security" accept=".pdf" required>

            <label class="required">Attestation de prise en charge de la CPAM</label>
            <input type="file" name="cpam" accept=".pdf" required>

            <label class="required">Formulaire "Personal data collection form"</label>
            <input type="file" name="data_collection_form" accept=".pdf" required>

            <label>Formulaire protection contre les accidents du travail <small>(si gratification &gt; 15%)</small></label>
            <input type="file" name="accident_protection" accept=".pdf">
        <?php endif; ?>

        <div class="form-actions">
            <a href="/stalhub/student/request/step3" class="button">← Retour</a>
            <button type="submit">Continuer</button>
        </div>
    </form>
</main>
