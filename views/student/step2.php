<?php $step3 = $step3 ?? [];  $currentStep = $currentStep ?? 1; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>StalHub - Tableau de bord</title>
    <link rel="stylesheet" href="/stalhub/public/css/request-mission.css">
</head>

<?php include __DIR__ . '/../components/sidebar.php'; ?>

<main class="request-container">
    <h1>Nouvelle Demande</h1>
    <?php include __DIR__ . '/steps.php'; ?>

    <form id="step3-form" action="/stalhub/student/request/step3" method="POST" class="request-form">
        <h2>Informations sur le poste</h2>

        <label>Type de contrat<span class="required">*</span></label>
        <select name="contract_type" required>
            <option value="">-- Sélectionner --</option>
            <option value="Stage" <?= ($step2['contract_type'] ?? '') === 'Stage' ? 'selected' : '' ?>>Stage</option>
            <option value="apprenticeship" <?= ($step2['contract_type'] ?? '') === 'apprenticeship' ? 'selected' : '' ?>>Alternance</option>
        </select>

        <label>Intitulé du poste <span class="required">*</span></label>
        <input type="text" name="job_title" value="<?= htmlspecialchars($step2['job_title'] ?? '') ?>" required>

        <div class="grid-2">
            <div>
                <label>Date de début <span class="required">*</span> </label>
                <input type="date" name="start_date" value="<?= htmlspecialchars($step2['start_date'] ?? '') ?>" required>
            </div>
            <div>
                <label>Date de fin<span class="required">*</span> </label>
                <input type="date" name="end_date" value="<?= htmlspecialchars($step2['end_date'] ?? '') ?>" required>
            </div>
        </div>

        <div class="grid-2">
            <div>
                <label>Volume horaire (heures/semaine) <span class="required">*</span> </label>
                <input type="number" id="weekly_hours" name="weekly_hours" value="<?= htmlspecialchars($step2['weekly_hours'] ?? '') ?>" required min="20" max="35">
            </div>
            <div>
                <label>Rémunération (€ / mois) <span class="required">*</span> </label>
                <input type="number" name="salary" step="0.01" value="<?= htmlspecialchars($step2['salary'] ?? '') ?>" required>
            </div>
        </div>

        <label>Missions <span class="required">*</span> </label>
        <textarea name="missions" rows="10" required><?= htmlspecialchars($step2['missions'] ?? '') ?></textarea>

        <label>Le télétravail est-il possible ?<span class="required">*</span> </label>
        <select name="is_remote" id="is_remote" required>
            <option value="">-- Sélectionner --</option>
            <option value="1" <?= ($step2['is_remote'] ?? '') === '1' ? 'selected' : '' ?>>Oui</option>
            <option value="0" <?= ($step2['is_remote'] ?? '') === '0' ? 'selected' : '' ?>>Non</option>
        </select>

        <label>Si oui, nombre de jours de télétravail par semaine</label>
        <input type="number" name="remote_days_per_week" id="remote_days_per_week" min="0" max="5"
               value="<?= htmlspecialchars($step2['remote_days_per_week'] ?? '') ?>">

        <div class="form-actions">
            <a href="/stalhub/student/new-request" class="button">← Retour</a>
            <button type="submit">Continuer</button>
        </div>
    </form>
</main>

<script>
    const form = document.getElementById('step3-form');
    const remoteSelect = document.getElementById('is_remote');
    const remoteDaysInput = document.getElementById('remote_days_per_week');

    form.addEventListener('submit', function (e) {
        const isRemote = remoteSelect.value;
        const remoteDays = remoteDaysInput.value;

        if (isRemote === "1" && (!remoteDays || parseInt(remoteDays) <= 0)) {
            e.preventDefault();
            alert("Veuillez indiquer le nombre de jours de télétravail par semaine.");
            remoteDaysInput.focus();
        }
    });

    remoteSelect.addEventListener('change', () => {
        if (remoteSelect.value === "1") {
            remoteDaysInput.required = true;
        } else {
            remoteDaysInput.required = false;
            remoteDaysInput.value = '';
        }
    });

    const weeklyHoursInput = document.getElementById('weekly_hours');

    form.addEventListener('submit', function (e) {
        const weeklyHours = parseInt(weeklyHoursInput.value);

        if (isNaN(weeklyHours) || weeklyHours < 20 || weeklyHours > 35) {
            e.preventDefault();
            alert("Le volume horaire doit être compris entre 20 et 35 heures par semaine.");
            weeklyHoursInput.focus();
        }
    });

</script>

</html>
