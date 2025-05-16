# Tbibi - Symfony (Plateforme Web)

Tbibi est une plateforme web de santé connectée développée avec **Symfony 6.4**. Elle facilite la communication entre patients et médecins via un forum, propose des services comme la synthèse vocale des réponses et un catalogue de produits médicaux.

---

## 1. Fonctionnalités

- **Forum de questions/réponses**
  - Questions médicales catégorisées par spécialité
  - Réponses validées par des médecins

- **Synthèse vocale**
  - Lecture audio des réponses textuelles via Web Speech API

- **Gestion des profils**
  - Médecins et patients avec fiches détaillées

- **Catalogue de produits médicaux**
  - Liste des articles disponibles à la commande

- **Système de commande**
  - Achats et suivi des commandes

- **Suivi médical**
  - Historique patient-médecin

- **Administration**
  - Gestion des utilisateurs
  - Modération du contenu
  - Statistiques

---

## 2. Prérequis

- PHP ≥ 8.1
- Symfony 6.4
- MySQL / MariaDB ≥ 10.4
- Composer
- Node.js et npm

---

## 3. Installation

### Cloner le dépôt

```bash
git clone https://github.com/votre-username/tbibi.git
cd tbibi

### Installer les dépendances
composer install
npm install
npm run build


 ###Configurer la base de données
DATABASE_URL="mysql://username:password@127.0.0.1:3306/tbibi2?serverVersion=10.4.32-MariaDB"

###php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load

 ### lancer le serveur
 symfony server:start

   ###Utilisation
Accès via navigateur : http://localhost:8000

Comptes de test
Rôle	Email	Mot de passe
Admin	admin@tbibi.com	password
Médecin	medecin@tbibi.com	password
Patient	patient@tbibi.com	password

Fonctionnalités par rôle
Patient :

Poser des questions

Lire les réponses (texte ou audio)

Commander des produits

Médecin :

Répondre aux questions

Gérer son profil

Administrateur :

Gérer utilisateurs et contenu

Visualiser les statistiques



5. Structure du projet
bash
Copier
Modifier
tbibi/
├── src/
│   ├── Controller/    # Contrôleurs Symfony
│   ├── Entity/        # Entités Doctrine (User, Question, Product, etc.)
│   ├── Form/          # Formulaires Symfony
│   ├── Repository/    # Requêtes personnalisées
│   └── Enum/          # Énumérations métier (ex : spécialités)
├── templates/         # Templates Twig (vues HTML)
├── public/            # Ressources front-end (CSS, JS, images)
├── migrations/        # Fichiers de migration Doctrine
├── assets/            # Code front-end (JS, SCSS compilé avec Webpack Encore)
├── .env               # Configuration de l’environnement




6. Technologies
Symfony 6.4 : Framework PHP moderne

Doctrine ORM : Gestion de la base de données

Twig : Moteur de templates

Bootstrap : Interface responsive

Webpack Encore : Compilation des assets

SendGrid : Envoi d’emails

Twilio : Envoi de SMS (optionnel)

Web Speech API : Synthèse vocale côté navigateur

9. Statut du projet
Projet en développement actif

Contributions : Bienvenues

Pour connaître les branches disponibles :
git branch -a

10. Contact
Pour toute question ou contribution :

Ouvrez une issue sur GitHub

Contactez : [siwar-chouanine]
