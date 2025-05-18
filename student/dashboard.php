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

// Récupérer les formations inscrites
$enrolled_query = "SELECT c.*, ce.completion_status, ce.last_accessed
                   FROM courses c
                   JOIN course_enrollments ce ON c.id = ce.course_id
                   WHERE ce.user_id = :user_id
                   ORDER BY ce.last_accessed DESC
                   LIMIT 5";
$enrolled_stmt = $db->prepare($enrolled_query);
$enrolled_stmt->bindParam(':user_id', $_SESSION['user_id']);
$enrolled_stmt->execute();
$current_courses = $enrolled_stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les formations favorites
$favorites_query = "SELECT c.*
                   FROM courses c
                   JOIN favorites f ON c.id = f.course_id
                   WHERE f.user_id = :user_id
                   LIMIT 5";
$favorites_stmt = $db->prepare($favorites_query);
$favorites_stmt->bindParam(':user_id', $_SESSION['user_id']);
$favorites_stmt->execute();
$favorite_courses = $favorites_stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération des formations recommandées basées sur les thèmes suivis
$recommended_query = "SELECT DISTINCT c.*
                      FROM courses c
                      WHERE c.theme IN (
                          SELECT DISTINCT c2.theme
                          FROM courses c2
                          JOIN course_enrollments ce ON c2.id = ce.course_id
                          WHERE ce.user_id = :user_id
                      )
                      AND c.id NOT IN (
                          SELECT course_id FROM course_enrollments WHERE user_id = :user_id
                      )
                      LIMIT 5";
$recommended_stmt = $db->prepare($recommended_query);
$recommended_stmt->bindParam(':user_id', $_SESSION['user_id']);
$recommended_stmt->execute();
$recommended_courses = $recommended_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - E-learning Platform</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .dashboard-header {
            margin-bottom: 30px;
        }
        .welcome-message {
            font-size: 1.5em;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-number {
            font-size: 2em;
            color: #3498db;
            margin: 10px 0;
        }
        .courses-section {
            margin-bottom: 40px;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        .course-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .course-image {
            width: 100%;
            height: 160px;
            object-fit: cover;
        }
        .course-content {
            padding: 15px;
        }
        .course-title {
            font-size: 1.1em;
            margin: 0 0 10px 0;
            color: #2c3e50;
        }
        .course-theme {
            display: inline-block;
            padding: 3px 8px;
            background: #f0f0f0;
            border-radius: 12px;
            font-size: 0.9em;
            color: #666;
        }
        .course-progress {
            margin-top: 15px;
        }
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #eee;
            border-radius: 4px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: #2ecc71;
            transition: width 0.3s ease;
        }
        .view-all-btn {
            display: inline-block;
            padding: 8px 15px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .view-all-btn:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <?php include '../includes/student_navbar.php'; ?>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1 class="welcome-message">Bienvenue, <?php echo htmlspecialchars($_SESSION['firstname']); ?> !</h1>
            <p>Voici un aperçu de votre progression et de vos formations.</p>
        </div>

        <div class="stats-container">
            <div class="stat-card">
                <h3>Formations en cours</h3>
                <div class="stat-number"><?php echo count($current_courses); ?></div>
            </div>
            <div class="stat-card">
                <h3>Formations terminées</h3>
                <div class="stat-number">
                    <?php
                    $stmt = $db->prepare("SELECT COUNT(*) FROM course_enrollments WHERE user_id = ? AND completion_status = 'completed'");
                    $stmt->execute([$_SESSION['user_id']]);
                    echo $stmt->fetchColumn();
                    ?>
                </div>
            </div>
            <div class="stat-card">
                <h3>Formations favorites</h3>
                <div class="stat-number"><?php echo count($favorite_courses); ?></div>
            </div>
        </div>

        <!-- Formations en cours -->
        <div class="courses-section">
            <div class="section-header">
                <h2>Vos formations en cours</h2>
                <a href="available_courses.php" class="view-all-btn">Voir tout</a>
            </div>
            <div class="courses-grid">
                <?php foreach ($current_courses as $course): ?>
                    <div class="course-card">
                        <?php if (!empty($course['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($course['image_url']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="course-image">
                        <?php endif; ?>
                        <div class="course-content">
                            <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                            <span class="course-theme"><?php echo htmlspecialchars($course['theme']); ?></span>
                            <div class="course-progress">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php 
                                        echo $course['completion_status'] === 'completed' ? '100' : 
                                            ($course['completion_status'] === 'in_progress' ? '50' : '0'); 
                                    ?>%"></div>
                                </div>
                                <p>Statut: <?php 
                                    echo $course['completion_status'] === 'completed' ? 'Terminé' : 
                                        ($course['completion_status'] === 'in_progress' ? 'En cours' : 'Non commencé'); 
                                ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Formations favorites -->
        <div class="courses-section">
            <div class="section-header">
                <h2>Vos favoris</h2>
                <a href="favorites.php" class="view-all-btn">Voir tout</a>
            </div>
            <div class="courses-grid">
                <?php foreach ($favorite_courses as $course): ?>
                    <div class="course-card">
                        <?php if (!empty($course['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($course['image_url']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="course-image">
                        <?php endif; ?>
                        <div class="course-content">
                            <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                            <span class="course-theme"><?php echo htmlspecialchars($course['theme']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Formations recommandées -->
        <div class="courses-section">
            <div class="section-header">
                <h2>Recommandé pour vous</h2>
                <a href="available_courses.php" class="view-all-btn">Voir tout</a>
            </div>
            <div class="courses-grid">
                <?php foreach ($recommended_courses as $course): ?>
                    <div class="course-card">
                        <?php if (!empty($course['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($course['image_url']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="course-image">
                        <?php endif; ?>
                        <div class="course-content">
                            <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                            <span class="course-theme"><?php echo htmlspecialchars($course['theme']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html> 