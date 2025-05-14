# 📦 StalHub - Installation Complète et Fonctionnelle (Mac & Windows)

Bienvenue dans le projet **StalHub**, une application PHP MVC avec système d'authentification + OTP et envoi d'e-mail.  
Ce guide est **clairement détaillé** pour permettre à **tous les membres de l’équipe (débutants compris)** de tout installer, lancer et tester **sans Docker**, sur **Mac ou Windows**, avec MAMP et MailHog.

---

## ✅ Prérequis

### Pour tous :
- PHP ≥ 8.1
- Composer (https://getcomposer.org/)
- Git
- Un éditeur de code (ex : VS Code)

### Pour le serveur local :
- MAMP (Mac & Windows) — https://www.mamp.info/en/

---

## 📁 Arborescence attendue

```
stalhub/
├── public/
│   └── index.php
├── controllers/
│   └── AuthController.php, OTPController.php, ...
├── views/
│   ├── auth/
│   │   └── login.php, otp.php
│   ├── dashboard/
│   └── layouts/
│       └── default.php
├── Router.php
├── View.php
├── BaseController.php
├── LogoutController.php
├── config/
│   └── routes.php
├── composer.json
└── vendor/
```

---

## 🚀 Étapes d'installation

### 1. Cloner le projet

```bash
git clone https://github.com/kayouba/stalhub
cd stalhub
```

### 2. Installer les dépendances PHP

```bash
composer install
```

---

## 💾 Configuration de la base de données

1. Lancer MAMP.
2. Accéder à `http://localhost/phpMyAdmin`.
3. Créer une base de données nommée : `stalhub_dev`
4. Importer `stalhub.sql` si disponible (ou créer tables `users`, `otp_codes`).

---

## 📬 Installation de MailHog

### Sur **Mac** :

```bash
brew install mailhog
```

### Sur **Windows** :

1. Télécharger sur https://github.com/mailhog/MailHog/releases
2. Extraire et lancer `MailHog.exe`

### Lancer MailHog

```bash
mailhog
```

Interface : [http://localhost:8025](http://localhost:8025)

---

## 📤 PHPMailer : Envoi OTP par e-mail

- PHPMailer est utilisé au lieu de `mail()` pour fiabilité.
- SMTP configuré sur `localhost:1025`
- Pas besoin d’authentification ou TLS.

---

## ⚙️ Configuration du serveur Apache (MAMP)

1. Dossier racine du serveur : `stalhub/public`
2. Lancer les serveurs Apache + MySQL dans MAMP

### Accès au site :

```
http://localhost:8888/login
```

---

## 🔐 Routes principales

| URL                | Contrôleur                         |
|--------------------|------------------------------------|
| `/login`           | `AuthController::showLoginForm()`  |
| `/login/post`      | `AuthController::login()`          |
| `/otp`             | `OTPController::show()`            |
| `/otp/verify`      | `OTPController::verify()`          |
| `/dashboard`       | `DashboardController::index()`     |
| `/logout`          | `LogoutController::logout()`       |

---

## 🐞 Problèmes rencontrés (et résolus)

| Problème                         | Solution                           |
|----------------------------------|------------------------------------|
| `404` sur /login/post            | Route oubliée ou formulaire mal configuré |
| `mail()` ne fonctionne pas       | Utilisation de **PHPMailer** via SMTP |
| Mail non reçu dans MailHog       | Mauvais port SMTP dans php.ini     |
| `Class not found`                | Correction autoload + namespace    |
| Vue non trouvée (`View.php`)     | Correction du chemin d'accès       |

---

## 🧪 Test de l'application

1. Accéder à `/login`
2. Se connecter avec un utilisateur valide
3. Vérifier le mail dans MailHog (`localhost:8025`)
4. Saisir le code OTP
5. Redirection vers `/dashboard`

---

## 📌 Commit & Push Git

```bash
git add .
git commit -m "✅ Setup complet StalHub (sans Docker, avec PHPMailer & MailHog)"
git push origin main
```

---

## 🙏 Bon courage ! Vous avez tout ce qu’il faut 🚀
