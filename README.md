# Système de Réservation - Lunastra

## Description

Ce projet permet de gérer les réservations du site Lunastra - Albert Einstein. Le système permet aux utilisateurs de réserver des places, de les modifier, de les supprimer et de recevoir une confirmation par email. Les réservations sont stockées dans une base de données MySQL et un fichier JSON pour la synchronisation.

## Fonctionnalités

- **Gestion des réservations** : Ajout, mise à jour, suppression et récupération des réservations.
- **Envoi d'emails** : Envoi d'une confirmation de réservation par email à l'utilisateur.
- **Base de données** : Utilisation de MySQL pour stocker les réservations et synchronisation avec un fichier JSON.
- **Interfaces API** : Requête API en POST pour ajouter, mettre à jour, supprimer des réservations.

## Prérequis

- PHP 7.4 ou supérieur
- Serveur MySQL
- Serveur web (Apache, Nginx, etc.)

## Installation

1. **Clonez le dépôt** :

   ```bash
   git clone https://github.com/al3xghm/lunastra_api.git
   ```

2. **Configuration de la base de données** :

   Créez une base de données MySQL et ajoutez les informations dans le fichier `config.php`.

   Exemple de contenu pour `config.php` :

   ```php
   return [
       'db' => [
           'host' => 'localhost',
           'dbname' => 'lunastra_api',
           'username' => 'root',
           'password' => 'root',
       ],
       'secret_key' => 'luneinstein'
   ];
   ```

3. **Création de la table `users`** :

   Exécutez la commande SQL suivante pour créer la table `users` et insérer un utilisateur admin avec un mot de passe haché :

   ```sql
   CREATE TABLE `users` (
     `id` int(11) NOT NULL AUTO_INCREMENT,
     `username` varchar(50) NOT NULL,
     `password` varchar(255) NOT NULL,
     PRIMARY KEY (`id`)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

   -- Insérer un utilisateur admin
   INSERT INTO `users` (`username`, `password`)
   VALUES ('admin', '$2y$10$RkUeNiQFnUpRQa9uQ/s/CQ31mM1wXwB2IYrH6KGBVXywrKc5Xze46'); -- Le mot de passe est 'admin123' haché
   ```

4. **Création de la table des réservations** :

   Lors de la première exécution du projet, la table des réservations sera automatiquement créée dans la base de données grâce à la méthode `createTableIfNotExists()` dans le modèle `ReservationModel`.

5. **Démarrage du serveur** :

   Assurez-vous que le serveur web est correctement configuré et que le fichier `index.php` est bien défini comme point d'entrée de votre application.

6. **Tester l'API** :

   Vous pouvez tester l'API en envoyant des requêtes POST pour ajouter, mettre à jour ou supprimer des réservations. Utilisez un outil comme Postman pour envoyer les requêtes et vérifier les réponses.

## Structure du projet

Voici comment est organisé le projet :

```
.
├── config.php          # Configuration de la base de données
├── index.php       # Point d'entrée principal de l'application
├── data/
│   └── data.json       # Fichier de sauvegarde des réservations (optionnel)
├── models/
│   └── Model.php  # Modèle pour la gestion des réservations
├── controllers/
│   └── Controller.php  # Contrôleur principal pour gérer les requêtes API
└── README.md           # Documentation du projet
```

## API

### Récupérer les réservations

**GET** `/reservations`

Renvoie une liste de toutes les réservations.

### Ajouter une réservation

**POST** `/reservations`

Exemple de JSON :

```json
{
    "prenom": "John",
    "nom": "Doe",
    "email": "john.doe@example.com",
    "date": "2025-03-30",
    "horaire": "14:00:00",
    "amount": 3
}
```

### Mettre à jour une réservation

**POST** `/reservations`

Exemple de JSON :

```json
{
    "id": 1,
    "action": "update",
    "updatedValues": {
        "horaire": "15:00:00",
        "amount": 2
    }
}
```

### Supprimer une réservation

**POST** `/reservations`

Exemple de JSON :

```json
{
    "id": 1,
    "action": "delete"
}
```

## Sécurisation des requêtes

L'application utilise une `secret_key` pour sécuriser l'accès aux données. Assurez-vous que cette clé est définie et conservée secrète dans le fichier `config.php`.

## Envoi d'email

L'application envoie une confirmation de réservation par email à l'utilisateur. Vous devez avoir un serveur SMTP configuré.

## Synchronisation entre JSON et Base de données

- **JSON vers MySQL** : Si des réservations sont présentes dans le fichier JSON mais pas dans la base de données, elles seront ajoutées dans cette dernière.
- **MySQL vers JSON** : Les données de la base de données sont exportées dans le fichier JSON.

