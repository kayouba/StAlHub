<?php
use App\Lib\StatusTranslator;

$request = $request ?? [];
$documents = $documents ?? [];
$statusHistory = $statusHistory ?? [];

function safe($value): string {
    return htmlspecialchars($value ?? '');
}

$statusLabel = StatusTranslator::translate($request['status'] ?? '');
$statusClass = match($request['status']) {
    'VALIDEE', 'VALID_DIRECTION', 'VALIDE' => 'badge-green',
    'REFUSEE', 'REFUSEE_CFA', 'REFUSEE_PEDAGO' => 'badge-red',
    'SOUMISE', 'EN_ATTENTE_CFA', 'EN_ATTENTE_SIGNATURE_ENT' => 'badge-blue',
    default => 'badge-grey',
};
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

    <section>
        <h2>Statut actuel</h2>
        <p><strong>État :</strong> <span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span></p>
    </section>

    <section>
        <h2>Historique du statut</h2>
        <ul class="timeline">
            <?php foreach ($statusHistory as $step): ?>
                <li>
                    <strong><?= safe(StatusTranslator::translate($step['label'])) ?></strong>
                    — <?= date('d/m/Y H:i', strtotime($step['updated_at'])) ?>
                    <?php if (!empty($step['comment'])): ?>
                        <br><em><?= nl2br(safe($step['comment'])) ?></em>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

    <section>
        <h2>Entreprise</h2>
        <p><strong>Nom :</strong> <?= safe($request['company_name']) ?></p>
        <p><strong>SIRET :</strong> <?= safe($request['siret']) ?></p>
        <p><strong>Ville :</strong> <?= safe($request['city']) ?></p>
        <p><strong>Code postal :</strong> <?= safe($request['postal_code']) ?></p>
    </section>

    <section>
        <h2>Poste</h2>
        <p><strong>Type :</strong> <?= $request['contract_type'] === 'stage' ? 'Stage' : 'Alternance' ?></p>
        <p><strong>Intitulé :</strong> <?= safe($request['job_title']) ?></p>
        <p><strong>Date de début :</strong> <?= safe($request['start_date']) ?></p>
        <p><strong>Date de fin :</strong> <?= safe($request['end_date']) ?></p>
        <p><strong>Volume horaire :</strong> <?= safe($request['weekly_hours']) ?> h/semaine</p>
        <p><strong>Rémunération :</strong> <?= safe($request['salary']) ?> €/<?= safe($request['salary_duration']) ?></p>
        <p><strong>Missions :</strong> <?= nl2br(safe($request['mission'])) ?></p>
        <p><strong>Tuteur :</strong> <?= safe($request['supervisor_last_name'] . ' ' . $request['supervisor_first_name']) ?></p>
    </section>

    <section>
        <h2>Documents fournis</h2>
        <form action="/stalhub/student/upload-correction" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
            <ul>
                <?php foreach ($documents as $doc): ?>
                    <li>
                        <strong><?= safe($doc['label']) ?> :</strong>
                        <a href="/stalhub/document/view?file=<?= urlencode($doc['file_path']) ?>" target="_blank">Voir</a>

                        <?php if ($doc['status'] === 'rejected'): ?>
                            <br>
                            <label>Remplacer le document :</label>
                            <input type="file" name="documents[<?= $doc['id'] ?>]" accept=".pdf,.jpg,.jpeg,.png">
                        <?php elseif ($doc['status'] === 'validated'): ?>
                            <span style="color: green;">(Validé)</span>
                        <?php elseif ($doc['status'] === 'submitted'): ?>
                            <span style="color: orange;">(En attente de validation)</span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <button type="submit">Envoyer les documents corrigés</button>
        </form>
    </section>


    <?php if ($request['status'] === 'VALIDE'): ?>
        <section>
            <h2>Convention</h2>
            <a href="/stalhub/student/convention/download?id=<?= $request['id'] ?>" class="button">📄 Télécharger la convention signée</a>
        </section>
    <?php endif; ?>

    <div class="form-actions">
        <a href="/stalhub/dashboard" class="button">← Retour au tableau de bord</a>
    </div>
</main>
</body>
</html>
