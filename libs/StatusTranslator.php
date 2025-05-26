<?php
namespace App\Lib;

class StatusTranslator
{
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

    public static function contractType(string $type): string
    {
        return match (strtolower($type)) {
            'apprenticeship' => 'Apprentissage',
            'internship'     => 'Stage',
            default          => ucfirst($type),
        };
    }
}
