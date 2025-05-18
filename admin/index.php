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

// Récupérer quelques statistiques rapides
$stats = [];

// Nombre total d'étudiants
$query = "SELECT COUNT(*) as total FROM users WHERE role = 'student'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_students'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Nombre de formations
$query = "SELECT COUNT(*) as total FROM courses";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_courses'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Inscriptions aujourd'hui
$query = "SELECT COUNT(*) as total FROM course_enrollments WHERE DATE(created_at) = CURRENT_DATE()";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['today_enrollments'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Activité récente
$query = "SELECT 
    (SELECT COUNT(*) FROM users WHERE role = 'student' AND DATE(created_at) = CURRENT_DATE()) as new_students,
    (SELECT COUNT(*) FROM course_completions WHERE DATE(completed_at) = CURRENT_DATE()) as completed_today";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_activity = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Admin - E-learning Platform</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .welcome-section {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 40px;
        }
        .stats-grid {
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
            color: var(--primary-color);
            margin: 10px 0;
        }
        .stat-label {
            color: #666;
            font-size: 0.9em;
        }
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        .action-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }
        .action-card:hover {
            transform: translateY(-5px);
        }
        .action-icon {
            font-size: 2em;
            margin-bottom: 10px;
        }
        .action-link {
            text-decoration: none;
            color: inherit;
        }
    </style>
</head>
<body>
    <?php include '../includes/admin_navbar.php'; ?>

    <div class="admin-container">
        <div class="welcome-section">
            <h1>Bienvenue dans le panneau d'administration</h1>
            <p>Gérez vos utilisateurs, formations et consultez les statistiques de la plateforme.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_students']; ?></div>
                <div class="stat-label">Étudiants inscrits</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_courses']; ?></div>
                <div class="stat-label">Formations disponibles</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['today_enrollments']; ?></div>
                <div class="stat-label">Inscriptions aujourd'hui</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $recent_activity['completed_today']; ?></div>
                <div class="stat-label">Cours complétés aujourd'hui</div>
            </div>
        </div>

        <h2>Actions rapides</h2>
        <div class="quick-actions">
            <a href="manage_users.php" class="action-link">
                <div class="action-card">
                    <div class="action-icon">👥</div>
                    <h3>Gestion des utilisateurs</h3>
                    <p>Gérer les étudiants et leurs accès</p>
                </div>
            </a>
            <a href="manage_courses.php" class="action-link">
                <div class="action-card">
                    <div class="action-icon">📚</div>
                    <h3>Gestion des formations</h3>
                    <p>Ajouter ou modifier des formations</p>
                </div>
            </a>
            <a href="statistics.php" class="action-link">
                <div class="action-card">
                    <div class="action-icon">📊</div>
                    <h3>Statistiques</h3>
                    <p>Consulter les statistiques détaillées</p>
                </div>
            </a>
        </div>
    </div>
</body>
</html> 