-- Ajout de la colonne completion_status à la table course_enrollments
ALTER TABLE course_enrollments
ADD COLUMN completion_status ENUM('not_started', 'in_progress', 'completed') DEFAULT 'not_started' AFTER progress;

-- Mise à jour des statuts existants basée sur la progression
UPDATE course_enrollments
SET completion_status = 
    CASE 
        WHEN progress = 0 THEN 'not_started'
        WHEN progress = 100 THEN 'completed'
        ELSE 'in_progress'
    END; 