<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Récupération des thèmes
$theme_query = "SELECT DISTINCT theme FROM courses WHERE theme IS NOT NULL ORDER BY theme";
$theme_stmt = $db->prepare($theme_query);
$theme_stmt->execute();
$themes = $theme_stmt->fetchAll(PDO::FETCH_COLUMN);

// Filtrage par thème
$selected_theme = $_GET['theme'] ?? '';
$search_query = $_GET['search'] ?? '';

// Construction de la requête de base
$query = "SELECT c.*, 
          (SELECT COUNT(*) FROM course_enrollments WHERE course_id = c.id) as enrollment_count,
          CASE WHEN f.course_id IS NOT NULL THEN 1 ELSE 0 END as is_favorite
          FROM courses c
          LEFT JOIN favorites f ON c.id = f.course_id AND f.user_id = :user_id
          WHERE 1=1";
$params = [':user_id' => $_SESSION['user_id']];

if (!empty($selected_theme)) {
    $query .= " AND c.theme = :theme";
    $params[':theme'] = $selected_theme;
}

if (!empty($search_query)) {
    $query .= " AND (c.title LIKE :search OR c.description LIKE :search)";
    $params[':search'] = "%$search_query%";
}

$query .= " ORDER BY c.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement de l'inscription à un cours
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll'])) {
    $course_id = $_POST['course_id'];
    
    // Vérifier si l'utilisateur n'est pas déjà inscrit
    $check_stmt = $db->prepare("SELECT id FROM course_enrollments WHERE user_id = ? AND course_id = ?");
    $check_stmt->execute([$_SESSION['user_id'], $course_id]);
    
    if (!$check_stmt->fetch()) {
        $enroll_stmt = $db->prepare("INSERT INTO course_enrollments (user_id, course_id, completion_status) VALUES (?, ?, 'not_started')");
        $enroll_stmt->execute([$_SESSION['user_id'], $course_id]);
        header('Location: course.php?id=' . $course_id);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogue des formations - E-learning Platform</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .courses-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .filters {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            gap: 20px;
            align-items: center;
        }
        .filter-group {
            flex: 1;
        }
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .course-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: relative;
        }
        .course-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
        .course-content {
            padding: 20px;
        }
        .course-title {
            margin: 0 0 10px 0;
            color: #2c3e50;
            font-size: 1.2em;
        }
        .course-theme {
            display: inline-block;
            padding: 3px 8px;
            background: #f0f0f0;
            border-radius: 12px;
            font-size: 0.9em;
            color: #666;
            margin-bottom: 10px;
        }
        .course-description {
            color: #666;
            margin-bottom: 15px;
            font-size: 0.95em;
            line-height: 1.4;
        }
        .course-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        .enrollment-count {
            color: #666;
            font-size: 0.9em;
        }
        .btn-enroll {
            background: #3498db;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-enroll:hover {
            background: #2980b9;
        }
        .favorite-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255,255,255,0.9);
            border: none;
            padding: 8px;
            border-radius: 50%;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .favorite-btn:hover {
            transform: scale(1.1);
        }
        .favorite-btn.active {
            color: #e74c3c;
        }
    </style>
</head>
<body>
    <?php include '../includes/student_navbar.php'; ?>

    <div class="courses-container">
        <h1>Catalogue des formations</h1>

        <div class="filters">
            <div class="filter-group">
                <label for="theme">Filtrer par thème</label>
                <select id="theme" name="theme" onchange="applyFilters()">
                    <option value="">Tous les thèmes</option>
                    <?php foreach($themes as $theme): ?>
                        <option value="<?php echo htmlspecialchars($theme); ?>"
                            <?php echo $selected_theme === $theme ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($theme); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="search">Rechercher</label>
                <input type="text" id="search" name="search" 
                    value="<?php echo htmlspecialchars($search_query); ?>"
                    placeholder="Rechercher une formation..."
                    onkeyup="debounce(applyFilters, 500)()">
            </div>
        </div>

        <div class="courses-grid">
            <?php foreach($courses as $course): ?>
                <div class="course-card">
                    <?php if (!empty($course['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($course['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($course['title']); ?>" 
                             class="course-image">
                    <?php endif; ?>
                    
                    <button class="favorite-btn <?php echo $course['is_favorite'] ? 'active' : ''; ?>"
                            onclick="toggleFavorite(<?php echo $course['id']; ?>, this)">
                        ❤
                    </button>

                    <div class="course-content">
                        <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                        <span class="course-theme"><?php echo htmlspecialchars($course['theme']); ?></span>
                        <p class="course-description">
                            <?php echo htmlspecialchars(substr($course['description'], 0, 150)) . '...'; ?>
                        </p>
                        
                        <div class="course-meta">
                            <span class="enrollment-count">
                                <?php echo $course['enrollment_count']; ?> inscrits
                            </span>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                <button type="submit" name="enroll" class="btn-enroll">S'inscrire</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        let debounceTimer;
        
        function debounce(func, wait) {
            return function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(func, wait);
            }
        }

        function applyFilters() {
            const theme = document.getElementById('theme').value;
            const search = document.getElementById('search').value;
            
            let url = window.location.pathname + '?';
            if (theme) url += `theme=${encodeURIComponent(theme)}&`;
            if (search) url += `search=${encodeURIComponent(search)}`;
            
            window.location.href = url;
        }

        async function toggleFavorite(courseId, button) {
            try {
                const method = button.classList.contains('active') ? 'DELETE' : 'POST';
                const response = await fetch('../api/favorites.php', {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ course_id: courseId })
                });

                if (response.ok) {
                    button.classList.toggle('active');
                }
            } catch (error) {
                console.error('Erreur:', error);
            }
        }
    </script>
</body>
</html> 