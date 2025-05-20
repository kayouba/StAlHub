<?php $step2 = $step2 ?? []; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>StalHub - Tableau de bord</title>
    <link rel="stylesheet" href="/stalhub/public/css/step2.css">
    <script src="/stalhub/public/js/request.js" defer></script>
</head>

<?php include __DIR__ . '/../components/sidebar.php'; ?>
<main class="request-container">
    <h1>Nouvelle Demande</h1>

    <div class="steps">
        <div class="step completed">✔</div>
        <div class="step active"><span>2</span> Entreprise</div>
        <div class="step"><span>3</span> Poste</div>
        <div class="step"><span>4</span> Documents</div>
        <div class="step"><span>5</span> Résumé</div>
    </div>

    <form action="/stalhub/student/request/step3" method="POST" class="request-form">
        <h2>Informations sur l’entreprise</h2>

        <label>SIRET de l'entreprise</label>
        <input type="text" name="siret" id="siret" value="<?= htmlspecialchars($step2['siret'] ?? '') ?>" required>
        <div id="siret-result" style="margin-bottom: 1em; color: green;"></div>

        <label>Nom de l'entreprise</label>
        <input type="text" name="company_name" value="<?= htmlspecialchars($step2['company_name'] ?? '') ?>" required>

        <label>Numéro SIREN</label>
        <input type="text" name="siren" value="<?= htmlspecialchars($step2['siren'] ?? '') ?>">

        <!-- <label>Secteur d'activité</label>
        <select name="industry" required>
            <option value="">-- Sélectionner --</option>
            <option value="informatique" <?= ($step2['industry'] ?? '') === 'informatique' ? 'selected' : '' ?>>Informatique</option>
            <option value="finance" <?= ($step2['industry'] ?? '') === 'finance' ? 'selected' : '' ?>>Finance</option>
        </select> -->

        <label>Ville</label>
        <input type="text" name="city" value="<?= htmlspecialchars($step2['city'] ?? '') ?>" required>

        <label>Code Postal</label>
        <input type="text" name="postal_code" value="<?= htmlspecialchars($step2['postal_code'] ?? '') ?>" required>

        <label>Email du référent dans l'entreprise</label>
        <input type="email" name="referent_email"
               value="<?= htmlspecialchars($step2['referent_email'] ?? '') ?>"
               placeholder="jean.dupont@entreprise.com" required>

        <div class="form-actions">
            <a href="/stalhub/student/new-request" class="button">← Retour</a>
            <button type="submit">Continuer</button>
        </div>
    </form>
</main>
