<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>StalHub - Tableau de bord</title>
    <link rel="stylesheet" href="/stalhub/public/css/step2.css">
    <style>

    </style>
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

        <label>Numéro de SIRET</label>
        <input type="text" name="siret" required>

        <label>Nom de l'entreprise</label>
        <input type="text" name="company_name" required>

        <label>Numéro SIREN</label>
        <input type="text" name="siren">

        <label>Secteur d'activité</label>
        <select name="industry" required>
            <option value="">-- Sélectionner --</option>
            <option value="informatique">Informatique</option>
            <option value="finance">Finance</option>
        </select>

        <label>Ville</label>
        <input type="text" name="city" required>

        <label>Code Postal</label>
        <input type="text" name="postal_code" required>

        <div class="form-actions">
            <button type="button" onclick="history.back()">← Retour</button>
            <button type="submit">Continuer</button>
        </div>
    </form>
</main>
