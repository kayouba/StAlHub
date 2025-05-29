<?php
namespace App\Lib;
/**
 * Traduit des statuts techniques en libellés lisibles pour l'utilisateur.
 */

class StatusTranslator
{
    /**
     * Traduit un code de statut en libellé utilisateur.
     *
     * @param string $status Code de statut en base (ex: 'VALID_PEDAGO', 'BROUILLON', etc.)
     * @return string Libellé lisible (ex: 'Validée par référent pédagogique')
     */
    public static function translate(string $status): string
    {
        $statusMap = [
            'BROUILLON' => 'Brouillon',
            'SOUMISE' => 'Soumise',
            'VALID_PEDAGO' => 'Validée par référent pédagogique',
            'REFUSEE_PEDAGO' => 'Refusée par référent pédagogique',
            'EN_ATTENTE_SIGNATURE_ENT' => 'En attente de signature entreprise',
            'SIGNEE_PAR_ENTREPRISE' => 'Signée par l’entreprise',
            'EN_ATTENTE_CFA' => 'En attente CFA',
            'VALID_CFA' => 'Validée par le CFA',
            'REFUSEE_CFA' => 'Refusée par le CFA',
            'EN_ATTENTE_SECRETAIRE' => 'En attente du secrétariat',
            'VALID_SECRETAIRE' => 'Validée par le secrétariat',
            'REFUSEE_SECRETAIRE' => 'Refusée par le secrétariat',
            'EN_ATTENTE_DIRECTION' => 'En attente de la direction',
            'VALID_DIRECTION' => 'Validée par la direction',
            'REFUSEE_DIRECTION' => 'Refusée par la direction',
            'VALIDE' => 'Demande validée',
            'SOUTENANCE_PLANIFIEE' => 'Soutenance planifiée',
            'ANNULEE' => 'Annulée',
            'EXPIREE' => 'Expirée'
        ];

        return $statusMap[strtoupper($status)] ?? ucfirst(strtolower($status));
    }

    /**
     * Traduit un type de contrat technique en libellé utilisateur.
     *
     * @param string $type Type de contrat (ex: 'apprenticeship', 'internship')
     * @return string Libellé lisible (ex: 'Apprentissage', 'Stage')
     */
    public static function contractType(string $type): string
    {
        return match (strtolower($type)) {
            'apprenticeship' => 'Apprentissage',
            'internship'     => 'Stage',
            default          => ucfirst($type),
        };
    }
}
