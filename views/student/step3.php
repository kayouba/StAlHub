<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>StalHub - Tableau de bord</title>
    <link rel="stylesheet" href="/stalhub/public/css/step3.css">
    <style>

    </style>
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
            <option value="Stage">Stage</option>
            <option value="Alternance">Alternance</option>
            <option value="CDD">CDD</option>
            <option value="CDI">CDI</option>
        </select>

        <label>Intitulé du poste</label>
        <input type="text" name="job_title" required>

        <div class="grid-2">
            <div>
                <label>Date de début</label>
                <input type="date" name="start_date" required>
            </div>
            <div>
                <label>Date de fin</label>
                <input type="date" name="end_date" required>
            </div>
        </div>

        <div class="grid-2">
            <div>
                <label>Volume horaire (heures/semaine)</label>
                <input type="number" name="weekly_hours" required>
            </div>
            <div>
                <label>Rémunération (€ / mois)</label>
                <input type="number" name="salary" step="0.01" required>
            </div>
        </div>

        <label>Missions</label>
        <textarea name="missions" rows="5" required></textarea>

        <div class="form-actions">
            <button type="button" onclick="history.back()">← Retour</button>
            <button type="submit">Continuer</button>
        </div>
    </form>
</main>
