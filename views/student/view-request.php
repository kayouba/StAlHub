<?php

use App\Lib\StatusTranslator;

$request = $request ?? [];
$documents = $documents ?? [];
$statusHistory = $statusHistory ?? [];

function safe($value): string
{
    return htmlspecialchars($value ?? '');
}

$statusLabel = StatusTranslator::translate($request['status'] ?? '');
$statusClass = match ($request['status']) {
    'VALIDEE', 'VALID_DIRECTION', 'VALIDE' => 'badge-green',
    'REFUSEE', 'REFUSEE_CFA', 'REFUSEE_PEDAGO' => 'badge-red',
    'SOUMISE', 'EN_ATTENTE_CFA', 'EN_ATTENTE_SIGNATURE_ENT' => 'badge-blue',
    default => 'badge-grey',
};

$allValidated = true;
$signed = false;
$conventionToSign = null;

foreach ($documents as $doc) {
    if (strtolower($doc['status']) !== 'validated') {
        $allValidated = false;
    }

    if (
        strtolower($doc['label']) === 'convention de stage' &&
        strtolower($doc['status']) === 'validated'
    ) {
        // Si le champ signed_by_student existe et vaut 1 => signé
        if (isset($doc['signed_by_student']) && (int)$doc['signed_by_student'] === 1) {
            $signed = true;
        } else {
            $conventionToSign = $doc; // à signer
        }
    }
}



$hasRejectedOrSubmitted = false;
foreach ($documents as $doc) {
    if (in_array(strtolower($doc['status']), ['rejected'])) {
        $hasRejectedOrSubmitted = true;
    }
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

<h1>Détail de la demande</h1>
<main class="request-container">

    <?php if (!empty($conventionTo)): ?>
        <section class="signature-callout">
            <h2>🖊️ Convention à signer</h2>
            <p>La convention a été validée et nécessite votre signature.</p>
            <a href="/stalhub/student/sign-convention?id=<?= $request['id'] ?>" class="button">✍️ Signer maintenant</a>
        </section>
    <?php elseif ($hasSignedConvention): ?>
        <section class="signature-callout">
            <h2>✅ Convention signée</h2>
            <p>La convention a été validée et signée. Vous pourrez la telecharger quand elle sera complètement signée.</p>
        </section>
    <?php else: ?>
        <!-- Rien à afficher -->
    <?php endif; ?>

    <section>
        <h2>Statut actuel</h2>
        <p><strong>État :</strong> <span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span></p>
    </section>

    <section>
        <h2>Historique du statut</h2>
        <button type="button" class="toggle-section"></button>
        <div class="collapsible">
            <ul class="timeline">
                <?php foreach ($statusHistory as $step): ?>
                    <li>
                        <?= date('d/m/Y H:i', strtotime($step['updated_at'])) ?>
                        <?php if (!empty($step['comment'])): ?>
                            <br><em><?= nl2br(safe($step['comment'])) ?></em>
                        <?php endif; ?>
                    </li>

                <?php endforeach; ?>
            </ul>
        </div>
    </section>

    <section>
        <h2>Entreprise</h2>
        <button type="button" class="toggle-section"></button>
        <div class="collapsible">
            <p><strong>Nom :</strong> <?= safe($request['company_name']) ?></p>

            <?php if (!$request['is_abroad']): ?>
            <p><strong>SIRET :</strong> <?= safe($request['siret']) ?></p>
            <?php else: ?>
            <p><em>Entreprise à l'étranger – pas de SIRET</em></p>
            <?php endif; ?>

            <p><strong>Ville :</strong> <?= safe($request['city']) ?></p>
            <p><strong>Code postal :</strong> <?= safe($request['postal_code']) ?></p>
        </div>
    </section>

    <section>
        <h2>Poste</h2>
        <button type="button" class="toggle-section"></button>
        <div class="collapsible">
            <p><strong>Type :</strong> <?= $request['contract_type'] === 'stage' ? 'Stage' : 'Alternance' ?></p>
            <p><strong>Intitulé :</strong> <?= safe($request['job_title']) ?></p>
            <p><strong>Date de début :</strong> <?= safe($request['start_date']) ?></p>
            <p><strong>Date de fin :</strong> <?= safe($request['end_date']) ?></p>
            <p><strong>Volume horaire :</strong> <?= safe($request['weekly_hours']) ?> h/semaine</p>
            <p><strong>Rémunération :</strong> <?= safe($request['salary']) ?> €/<?= safe($request['salary_duration']) ?></p>
            <p><strong>Missions :</strong> <?= nl2br(safe($request['mission'])) ?></p>
            <p><strong>Tuteur :</strong> <?= safe($request['supervisor_last_name'] . ' ' . $request['supervisor_first_name']) ?></p>
            <p><strong>Travail à distance :</strong> <?= $request['is_remote'] ? 'Oui' : 'Non' ?></p>
            <?php if ($request['is_remote']): ?>
            <p><strong>Jours de télétravail par semaine :</strong> <?= safe($request['remote_days_per_week']) ?> jour(s)</p>
            <?php endif; ?>

        </div>
    </section>

    <section>
        <h2>Documents fournis</h2>
        <button type="button" class="toggle-section"></button>
        <div class="collapsible">
            <form action="/stalhub/student/upload-correction" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                <ul>
                    <?php foreach ($documents as $doc): ?>
                        <li>
                            <strong><?= safe($doc['label']) ?> :  
                            <a href="/stalhub/document/view?file=<?= urlencode($doc['file_path']) ?>" target="_blank">Voir</a></strong>
                            <?php if ($doc['status'] === 'rejected'): ?>
                                <br><label>Remplacer le document :</label>
                                <input type="file" name="documents[<?= $doc['id'] ?>]" accept=".pdf,.jpg,.jpeg,.png">
                            <?php elseif ($doc['status'] === 'validated'): ?>
                                <span style="color: green;">(Validé)</span>
                            <?php elseif ($doc['status'] === 'submitted'): ?>
                                <span style="color: orange;">(En attente de validation)</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php if ($hasRejectedOrSubmitted): ?>
                    <button type="submit">Envoyer les documents corrigés</button>
                <?php endif; ?>
            </form>
        </div>
    </section>

    <div class="form-actions">
        <a href="javascript:history.back()" class="button">← Retour à la page précédente</a>
    </div>
</main>
</body>

</html>