ALTER TABLE courses
ADD COLUMN image_url VARCHAR(255) DEFAULT NULL;

-- Ajout d'images par défaut pour les cours existants (optionnel)
UPDATE courses
SET image_url = CASE
    WHEN theme = 'Développement Web' THEN 'https://images.unsplash.com/photo-1498050108023-c5249f4df085'
    WHEN theme = 'Design' THEN 'https://images.unsplash.com/photo-1561070791-2526d30994b5'
    WHEN theme = 'Marketing Digital' THEN 'https://images.unsplash.com/photo-1460925895917-afdab827c52f'
    WHEN theme = 'Business' THEN 'https://images.unsplash.com/photo-1507679799987-c73779587ccf'
    WHEN theme = 'Langues' THEN 'https://images.unsplash.com/photo-1546410531-bb4caa6b424d'
    ELSE 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3'
END
WHERE image_url IS NULL; 