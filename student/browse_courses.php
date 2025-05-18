<?php
session_start();
require_once '../config/database.php';

// Vérifier si l'utilisateur est connecté et est un étudiant
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Paramètres de recherche et filtrage
$search = $_GET['search'] ?? '';
$theme = $_GET['theme'] ?? '';
$sort = $_GET['sort'] ?? 'title';

// Construction de la requête
$query = "SELECT c.*, 
          (SELECT COUNT(*) FROM course_enrollments WHERE course_id = c.id) as enrollment_count,
          (SELECT COUNT(*) FROM favorites WHERE course_id = c.id) as favorite_count,
          (SELECT COUNT(*) FROM course_enrollments WHERE course_id = c.id AND user_id = :user_id) as is_enrolled,
          (SELECT COUNT(*) FROM favorites WHERE course_id = c.id AND user_id = :user_id) as is_favorite
          FROM courses c
          WHERE 1=1";

if(!empty($search)) {
    $query .= " AND (c.title LIKE :search OR c.description LIKE :search)";
}

if(!empty($theme)) {
    $query .= " AND c.theme = :theme";
}

switch($sort) {
    case 'popular':
        $query .= " ORDER BY enrollment_count DESC";
        break;
    case 'newest':
        $query .= " ORDER BY c.created_at DESC";
        break;
    default:
        $query .= " ORDER BY c.title ASC";
}

$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);

if(!empty($search)) {
    $searchParam = "%$search%";
    $stmt->bindParam(':search', $searchParam);
}

if(!empty($theme)) {
    $stmt->bindParam(':theme', $theme);
}

$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les thèmes uniques
$theme_query = "SELECT DISTINCT theme FROM courses WHERE theme IS NOT NULL ORDER BY theme";
$theme_stmt = $db->prepare($theme_query);
$theme_stmt->execute();
$themes = $theme_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parcourir les formations - E-learning Platform</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .browse-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .filters {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .filters form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            align-items: end;
        }

        .course-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }

        .course-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: relative;
        }

        .course-card h3 {
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .course-meta {
            display: flex;
            justify-content: space-between;
            margin: 15px 0;
            color: #666;
            font-size: 0.9em;
        }

        .favorite-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.5em;
            color: #ccc;
        }

        .favorite-btn.active {
            color: #ff4081;
        }

        .course-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }

        .enrollment-count {
            color: #666;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <a href="../index.php" class="logo">E-Learning Platform</a>
            <div class="nav-links">
                <a href="dashboard.php" class="btn">Dashboard</a>
                <a href="profile.php" class="btn">Mon Profil</a>
                <a href="../auth/logout.php" class="btn">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="browse-container">
        <div class="filters">
            <form method="GET" action="">
                <div class="form-group">
                    <label for="search">Rechercher</label>
                    <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Titre ou description...">
                </div>

                <div class="form-group">
                    <label for="theme">Thème</label>
                    <select id="theme" name="theme">
                        <option value="">Tous les thèmes</option>
                        <?php foreach($themes as $t): ?>
                            <option value="<?php echo htmlspecialchars($t); ?>" <?php echo $t === $theme ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($t); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="sort">Trier par</label>
                    <select id="sort" name="sort">
                        <option value="title" <?php echo $sort === 'title' ? 'selected' : ''; ?>>Titre</option>
                        <option value="popular" <?php echo $sort === 'popular' ? 'selected' : ''; ?>>Popularité</option>
                        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Plus récent</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Filtrer</button>
            </form>
        </div>

        <div class="course-grid">
            <?php foreach($courses as $course): ?>
                <div class="course-card">
                    <button class="favorite-btn <?php echo $course['is_favorite'] ? 'active' : ''; ?>" 
                            data-id="<?php echo $course['id']; ?>"
                            onclick="toggleFavorite(this, <?php echo $course['id']; ?>)">
                        ♥
                    </button>

                    <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                    <p><?php echo htmlspecialchars($course['description']); ?></p>

                    <div class="course-meta">
                        <span>Thème: <?php echo htmlspecialchars($course['theme']); ?></span>
                        <span>Durée: <?php echo $course['duration']; ?> min</span>
                    </div>

                    <div class="course-actions">
                        <span class="enrollment-count">
                            <?php echo $course['enrollment_count']; ?> inscrits
                        </span>
                        <?php if($course['is_enrolled']): ?>
                            <a href="course.php?id=<?php echo $course['id']; ?>" class="btn btn-primary">Continuer</a>
                        <?php else: ?>
                            <button class="btn btn-primary" onclick="enrollCourse(<?php echo $course['id']; ?>)">
                                S'inscrire
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        async function toggleFavorite(button, courseId) {
            try {
                const method = button.classList.contains('active') ? 'DELETE' : 'POST';
                const response = await fetch('../api/favorites.php', {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ course_id: courseId })
                });

                if(response.ok) {
                    button.classList.toggle('active');
                }
            } catch(error) {
                console.error('Erreur:', error);
            }
        }

        async function enrollCourse(courseId) {
            try {
                const response = await fetch('../api/enrollments.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ course_id: courseId })
                });

                if(response.ok) {
                    window.location.href = `course.php?id=${courseId}`;
                }
            } catch(error) {
                console.error('Erreur:', error);
            }
        }
    </script>
</body>
</html> 