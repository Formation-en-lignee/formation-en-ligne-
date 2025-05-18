-- Création de la table departments
CREATE TABLE IF NOT EXISTS departments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertion des départements par défaut
INSERT INTO departments (name) VALUES
    ('Informatique'),
    ('Marketing'),
    ('Ressources Humaines'),
    ('Finance'),
    ('Communication'),
    ('Ventes'),
    ('Recherche et Développement'),
    ('Support Client'); 