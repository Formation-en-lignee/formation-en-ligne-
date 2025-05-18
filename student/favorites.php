<?php
session_start();
require_once '../config/database.php';

// Vérifier si l'utilisateur est connecté
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Traitement des actions (ajout/suppression des favoris)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'] ?? null;
    $action = $_POST['action'] ?? '';

    if ($course_id) {
        switch($action) {
            case 'add':
                try {
                    $stmt = $db->prepare("INSERT INTO favorites (user_id, course_id) VALUES (?, ?)");
                    $stmt->execute([$_SESSION['user_id'], $course_id]);
                    header('Location: favorites.php?success=added');
                } catch(PDOException $e) {
                    if ($e->getCode() == 23000) { // Code d'erreur pour duplicate entry
                        header('Location: favorites.php?error=already_exists');
                    } else {
                        header('Location: favorites.php?error=add_failed');
                    }
                }
                break;

            case 'remove':
                $stmt = $db->prepare("DELETE FROM favorites WHERE user_id = ? AND course_id = ?");
                if ($stmt->execute([$_SESSION['user_id'], $course_id])) {
                    header('Location: favorites.php?success=removed');
                } else {
                    header('Location: favorites.php?error=remove_failed');
                }
                break;
        }
        exit();
    }
}

// Récupération des formations favorites
$query = "SELECT c.*, 
          (SELECT COUNT(*) FROM course_enrollments WHERE course_id = c.id) as enrollment_count,
          (SELECT COUNT(*) FROM course_completions WHERE course_id = c.id) as completion_count
          FROM courses c
          INNER JOIN favorites f ON c.id = f.course_id
          WHERE f.user_id = ?
          ORDER BY c.title";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$favorite_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Messages de succès/erreur
$messages = [
    'success' => [
        'added' => 'Formation ajoutée aux favoris',
        'removed' => 'Formation retirée des favoris'
    ],
    'error' => [
        'already_exists' => 'Cette formation est déjà dans vos favoris',
        'add_failed' => 'Erreur lors de l\'ajout aux favoris',
        'remove_failed' => 'Erreur lors de la suppression des favoris'
    ]
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Favoris - E-learning Platform</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        :root {
            --primary-color: #3498db;
            --primary-dark: #2980b9;
            --secondary-color: #2ecc71;
            --text-color: #2c3e50;
            --light-gray: #f5f6fa;
            --border-radius: 12px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .favorites-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .favorites-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .favorites-header h1 {
            font-size: 2.5em;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .favorites-header p {
            color: #666;
            font-size: 1.1em;
        }

        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .course-card {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
        }

        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .course-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .course-content {
            padding: 25px;
        }

        .course-title {
            margin: 0 0 15px 0;
            color: var(--text-color);
            font-size: 1.4em;
            font-weight: 600;
            line-height: 1.3;
        }

        .course-theme {
            display: inline-block;
            padding: 6px 12px;
            background: var(--light-gray);
            border-radius: 20px;
            font-size: 0.9em;
            color: var(--text-color);
            font-weight: 500;
            margin-bottom: 15px;
        }

        .course-stats {
            display: flex;
            gap: 20px;
            margin: 15px 0;
            color: #666;
            font-size: 0.9em;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .course-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: 500;
            transition: all 0.3s ease;
            text-align: center;
        }

        .btn-view {
            background-color: var(--primary-color);
            color: white;
            flex: 1;
        }

        .btn-view:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-remove {
            background-color: #e74c3c;
            color: white;
            width: 40px;
            height: 40px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 1.2em;
        }

        .btn-remove:hover {
            background-color: #c0392b;
            transform: scale(1.1);
        }

        .empty-message {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .empty-message h2 {
            color: var(--text-color);
            margin-bottom: 1rem;
            font-size: 1.8em;
        }

        .empty-message p {
            color: #666;
            margin-bottom: 2rem;
        }

        .alert {
            padding: 15px 20px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        @media (max-width: 768px) {
            .courses-grid {
                grid-template-columns: 1fr;
            }

            .course-card {
                margin-bottom: 20px;
            }

            .favorites-header h1 {
                font-size: 2em;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/student_navbar.php'; ?>

    <div class="favorites-container">
        <div class="favorites-header">
            <h1>Mes Formations Favorites</h1>
            <p>Retrouvez ici toutes les formations que vous avez marquées comme favorites</p>
        </div>

        <?php
        if (isset($_GET['success']) && isset($messages['success'][$_GET['success']])) {
            echo '<div class="alert alert-success">✓ ' . htmlspecialchars($messages['success'][$_GET['success']]) . '</div>';
        }
        if (isset($_GET['error']) && isset($messages['error'][$_GET['error']])) {
            echo '<div class="alert alert-error">⚠ ' . htmlspecialchars($messages['error'][$_GET['error']]) . '</div>';
        }
        ?>

        <?php if (empty($favorite_courses)): ?>
            <div class="empty-message">
                <h2>Aucune formation favorite</h2>
                <p>Vous n'avez pas encore ajouté de formations à vos favoris.</p>
                <a href="available_courses.php" class="btn btn-view">Parcourir les formations</a>
            </div>
        <?php else: ?>
            <div class="courses-grid">
                <?php foreach($favorite_courses as $course): ?>
                    <div class="course-card">
                        <?php if (!empty($course['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($course['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($course['title']); ?>" 
                                 class="course-image">
                        <?php endif; ?>
                        
                        <div class="course-content">
                            <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                            <span class="course-theme"><?php echo htmlspecialchars($course['theme']); ?></span>
                            
                            <div class="course-stats">
                                <span class="stat-item">
                                    👥 <?php echo $course['enrollment_count']; ?> inscrits
                                </span>
                                <span class="stat-item">
                                    ✓ <?php echo $course['completion_count']; ?> complétions
                                </span>
                            </div>
                            
                            <div class="course-actions">
                                <a href="course.php?id=<?php echo $course['id']; ?>" class="btn btn-view">
                                    Voir le cours
                                </a>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                    <input type="hidden" name="action" value="remove">
                                    <button type="submit" class="btn btn-remove" title="Retirer des favoris">×</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 