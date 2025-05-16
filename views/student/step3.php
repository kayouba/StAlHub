<?php $step2 = $step2 ?? []; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>StalHub - Tableau de bord</title>
    <link rel="stylesheet" href="/stalhub/public/css/step3.css">
</head>

<?php include __DIR__ . '/../components/sidebar.php'; ?>

<main class="request-container">
    <h1>Nouvelle Demande</h1>

    <div class="steps">
        <div class="step completed">✔</div>
        <div class="step completed">✔</div>
        <div class="step active"><span>3</span> Poste</div>
        <div class="step"><span>4</span> Documents</div>
        <div class="step"><span>5</span> Résumé</div>
    </div>

    <form action="/stalhub/student/request/step4" method="POST" class="request-form">
        <h2>Informations sur le poste</h2>

        <label>Type de contrat</label>
        <select name="contract_type" required>
            <option value="">-- Sélectionner --</option>
            <option value="Stage" <?= ($step2['contract_type'] ?? '') === 'Stage' ? 'selected' : '' ?>>Stage</option>
            <option value="apprenticeship" <?= ($step2['contract_type'] ?? '') === 'apprenticeship' ? 'selected' : '' ?>>Alternance</option>
        </select>

        <label>Intitulé du poste</label>
        <input type="text" name="job_title" value="<?= htmlspecialchars($step2['job_title'] ?? '') ?>" required>

        <div class="grid-2">
            <div>
                <label>Date de début</label>
                <input type="date" name="start_date" value="<?= htmlspecialchars($step2['start_date'] ?? '') ?>" required>
            </div>
            <div>
                <label>Date de fin</label>
                <input type="date" name="end_date" value="<?= htmlspecialchars($step2['end_date'] ?? '') ?>" required>
            </div>
        </div>

        <div class="grid-2">
            <div>
                <label>Volume horaire (heures/semaine)</label>
                <input type="number" name="weekly_hours" value="<?= htmlspecialchars($step2['weekly_hours'] ?? '') ?>" required>
            </div>
            <div>
                <label>Rémunération (€ / mois)</label>
                <input type="number" name="salary" step="0.01" value="<?= htmlspecialchars($step2['salary'] ?? '') ?>" required>
            </div>
        </div>

        <label>Missions</label>
        <textarea name="missions" rows="5" required><?= htmlspecialchars($step2['missions'] ?? '') ?></textarea>

        <div class="form-actions">
            <a href="/stalhub/student/request/step2" class="button">← Retour</a>
            <button type="submit">Continuer</button>
        </div>
    </form>
</main>
