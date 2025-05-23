# 📚 Projet e-Learning Platform

## 🎯 Objectif du Projet

Développer une plateforme e-Learning complète permettant aux **utilisateurs** de :

        - Consulter un catalogue de formations,
        - S’inscrire à des cours,
        - Gérer leur profil utilisateur.

Et aux **administrateurs** de :

     - Gérer les cours (ajout, modification, suppression),
     - Suivre les inscriptions et les statistiques,
     - Gérer les utilisateurs, rôles, et départements.

## 🧾 Définition du Projet

Ce projet vise à fournir une solution e-learning moderne et responsive adaptée aux besoins d’une organisation souhaitant former ses employés ou proposer des formations en ligne. L’interface sera intuitive aussi bien pour les apprenants que pour les administrateurs.

## 🛠️ Technologies et Outils Utilisés

### Backend

    - PHP / Symfony (ou Laravel selon le choix)
    - MySQL / MariaDB (base de données relationnelle)
    - Doctrine ORM

### Frontend

    - HTML, CSS, JavaScript
    - Bootstrap / Tailwind CSS
    - Twig (si Symfony)

### Outils de Gestion de Projet

    - Jira (Gestion Agile : Epic > Sprint > Tâches)
    - Git & GitHub (ou GitLab) pour le versioning

### Sécurité

    - Protection contre XSS, CSRF, SQL Injection
    - Gestion sécurisée des sessions

## 📦 Architecture Fonctionnelle (Jira)

Le projet est divisé en 5 Epics principaux :

    ### Epic 1 : Catalogue des formations
    Permet aux utilisateurs de consulter et de s'inscrire à des formations.

    ### Epic 2 : Authentification & Profil
    Fonctionnalités d'inscription, connexion, et gestion de profil utilisateur.

    ### Epic 3 : Gestion des cours (Admin)
    Interface pour les administrateurs pour gérer les cours (CRUD complet).

    ### Epic 4 : Gestion des utilisateurs (Admin)
    Permet la gestion des comptes, rôles et départements.

    ### Epic 5 : Statistiques & Sécurité
    Dashboard administrateur, affichage des statistiques et gestion des aspects de sécurité.

## 📅 Méthodologie

    - **Méthode Agile Scrum**
    - Découpage par **Epics > Sprints > Tâches**
    - Suivi via Jira avec sprints de 1 ou 2 semaines

## 🧪 Fonctionnalités Clés à Tester

    - Navigation fluide entre les pages
    - Inscription et authentification sécurisées
    - Inscription à un cours
    - Interface d’administration fonctionnelle
    - Statistiques fiables et mises à jour

## 👥 Équipe & Rôles

    - **Développeurs Backend** : Gestion logique, base de données, sécurité
    - **Développeurs Frontend** : UI/UX, intégration, responsive
    - **Chef de projet / Scrum Master** : Suivi des sprints, gestion des tâches
    - **Testeurs QA** : Vérification des fonctionnalités et de la sécurité

---


