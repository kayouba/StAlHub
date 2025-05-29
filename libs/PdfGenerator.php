<?php
namespace App\Lib;

use App\Model\RequestModel;
use App\Model\UserModel;
use App\Model\CompanyModel;
use DateTime;

/**
 * Génère un fichier PDF récapitulatif pour une demande de stage ou d'alternance,
 * à partir des données de la base, puis le chiffre avant sauvegarde.
 */
class PdfGenerator
{
    /**
     * Génère un PDF récapitulatif d'une demande, le chiffre, puis retourne son chemin.
     *
     * @param int    $requestId  ID de la demande à traiter.
     * @param string $uploadDir  Dossier de destination du fichier généré.
     * @return string|null       Chemin du fichier PDF chiffré, ou null en cas d’échec.
     */
    public static function generateFromDatabase(int $requestId, string $uploadDir): ?string
    {
        $requestModel = new RequestModel();
        $companyModel = new CompanyModel();
        $userModel = new UserModel();

        $request = $requestModel->findById($requestId);
        if (!$request) return null;

        $company = $companyModel->findById($request['company_id']);
        $user = $userModel->findById($request['student_id']);
        if (!$company || !$user) return null;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $foreignCountry = $company['foreign_country'] ?? '—';
        $isRemote = $request['is_remote'] ? 'Oui' : 'Non';
        $remoteDays = $request['remote_days_per_week'] ?? '—';

        //  Calcul de la durée
        $durationText = '';
        if (!empty($request['start_date']) && !empty($request['end_date'])) {
            try {
                $start = new DateTime($request['start_date']);
                $end = new DateTime($request['end_date']);
                $interval = $start->diff($end);
                $months = $interval->m + $interval->y * 12;
                $days = $interval->days;

                if ($request['contract_type'] === 'apprenticeship') {
                    $durationText = "Durée de l’alternance : {$months} mois";
                } else {
                    $durationText = "Durée du stage : {$days} jours";
                }
            } catch (\Exception $e) {
                // ignore le calcul si erreur
            }
        }

        //  HTML
        $html = "<h1>Récapitulatif de la demande</h1>";

        $html .= "<h2>Étudiant</h2>";
        if (!empty($user['last_name'])) $html .= "Nom : {$user['last_name']}<br>";
        if (!empty($user['first_name'])) $html .= "Prénom : {$user['first_name']}<br>";
        if (!empty($user['email'])) $html .= "Email : {$user['email']}<br>";
        if (!empty($user['student_number'])) $html .= "Numéro étudiant : {$user['student_number']}<br>";
        if (!empty($user['phone'])) $html .= "Téléphone : {$user['phone']}<br>";

        $html .= "<h2>Poste</h2>";
        if (!empty($request['contract_type'])) $html .= "Type de contrat : {$request['contract_type']}<br>";
        if (!empty($request['job_title'])) $html .= "Intitulé : {$request['job_title']}<br>";
        if (!empty($request['start_date'])) $html .= "Début : {$request['start_date']}<br>";
        if (!empty($request['end_date'])) $html .= "Fin : {$request['end_date']}<br>";
        if ($durationText) $html .= "$durationText<br>";
        if (!empty($request['weekly_hours'])) $html .= "Heures / semaine : {$request['weekly_hours']}<br>";
        if (!empty($request['salary'])) $html .= "Rémunération : {$request['salary']} € / mois<br>";
        $html .= "Télétravail : {$isRemote}<br>";
        $html .= "Jours télétravail : {$remoteDays}<br>";
        if (!empty($request['mission'])) $html .= "Missions :<br><pre>{$request['mission']}</pre>";

        $html .= "<h2>Entreprise</h2>";
        if (!empty($company['company_name'])) $html .= "Nom : {$company['company_name']}<br>";
        if (!empty($company['city'])) $html .= "Ville : {$company['city']}<br>";
        if (!empty($company['postal_code'])) $html .= "Code postal : {$company['postal_code']}<br>";
        if (!empty($company['country'])) $html .= "Pays : {$company['country']}<br>";
        if (!empty($company['country']) && $company['country'] !== 'France') {
            $html .= "Pays (si étranger) : {$foreignCountry}<br>";
        }
        if (!empty($company['supervisor_first_name']) || !empty($company['supervisor_last_name'])) {
            $html .= "Tuteur : {$company['supervisor_first_name']} {$company['supervisor_last_name']}<br>";
        }
        if (!empty($company['supervisor_email'])) $html .= "Email tuteur : {$company['supervisor_email']}<br>";
        if (!empty($company['supervisor_num'])) $html .= "Téléphone tuteur : {$company['supervisor_num']}<br>";
        if (!empty($company['supervisor_position'])) $html .= "Poste tuteur : {$company['supervisor_position']}<br>";

        //  PDF
        $pdf = new \TCPDF();
        $pdf->SetCreator('StalHub');
        $pdf->SetAuthor('StalHub');
        $pdf->SetTitle('Récapitulatif de la demande');
        $pdf->SetMargins(15, 15, 15);
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');
        // Chemins
        $filePath = rtrim($uploadDir, '/') . '/summary.pdf';
        $encryptedPath = $filePath . '.enc';

        // Sauvegarde du PDF non chiffré
        $pdf->Output($filePath, 'F');

        // Chiffrement
        if (\App\Lib\FileCrypto::encrypt($filePath, $encryptedPath)) {
            unlink($filePath); // Supprime le PDF original
            return $encryptedPath;
        } else {
            // Si le chiffrement échoue, retourne le PDF normal
            return $filePath;
        }

    }
}
