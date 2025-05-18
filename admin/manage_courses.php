<?php
session_start();
require_once '../config/database.php';

// Vérifier si l'utilisateur est connecté et est un admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $course_id = $_POST['course_id'] ?? '';

    switch($action) {
        case 'create':
            $stmt = $db->prepare("INSERT INTO courses (title, description, theme, duration, image_url) VALUES (:title, :description, :theme, :duration, :image_url)");
            $stmt->execute([
                'title' => $_POST['title'],
                'description' => $_POST['description'],
                'theme' => $_POST['theme'],
                'duration' => $_POST['duration'],
                'image_url' => $_POST['image_url']
            ]);
            break;

        case 'update':
            $stmt = $db->prepare("UPDATE courses SET 
                title = :title,
                description = :description,
                theme = :theme,
                duration = :duration,
                image_url = :image_url
                WHERE id = :id");
            $stmt->execute([
                'title' => $_POST['title'],
                'description' => $_POST['description'],
                'theme' => $_POST['theme'],
                'duration' => $_POST['duration'],
                'image_url' => $_POST['image_url'],
                'id' => $course_id
            ]);
            break;

        case 'delete':
            $stmt = $db->prepare("DELETE FROM courses WHERE id = :id");
            $stmt->execute(['id' => $course_id]);
            break;
    }
    
    header('Location: manage_courses.php');
    exit();
}

// Récupération des formations
$query = "SELECT c.*, 
          (SELECT COUNT(*) FROM course_enrollments WHERE course_id = c.id) as enrollment_count
          FROM courses c 
          ORDER BY c.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération des thèmes uniques
$theme_query = "SELECT DISTINCT theme FROM courses WHERE theme IS NOT NULL ORDER BY theme";
$theme_stmt = $db->prepare($theme_query);
$theme_stmt->execute();
$themes = $theme_stmt->fetchAll(PDO::FETCH_COLUMN);

// Ajouter des thèmes par défaut si aucun thème n'existe
$default_themes = [
    'Développement Web',
    'Programmation',
    'Design',
    'Marketing Digital',
    'Business',
    'Langues',
    'Data Science',
    'Intelligence Artificielle',
    'Cybersécurité',
    'Gestion de Projet',
    'Soft Skills',
    'Productivité'
];

