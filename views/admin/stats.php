<?php
$soumise        = $soumise ?? 0;
$validPeda      = $validPeda ?? 0;
$refusPeda      = $refusPeda ?? 0;

$attendSecret   = $attendSecret ?? 0;
$validSecret    = $validSecret ?? 0;
$refusSecret    = $refusSecret ?? 0;

$attendCFA      = $attendCFA ?? 0;
$validCFA       = $validCFA ?? 0;
$refusCFA       = $refusCFA ?? 0;

$validFinal     = $validFinal ?? 0;

$totalDemandes = $soumise + $validPeda + $refusPeda + $attendSecret + $validSecret + $refusSecret + $attendCFA + $validCFA + $refusCFA + $validFinal;

// Évite division par zéro
function percent($part, $total)
{
    return $total > 0 ? round(($part / $total) * 100, 1) : 0;
}

// On regroupe par état global
$totalValide = $validFinal;
$totalRefuse = $refusPeda + $refusSecret + $refusCFA;
$totalAttente = $soumise + $attendSecret + $attendCFA + $validPeda + $validSecret + $validCFA;

$pValide = percent($totalValide, $totalDemandes);
$pRefuse = percent($totalRefuse, $totalDemandes);
$pAttente = percent($totalAttente, $totalDemandes);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>StalHub - Statistiques</title>
    <link rel="stylesheet" href="/stalhub/public/css/admin-dashboard.css">
    <style>
        .stats-section {
            margin-bottom: 3rem;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: bold;
            color: #074f76;
            border-left: 4px solid #074f76;
            padding-left: 10px;
            margin-bottom: 1rem;
            background-color: #e8f1f9;
            padding: 0.5rem 1rem;
            border-radius: 5px;
        }

        .stats-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }

        .stats-table th {
            background-color: #074f76;
            color: white;
            padding: 1rem;
            text-align: left;
            font-size: 1rem;
        }

        .stats-table td {
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
        }

        .stats-table tr:hover {
            background-color: #f8f9fa;
        }

        .tag {
            display: inline-block;
            padding: 0.3rem 0.6rem;
            border-radius: 20px;
            font-size: 0.85rem;
            color: white;
            font-weight: bold;
        }

        .tag.blue {
            background-color: #78a9dd;
        }

        .tag.green {
            background-color: #58a66a;
        }

        .tag.red {
            background-color: #dc3545;
        }

        .tag.yellow {
            background-color: #efe33e;
            color: black;
        }

        .tag.orange {
            background-color: #dc8935;
        }

        .tag.pink {
            background-color: #dc35c9;
        }

        .stats-table td.align-right {
            text-align: right;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/../components/sidebar.php'; ?>

    <main class="admin-dashboard">
        <h1>📊 Statistiques</h1>
        <div class="stats-section">
            <div class="section-title">📈 Rapport synthétique</div>
            <table class="stats-table">
                <tr>
                    <td><span class="tag green">✅ Validées</span></td>
                    <td>Demandes validées </td>
                    <td class="align-right"><?= $pValide ?>%</td>
                </tr>
                <tr>
                    <td><span class="tag orange">🕒 En attente</span></td>
                    <td>Demandes en cours de traitement</td>
                    <td class="align-right"><?= $pAttente ?>%</td>
                </tr>
                <tr>
                    <td><span class="tag red">❌ Refusées</span></td>
                    <td>Demandes refusées à un ou plusieurs niveaux</td>
                    <td class="align-right"><?= $pRefuse ?>%</td>
                </tr>
            </table>
        </div>

        <div class="stats-section">
            <div class="section-title">📥 Soumission</div>
            <table class="stats-table">
                <tr>
                    <td><span class="tag blue">Soumise</span></td>
                    <td>Demandes soumises</td>
                    <td class="align-right"><?= $soumise ?></td>
                </tr>
            </table>
        </div>

        <div class="stats-section">
            <div class="section-title">🎓 Validation Pédagogique</div>
            <table class="stats-table">
                <tr>
                    <td><span class="tag green">Validées Pédago</span></td>
                    <td>Demandes validées par le référent pédagogique</td>
                    <td class="align-right"><?= $validPeda ?></td>
                </tr>
                <tr>
                    <td><span class="tag red">Refusées Pédago</span></td>
                    <td>Demandes refusées par le référent pédagogique</td>
                    <td class="align-right"><?= $refusPeda ?></td>
                </tr>
            </table>
        </div>

        <div class="stats-section">
            <div class="section-title">📑 Secrétariat</div>
            <table class="stats-table">
                <tr>
                    <td><span class="tag orange">En attente Secrétariat</span></td>
                    <td>Demandes en attente au secrétariat</td>
                    <td class="align-right"><?= $attendSecret ?></td>
                </tr>
                <tr>
                    <td><span class="tag green">Validées Secrétariat</span></td>
                    <td>Demandes validées par le secrétariat</td>
                    <td class="align-right"><?= $validSecret ?></td>
                </tr>
                <tr>
                    <td><span class="tag red">Refusées Secrétariat</span></td>
                    <td>Demandes refusées par le secrétariat</td>
                    <td class="align-right"><?= $refusSecret ?></td>
                </tr>
            </table>
        </div>

        <div class="stats-section">
            <div class="section-title">🏫 CFA</div>
            <table class="stats-table">
                <tr>
                    <td><span class="tag orange">En attente CFA</span></td>
                    <td>Demandes en attente au CFA</td>
                    <td class="align-right"><?= $attendCFA ?></td>
                </tr>
                <tr>
                    <td><span class="tag green">Validées CFA</span></td>
                    <td>Demandes validées par le CFA</td>
                    <td class="align-right"><?= $validCFA ?></td>
                </tr>
                <tr>
                    <td><span class="tag red">Refusées CFA</span></td>
                    <td>Demandes refusées par le CFA</td>
                    <td class="align-right"><?= $refusCFA ?></td>
                </tr>
            </table>
        </div>

        <div class="stats-section">
            <div class="section-title">✅ Validation Finale</div>
            <table class="stats-table">
                <tr>
                    <td><span class="tag green">Finalisées</span></td>
                    <td>Demandes validées à tous les niveaux</td>
                    <td class="align-right"><?= $validFinal ?></td>
                </tr>
            </table>
        </div>
    </main>
</body>

</html>