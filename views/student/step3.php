<?php $step2 = $step2 ?? [];  $currentStep = $currentStep ?? 1;?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>StalHub - Tableau de bord</title>
    <link rel="stylesheet" href="/stalhub/public/css/request-company.css">
    <script src="/stalhub/public/js/request.js" defer></script>
</head>

<?php include __DIR__ . '/../components/sidebar.php'; ?>
<main class="request-container">
    <h1>Nouvelle Demande</h1>
    <?php include __DIR__ . '/steps.php'; ?>

    <form action="/stalhub/student/request/step4" method="POST" class="request-form">
        <h2>Informations sur l’entreprise</h2>
        <label>Pays <span style="color: red;">*</span></label>
        <select name="country" id="country" required>
            <option value="France" <?= ($step3['country'] ?? '') === 'France' ? 'selected' : '' ?>>France</option>
            <option value="Étranger" <?= ($step3['country'] ?? '') === 'Étranger' ? 'selected' : '' ?>>Étranger</option>
        </select>
        <div id="foreign-country-group" style="<?= ($step3['country'] ?? '') === 'Étranger' ? '' : 'display: none;' ?>">
            <label>Pays de l’entreprise</label>
            <input type="text" name="foreign_country" value="<?= htmlspecialchars($step3['foreign_country'] ?? '') ?>">
        </div>
        <div id="siret-group">
            <label>SIRET de l'entreprise</label>
            <input type="text" name="siret" id="siret" value="<?= htmlspecialchars($step3['siret'] ?? '') ?>">
            <div id="siret-result" style="margin-bottom: 1em; color: green;"></div>
        </div>
        <div id="manual-entry-note" style="display: none; margin-bottom: 1rem; color: #555; font-style: italic;">
            🔎 Les informations doivent être saisies manuellement pour une entreprise à l'étranger.
        </div>
        <label>Nom de l'entreprise<span style="color: red;">*</span></label>
        <input type="text" name="company_name" value="<?= htmlspecialchars($step3['company_name'] ?? '') ?>" required>
        
        <label>Ville<span style="color: red;">*</span></label>
        <input type="text" name="city" value="<?= htmlspecialchars($step3['city'] ?? '') ?>" required>

        <label>Code Postal<span style="color: red;">*</span></label>
        <input type="text" name="postal_code" value="<?= htmlspecialchars($step3['postal_code'] ?? '') ?>" required>

        <label>Nom du tuteur de stage en entreprise<span style="color: red;">*</span></label>
        <input type="text" name="supervisor_last_name" value="<?= htmlspecialchars($step3['supervisor_last_name'] ?? '') ?>" required>

        <label>Prénom du tuteur de stage en entreprise<span style="color: red;">*</span></label>
        <input type="text" name="supervisor_first_name" value="<?= htmlspecialchars($step3['supervisor_first_name'] ?? '') ?>" required>

        <label>Mail du tuteur de stage en entreprise<span style="color: red;">*</span></label>
        <input type="email" name="supervisor_email" value="<?= htmlspecialchars($step3['supervisor_email'] ?? '') ?>" required
            placeholder="tuteur@entreprise.com">

        <label>Numéro du tuteur<span style="color: red;">*</span> <small>avec indicatif international (ex : +33...)</small></label>
        <input type="tel" name="supervisor_num" value="<?= htmlspecialchars($step3['supervisor_num'] ?? '') ?>" required pattern="^\+[0-9]{7,15}$" title="Numéro au format international requis, ex : +33612345678">

        <label>Rôle du tuteur <span style="color: red;">*</span> (Chef de projet, Consultant...)</label>
        <input type="text" name="supervisor_position" value="<?= htmlspecialchars($step3['supervisor_position'] ?? '') ?>" required>



        <div class="form-actions">
            <a href="/stalhub/student/request/step2" class="button">← Retour</a>
            <button type="submit">Continuer</button>
        </div>
    </form>
</main>