// Fusionner les thèmes existants avec les thèmes par défaut
$themes = array_unique(array_merge($themes, $default_themes));
sort($themes); // Trier les thèmes par ordre alphabétique
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Formations - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .course-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .course-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .course-image.placeholder {
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
        }
        .course-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        .course-title {
            margin: 0;
            color: var(--primary-color);
        }
        .course-theme {
            background-color: #f0f0f0;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
        }
        .course-stats {
            margin: 15px 0;
            color: #666;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .btn-add {
            margin-bottom: 20px;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
        }
        .close {
            float: right;
            cursor: pointer;
            font-size: 28px;
        }
        .theme-selection {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .theme-selection select {
            flex: 1;
        }
        .new-theme-input {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .new-theme-input input {
            flex: 1;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <?php include '../includes/admin_navbar.php'; ?>

    <div class="admin-container">
        <h1>Gestion des Formations</h1>
        
        <button class="btn btn-primary btn-add" onclick="openAddModal()">
            Ajouter une formation
        </button>

        <div class="courses-grid">
            <?php foreach($courses as $course): ?>
                <div class="course-card">
                    <?php if (!empty($course['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($course['image_url']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="course-image">
                    <?php else: ?>
                        <div class="course-image placeholder">
                            <span>Aucune image</span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="course-header">
                        <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                        <span class="course-theme"><?php echo htmlspecialchars($course['theme']); ?></span>
                    </div>

                    <p><?php echo htmlspecialchars($course['description']); ?></p>

                    <div class="course-stats">
                        <div>Durée: <?php echo $course['duration']; ?> minutes</div>
                        <div>Inscrits: <?php echo $course['enrollment_count']; ?> étudiants</div>
                    </div>

                    <div class="action-buttons">
                        <button class="btn btn-edit" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($course)); ?>)">
                            Modifier
                        </button>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette formation ?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                            <button type="submit" class="btn btn-delete">Supprimer</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Modal d'ajout/modification -->
    <div id="courseModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle">Ajouter une formation</h2>
            <form method="POST">
                <input type="hidden" name="action" id="form_action" value="create">
                <input type="hidden" name="course_id" id="edit_course_id">
                
                <div class="form-group">
                    <label for="title">Titre</label>
                    <input type="text" id="edit_title" name="title" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="edit_description" name="description" required></textarea>
                </div>

                <div class="form-group">
                    <label for="image_url">URL de l'image</label>
                    <input type="url" id="edit_image_url" name="image_url" placeholder="https://exemple.com/image.jpg">
                    <small class="form-text text-muted">Entrez l'URL d'une image pour illustrer la formation</small>
                </div>

                <div class="form-group">
                    <label for="theme">Thème</label>
                    <div class="theme-selection">
                        <select id="edit_theme" name="theme" required>
                            <option value="">Sélectionner un thème</option>
                            <?php foreach($themes as $theme): ?>
                                <option value="<?php echo htmlspecialchars($theme); ?>">
                                    <?php echo htmlspecialchars($theme); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="btn btn-secondary" onclick="toggleNewThemeInput()">+ Nouveau thème</button>
                    </div>
                </div>

                <div id="new_theme_container" style="display: none;" class="form-group">
                    <label for="new_theme">Nouveau thème</label>
                    <div class="new-theme-input">
                        <input type="text" id="new_theme" name="new_theme" placeholder="Entrez le nom du nouveau thème">
                        <button type="button" class="btn btn-secondary" onclick="cancelNewTheme()">Annuler</button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="duration">Durée (minutes)</label>
                    <input type="number" id="edit_duration" name="duration" required min="1">
                </div>

                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Ajouter une formation';
            document.getElementById('form_action').value = 'create';
            document.getElementById('courseModal').style.display = 'block';
            // Réinitialiser le formulaire
            document.getElementById('edit_title').value = '';
            document.getElementById('edit_description').value = '';
            document.getElementById('edit_theme').value = '';
            document.getElementById('edit_duration').value = '';
            document.getElementById('edit_image_url').value = '';
        }

        function openEditModal(course) {
            document.getElementById('modalTitle').textContent = 'Modifier la formation';
            document.getElementById('form_action').value = 'update';
            document.getElementById('edit_course_id').value = course.id;
            document.getElementById('edit_title').value = course.title;
            document.getElementById('edit_description').value = course.description;
            document.getElementById('edit_theme').value = course.theme;
            document.getElementById('edit_duration').value = course.duration;
            document.getElementById('edit_image_url').value = course.image_url || '';
            document.getElementById('courseModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('courseModal').style.display = 'none';
        }

        function toggleNewThemeInput() {
            const themeSelect = document.getElementById('edit_theme');
            const newThemeContainer = document.getElementById('new_theme_container');
            const newThemeInput = document.getElementById('new_theme');

            if (newThemeContainer.style.display === 'none') {
                newThemeContainer.style.display = 'block';
                themeSelect.disabled = true;
                newThemeInput.required = true;
                themeSelect.value = '';
            }
        }

        function cancelNewTheme() {
            const themeSelect = document.getElementById('edit_theme');
            const newThemeContainer = document.getElementById('new_theme_container');
            const newThemeInput = document.getElementById('new_theme');

            newThemeContainer.style.display = 'none';
            themeSelect.disabled = false;
            newThemeInput.required = false;
            newThemeInput.value = '';
        }

        // Modification du traitement du formulaire
        document.querySelector('form').addEventListener('submit', function(e) {
            const newThemeInput = document.getElementById('new_theme');
            const themeSelect = document.getElementById('edit_theme');

            if (newThemeInput.value) {
                themeSelect.disabled = false; // Réactiver le select pour l'envoi du formulaire
                themeSelect.value = newThemeInput.value;
            }
        });

        // Fermer la modal si on clique en dehors
        window.onclick = function(event) {
            if (event.target == document.getElementById('courseModal')) {
                closeModal();
            }
        }
    </script>
</body>
</html> 