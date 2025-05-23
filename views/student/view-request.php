<?php
$request = $request ?? [];
$documents = $documents ?? [];

function safe($value): string {
    return htmlspecialchars($value ?? '');
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>StalHub - Détail de la demande</title>
    <link rel="stylesheet" href="/stalhub/public/css/request-view.css">
</head>
<body>

<?php include __DIR__ . '/../components/sidebar.php'; ?>

<main class="request-container">
    <h1>Détail de la demande</h1>

    <div class="steps">
        <div class="step <?= ($request['status'] ?? '') === 'SOUMISE' ? 'active' : 'completed' ?>">1</div>
        <div class="step <?= ($request['status'] ?? '') === 'VALIDEE' ? 'active' : '' ?>">2</div>
        <div class="step <?= ($request['status'] ?? '') === 'REFUSEE' ? 'active' : '' ?>">3</div>
    </div>

    <section>
        <h2>Informations personnelles</h2>
        <p><strong>Nom :</strong> <?= safe($request['last_name']) ?></p>
        <p><strong>Prénom :</strong> <?= safe($request['first_name']) ?></p>
        <p><strong>Email :</strong> <?= safe($request['email']) ?></p>
        <p><strong>Numéro étudiant :</strong> <?= safe($request['student_number']) ?></p>
        <p><strong>Téléphone :</strong> <?= safe($request['phone']) ?></p>
    </section>

    <section>
        <h2>Entreprise</h2>
        <p><strong>Nom :</strong> <?= safe($request['company_name']) ?></p>
        <p><strong>SIRET :</strong> <?= safe($request['siret']) ?></p>
        <?php if (!empty($request['industry'])): ?>
            <p><strong>Secteur :</strong> <?= safe($request['industry']) ?></p>
        <?php endif; ?>
        <p><strong>Ville :</strong> <?= safe($request['city']) ?></p>
        <p><strong>Code postal :</strong> <?= safe($request['postal_code']) ?></p>
    </section>

    <section>
        <h2>Poste</h2>
        <p><strong>Type :</strong> <?= ($request['contract_type'] ?? '') === 'stage' ? 'Stage' : 'Alternance' ?></p>
        <p><strong>Intitulé :</strong> <?= safe($request['job_title']) ?></p>
        <p><strong>Date de début :</strong> <?= safe($request['start_date']) ?></p>
        <p><strong>Date de fin :</strong> <?= safe($request['end_date']) ?></p>
        <p><strong>Volume horaire :</strong> <?= safe($request['weekly_hours']) ?> h/semaine</p>
        <p><strong>Rémunération :</strong> <?= safe($request['salary']) ?> €/<?= safe($request['salary_duration']) ?></p>
        <p><strong>Missions :</strong> <?= nl2br(safe($request['mission'])) ?></p>

    </section>

    <section>
        <h2>Documents fournis</h2>
        <ul>
            <?php foreach ($documents as $doc): ?>
                <li>
                    <strong><?= safe($doc['label']) ?> :</strong>
                    <a href="/stalhub/document/view?file=<?= urlencode($doc['file_path']) ?>" target="_blank">Voir le document</a>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

    <section>
        <h2>Statut</h2>
        <p><strong>État actuel :</strong> <?= ucfirst(strtolower($request['status'] ?? 'Inconnu')) ?></p>
    </section>

    <div class="form-actions">
        <a href="/stalhub/dashboard" class="button">← Retour au tableau de bord</a>
    </div>
</main>
</body>
</html>
