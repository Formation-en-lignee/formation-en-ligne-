-- Script pour exécuter les fichiers SQL dans l'ordre spécifié
-- Date: 2024

-- Exécution de la base de données principale
SOURCE database.sql;

-- Exécution des fichiers de questions dans l'ordre
SOURCE Question2.sql;
SOURCE Question3.sql;
SOURCE Question4.sql;
SOURCE Question5.sql;
SOURCE Question6.sql;

-- Confirmation de l'exécution
SELECT 'Tous les fichiers SQL ont été exécutés avec succès.' as message; 