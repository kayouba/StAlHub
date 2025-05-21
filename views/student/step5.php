<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>StalHub - Tableau de bord</title>
    <link rel="stylesheet" href="/stalhub/public/css/step5.css">
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form');
        const submitBtn = form.querySelector('button[type="submit"]');

        submitBtn.addEventListener('click', function (e) {
            const confirmed = confirm("Êtes-vous sûr de vouloir soumettre cette demande ?");
            if (!confirmed) {
                e.preventDefault();
            }
        });
    });
    </script>
</head>

<?php include __DIR__ . '/../components/sidebar.php'; ?>
<main class="request-container">
    <h1>Nouvelle Demande</h1>

    <div class="steps">
        <div class="step completed">✔</div>
        <div class="step completed">✔</div>
        <div class="step completed">✔</div>
        <div class="step completed">✔</div>
        <div class="step active"><span>5</span> Résumé</div>
    </div>

    <form action="/stalhub/student/request/submit" method="POST" enctype="multipart/form-data">

        <div class="summary-box">
            <h2>Résumé de votre demande</h2>

            <section>
                <h3>Informations personnelles</h3>
                <p><strong>Nom :</strong> <?= htmlspecialchars($_SESSION['step1']['last_name'] ?? '') ?></p>
                <p><strong>Prénom :</strong> <?= htmlspecialchars($_SESSION['step1']['first_name'] ?? '') ?></p>
                <p><strong>Email :</strong> <?= htmlspecialchars($_SESSION['step1']['email'] ?? '') ?></p>
                <p><strong>Numéro étudiant :</strong> <?= htmlspecialchars($_SESSION['step1']['student_number'] ?? '') ?></p>
                <p><strong>Formation :</strong> <?= htmlspecialchars($_SESSION['step1']['formation'] ?? '') ?></p>
            </section>

            <section>
                <h3>Poste</h3>
                <p><strong>Type :</strong> <?= htmlspecialchars($_SESSION['step2']['contract_type'] ?? '') ?></p>
                <p><strong>Intitulé :</strong> <?= htmlspecialchars($_SESSION['step2']['job_title'] ?? '') ?></p>
                <p><strong>Date début :</strong> <?= htmlspecialchars($_SESSION['step2']['start_date'] ?? '') ?></p>
                <p><strong>Date fin :</strong> <?= htmlspecialchars($_SESSION['step2']['end_date'] ?? '') ?></p>
                <p><strong>Volume horaire :</strong> <?= htmlspecialchars($_SESSION['step2']['weekly_hours'] ?? '') ?> h/semaine</p>
                <p><strong>Rémunération :</strong> <?= htmlspecialchars($_SESSION['step2']['salary'] ?? '') ?> €/mois</p>
                <p><strong>Missions :</strong> <?= nl2br(htmlspecialchars($_SESSION['step2']['missions'] ?? '')) ?></p>
            </section>
            
            <section>
                <h3>Entreprise</h3>
                <p><strong>SIRET :</strong> <?= htmlspecialchars($_SESSION['step3']['siret'] ?? '') ?></p>
                <p><strong>Nom :</strong> <?= htmlspecialchars($_SESSION['step3']['company_name'] ?? '') ?></p>
                <p><strong>Ville :</strong> <?= htmlspecialchars($_SESSION['step3']['city'] ?? '') ?></p>
                <p><strong>Code postal :</strong> <?= htmlspecialchars($_SESSION['step3']['postal_code'] ?? '') ?></p>
            </section>


            <section>
                <h3>Documents</h3>
                <?php foreach (['cv' => 'CV', 'insurance' => 'Assurance', 'justification' => 'Justificatif'] as $key => $label): ?>
                    <?php if (!empty($_SESSION['step4'][$key])): ?>
                        <p>
                            <strong><?= $label ?> :</strong>
                            <a href="<?= htmlspecialchars($_SESSION['step4'][$key]) ?>" target="_blank">Voir le document</a>
                        </p>
                    <?php endif; ?>
                <?php endforeach; ?>
            </section>


            <div class="form-actions">
                <a href="/stalhub/student/request/step4" class="button">← Retour</a>
                <button type="submit">Soumettre la demande</button>
            </div>
        </div>
    </form>
</main>
