<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouvelle Demande - Étudiant</title>
    <link rel="stylesheet" href="/stalhub/public/css/new-request.css">
</head>
<body>

<?php include __DIR__ . '/../components/sidebar.php'; ?>

<main class="request-container">
    <h1>Nouvelle Demande</h1>

    <div class="steps">
        <div class="step active"><span>1</span> Infos pers.</div>
        <div class="step"><span>2</span> Mission</div>
        <div class="step"><span>3</span> Entreprise</div>
        <div class="step"><span>4</span> Documents</div>
        <div class="step"><span>5</span> Validation</div>
    </div>

    <form action="/stalhub/student/request/step2" method="POST" class="request-form">

        <h2>Informations personnelles</h2>

        <label>Nom</label>
        <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required
               oninvalid="this.setCustomValidity('Veuillez saisir votre nom')"
               oninput="this.setCustomValidity('')">

        <label>Prénom</label>
        <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required
               oninvalid="this.setCustomValidity('Veuillez saisir votre prénom')"
               oninput="this.setCustomValidity('')">

        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required
               oninvalid="this.setCustomValidity('Veuillez entrer une adresse email valide')"
               oninput="this.setCustomValidity('')">

        <h2>Étudiant</h2>

        <label>Numéro d'étudiant</label>
        <input type="text" name="student_number" value="<?= htmlspecialchars($user['student_number'] ?? '') ?>" required
               oninvalid="this.setCustomValidity('Veuillez entrer votre numéro d\'étudiant')"
               oninput="this.setCustomValidity('')">

        <label>Parcours</label>
        <input type="text" name="parcours" value="MIAGE" readonly>

        <label>Formation</label>
        <select name="formation" required
                oninvalid="this.setCustomValidity('Veuillez sélectionner votre formation')"
                oninput="this.setCustomValidity('')">
            <option value="">-- Sélectionner --</option>
            <option value="Licence1" <?= ($user['formation'] ?? '') === 'Licence1' ? 'selected' : '' ?>>Licence 3</option>
            <option value="Master1" <?= ($user['formation'] ?? '') === 'Master1' ? 'selected' : '' ?>>Master 1</option>
            <option value="Master2" <?= ($user['formation'] ?? '') === 'Master2' ? 'selected' : '' ?>>Master 2</option>
        </select>

        <label>Téléphone</label>
        <input type="tel" name="phone"
               value="<?= htmlspecialchars($user['phone_number'] ?? '') ?>"
               pattern="^0[1-9]\d{8}$"
               placeholder="Ex: 0612345678" required
               oninvalid="this.setCustomValidity('Veuillez entrer un numéro de téléphone valide (ex: 0612345678)')"
               oninput="this.setCustomValidity('')">

        <div class="form-actions">
            <button type="button" onclick="history.back()">← Retour</button>
            <button type="submit">Continuer</button>
        </div>
    </form>
</main>
</body>
</html>
