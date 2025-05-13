# 🚀 StalHub — Setup Local Sans Docker (Mac & Windows)

Ce guide explique **pas à pas** comment configurer StalHub en local **sans Docker**, pour les utilisateurs **Windows (XAMPP)** et **macOS (MAMP)**. Il remplace l'ancien setup Docker. Chaque membre aura sa propre base MariaDB en local via phpMyAdmin.

---

## ✅ Prérequis

### Pour Windows

* Télécharger XAMPP : [https://www.apachefriends.org/fr/index.html](https://www.apachefriends.org/fr/index.html)

### Pour macOS

* Télécharger MAMP (version gratuite) : [https://www.mamp.info/en/](https://www.mamp.info/en/)

---

## 🧩 Objectif

* Avoir le projet fonctionnel en local avec PHP + MariaDB
* Utiliser phpMyAdmin pour gérer la base de données
* Chaque membre a **sa propre base locale**
* Le fichier `stalhub.sql` est la **référence partagée**, versionnée dans Git

---

## 📁 Structure du projet

```
stalhub/
├── api/
├── config/
│   ├── env.php
│   └── Database.php
├── sql/
│   └── stalhub.sql
├── .env.example
├── .gitignore
└── README.md
```

---

## ⚙️ Étapes détaillées (Mac & Windows)

### 1. 🧱 Installer XAMPP (Windows) ou MAMP (macOS)

#### ➤ Windows

1. Télécharger et installer XAMPP depuis [apachefriends.org](https://www.apachefriends.org/fr/index.html)
2. Lancer le **XAMPP Control Panel**
3. Cliquer sur **Start** pour : Apache & MySQL

#### ➤ macOS

1. Télécharger et installer MAMP depuis [mamp.info](https://www.mamp.info/en/)
2. Ouvrir **MAMP.app**
3. Cliquer sur **Start Servers** (Apache et MySQL)

---

### 2. 📂 Placer le projet dans le bon dossier

#### ➤ Windows (XAMPP)

* Copier le dossier `stalhub/` dans :

```
C:\xampp\htdocs\stalhub\
```

#### ➤ macOS (MAMP)

* Copier le dossier `stalhub/` dans :

```
/Applications/MAMP/htdocs/stalhub/
```

---

### 3. 🧠 Créer la base de données locale

1. Ouvrir **phpMyAdmin** dans le navigateur :

   * Windows : [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
   * macOS : [http://localhost:8888/phpmyadmin](http://localhost:8888/phpmyadmin)

2. Créer une base vide : `stalhub_dev`

   * Cliquer sur "Nouvelle base de données"
   * Nom : `stalhub_dev`, **format utf8mb4\_general\_ci**

3. Aller dans l'onglet **Importer**

   * Sélectionner le fichier `sql/stalhub.sql`
   * Cliquer sur **Exécuter**

---

### 4. 🔐 Configurer la connexion à la base (`.env`)

1. Copier le fichier `.env.example` en `.env`
2. Modifier le contenu selon votre environnement :

#### ➤ Windows (XAMPP)

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=stalhub_dev
DB_USER=root
DB_PASS=
```

#### ➤ macOS (MAMP)

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=stalhub_dev
DB_USER=root
DB_PASS=root
```

---

### 5. 🧪 Tester que tout fonctionne

1. Ouvrir dans le navigateur :

   * macOS : [http://localhost:8888/stalhub/api/test.php](http://localhost:8888/stalhub/api/test.php)
   * Windows : [http://localhost/stalhub/api/test.php](http://localhost/stalhub/api/test.php)

2. Vous devez voir :

```json
{"status":"OK","db":"connected"}
```

---

## 🔁 Garder une base commune synchronisée

* Le fichier `sql/stalhub.sql` contient la **structure et les données de référence**
* **Ne pas modifier la base à la main sans exporter ensuite**
* Pour mettre à jour la base :

  1. Modifier la base avec phpMyAdmin
  2. Exporter (`Exporter > SQL > stalhub.sql`)
  3. Remplacer le fichier dans `sql/`
  4. Commit et push

Chaque membre pourra ensuite réimporter ce fichier s’il veut repartir d’une base propre.

---

## ❗ Pour les anciens utilisateurs Docker

1. Supprimer les anciens fichiers Docker :

   * `docker-compose.yml`
   * `Dockerfile`
   * dossier `docker/`
2. Mettre à jour votre branche avec :

```bash
git pull origin main
```

3. Suivre les étapes du README ci-dessus

---

## ✅ Bonnes pratiques

* Ne **committez jamais** votre fichier `.env`
* Utilisez `stalhub.sql` pour garder une base commune
* Nommez bien vos fichiers d’API (`api/`) et testez-les avec Postman ou votre front

---

## 🙋‍♂️ Besoin d'aide ?

Force à vous
Contactez Kayou le bg avec un virement de 25 euros ou un membre de l’équipe technique en cas de blocage.
