<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>StalHub - Validation de la demande</title>
    <link rel="stylesheet" href="/stalhub/public/css/request-summary.css">
</head>
<style>
    .dual-box {
        display: flex;
        gap: 2rem;
        justify-content: space-between;
        margin-top: 2rem;
        flex-wrap: wrap;
    }

    .box-card {
        flex: 1 1 45%;
        background: #f9f9f9;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        border-left: 5px solid #0052cc;
    }

    .box-card.upload {
        border-left-color: #28a745;
    }

    .box-card h3 {
        margin-top: 0;
        font-size: 1.2rem;
        color: #333;
    }

    .box-card p,
    .box-card form {
        margin-top: 1rem;
    }

    button.pdf-btn,
    button[type="submit"] {
        display: inline-block;
        background-color: #0052cc;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        cursor: pointer;
        text-decoration: none;
        font-weight: bold;
    }

    button.pdf-btn:hover,
    button[type="submit"]:hover {
        background-color: #003d99;
    }

    .document-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .document-card {
        width: 200px;
        background: linear-gradient(to bottom right, #f3f4f6, #e0e7ff);
        border-radius: 10px;
        overflow: hidden;
        text-decoration: none;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        display: flex;
        flex-direction: column;
    }

    .document-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
    }

    .doc-preview {
        height: 150px;
        background: white;
        overflow: hidden;
    }

    .doc-preview iframe {
        width: 100%;
        height: 100%;
        border: none;
    }

    .doc-meta {
        padding: 0.75rem;
        text-align: center;
    }

    .doc-title {
        font-weight: 600;
        color: #1e3a8a;
        font-size: 0.95rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }


    .doc-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .view-link {
        background: none;
        border: none;
        color: #2563eb;
        font-weight: bold;
        text-decoration: none;
        cursor: pointer;
    }

    .view-link:hover {
        color: #1d4ed8;
        text-decoration: underline;
    }

    .delete-btn {
        background: none;
        border: none;
        color: #dc2626;
        font-size: 1.1rem;
        cursor: pointer;
    }

    .delete-btn:hover {
        color: #b91c1c;
        transform: scale(1.1);
    }

    .zip-download {
        margin-top: 1.5rem;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .zip-btn {
        background-color: rgb(119, 160, 249);
        color: white;
        padding: 0.4rem 0.9rem;
        border-radius: 6px;
        text-decoration: none;
        font-weight: bold;
        font-size: 0.9rem;
        transition: background 0.2s ease;
    }

    .zip-btn:hover {
        background-color: #1e40af;
    }
</style>

<body>
    <?php include __DIR__ . '/../components/sidebar.php'; ?>

    <main class="request-container">
        <div class="summary-box">
            <h2>Détails de la demande</h2>

            <!-- ÉTUDIANT -->
            <section>
                <h3>👨‍🏫 Étudiant</h3>
                <p><strong>Nom :</strong> <?= htmlspecialchars($request['student']) ?></p>
                <p><strong>Email :</strong> <?= htmlspecialchars($request['student_email']) ?></p>
                <p><strong>Numéro étudiant :</strong> <?= htmlspecialchars($request['student_number']) ?></p>
                <p><strong>Programme :</strong> <?= htmlspecialchars($request['program']) ?></p>
                <p><strong>Formation :</strong> <?= htmlspecialchars($request['track']) ?></p>
                <p><strong>Niveau :</strong> <?= htmlspecialchars($request['level']) ?></p>
            </section>

            <!-- ENTREPRISE -->
            <section>
                <h3>🏢 Entreprise</h3>
                <p><strong>Nom :</strong> <?= htmlspecialchars($request['company_name']) ?></p>
                <p><strong>Ville :</strong> <?= htmlspecialchars($request['company_city']) ?></p>
                <p><strong>SIRET :</strong> <?= htmlspecialchars($request['company_siret']) ?></p>
                <p><strong>Email contact :</strong> <?= htmlspecialchars($request['company_email']) ?></p>
            </section>

            <!-- DEMANDE -->
            <section>
                <h3>📄 Détails de la demande</h3>
                <p><strong>Type de contrat :</strong> <?= htmlspecialchars($request['contract_type']) ?></p>
                <p><strong>Intitulé du poste :</strong> <?= htmlspecialchars($request['job_title']) ?></p>
                <p><strong>Missions :</strong> <?= nl2br(htmlspecialchars($request['mission'])) ?></p>
                <p><strong>Date de début :</strong> <?= htmlspecialchars($request['start_date']) ?></p>
                <p><strong>Date de fin :</strong> <?= htmlspecialchars($request['end_date']) ?></p>
                <p><strong>Volume horaire :</strong> <?= htmlspecialchars($request['weekly_hours']) ?> h/semaine</p>
                <p><strong>Rémunération :</strong> <?= htmlspecialchars($request['salary_value']) ?> €/<?= htmlspecialchars($request['salary_duration']) ?></p>
            </section>

            <!-- TUTEUR ENTREPRISE -->
            <section>
                <h3>👔 Tuteur en entreprise</h3>
                <p><strong>Nom :</strong> <?= htmlspecialchars($request['supervisor_first_name'] . ' ' . $request['supervisor_last_name']) ?></p>
                <p><strong>Email :</strong> <?= htmlspecialchars($request['supervisor_email']) ?></p>
                <p><strong>Poste :</strong> <?= htmlspecialchars($request['supervisor_position']) ?></p>
            </section>

            <!-- TUTEUR UNIVERSITAIRE -->
            <section>
                <h3>🎓 Tuteur universitaire</h3>
                <p><strong>Nom :</strong> <?= htmlspecialchars($request['tutor_first_name'] . ' ' . $request['tutor_last_name']) ?></p>
                <p><strong>Email :</strong> <?= htmlspecialchars($request['tutor_email']) ?></p>
            </section>

            <!-- Document UNIVERSITAIRE -->
            <section class="document-section">
                <h3>📦 Documents liés à la demande</h3>
                <?php if (!empty($request['documents'])): ?>
                    <div class="document-grid">
                        <?php foreach ($request['documents'] as $doc): ?>
                            <a href="/stalhub/document/view?file=<?= urlencode($doc['file_path']) ?>" target="_blank" class="document-card">
                                <div class="doc-preview">
                                    <iframe src="/stalhub/document/view?file=<?= urlencode($doc['file_path']) ?>" frameborder="0"></iframe>
                                </div>
                                <div class="doc-meta">
                                    <div class="doc-title"><?= htmlspecialchars($doc['label']) ?></div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p><em>Aucun document lié à cette demande.</em></p>
                <?php endif; ?>
            </section>

            <?php if (empty($readonly)): ?>
                
                <!-- BOUTONS EN BAS -->
                <div class="form-actions">
                    <a href="/stalhub/direction/dashboard" class="button">← Retour</a>
                </div>
            <?php endif; ?>

        </div>
    </main>

    <script>
        const form = document.getElementById('signedUploadForm');
        const input = document.getElementById('signedFileInput');
        const button = document.getElementById('validateBtn');
        const errorMsg = document.getElementById('errorMsg');

        button.addEventListener('click', function(e) {
            if (!input.files.length) {
                e.preventDefault();
                errorMsg.style.display = 'block';
                input.focus();
            } else {
                errorMsg.style.display = 'none';
                form.submit();
            }
        });
    </script>
</body>

</html>