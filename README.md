# StalHub

Plateforme de gestion de demandes de stage/alternance pour étudiants et entreprises, avec authentification forte (OTP par email en dev via MailHog).

---

## 🚀 Table des matières

- [Technos & Architecture](#technos--architecture)  
- [Prérequis](#prérequis)  
- [Installation & Setup](#installation--setup)  
- [Configuration des variables d’environnement](#configuration-des-variables-denvironnement)  
- [Lancement de la stack Docker](#lancement-de-la-stack-docker)  
- [Migrations & Base de données](#migrations--base-de-données)  
- [Utilisateur de test](#utilisateur-de-test)  
- [Usage & Démo](#usage--démo)  
- [Structure du projet](#structure-du-projet)  
- [Workflow Git & Collaboration](#workflow-git--collaboration)  
- [Onboarding Guide (pour tout le monde)](#onboarding-guide-pour-tout-le-monde)  

---

## Technos & Architecture

- **Backend** : PHP 8.1-FPM, MVC minimal sans framework  
- **Base de données** : MariaDB (volume Docker persistant)  
- **Migrations** : Phinx  
- **OTP 2FA** : OTP par email via PHPMailer + MailHog en dev  
- **Serveur HTTP** : Nginx  
- **Conteneurs** : Docker Compose  
- **Tests unitaires** : PHPUnit (dossier `tests/`)  

---

## Prérequis

- Docker & Docker Compose  
- Git  
- Un accès SSH (ou HTTPS) à GitHub  

---

## Installation & Setup

1. **Cloner le repo**  
   ```bash
   git clone git@github.com:kayouba/stalhub.git
   cd stalhub
