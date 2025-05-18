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

// Statistiques globales
$stats = [];

// Nombre total d'étudiants
$query = "SELECT COUNT(*) as total FROM users WHERE role = 'student'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_students'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Nombre d'étudiants actifs
$query = "SELECT COUNT(*) as active FROM users WHERE role = 'student' AND is_active = 1";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['active_students'] = $stmt->fetch(PDO::FETCH_ASSOC)['active'];

// Nombre total de formations
$query = "SELECT COUNT(*) as total FROM courses";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_courses'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Formations les plus populaires
$query = "SELECT c.title, c.theme, COUNT(ce.id) as enrollment_count,
          (SELECT COUNT(*) FROM course_completions WHERE course_id = c.id) as completion_count
          FROM courses c
          LEFT JOIN course_enrollments ce ON c.id = ce.course_id
          GROUP BY c.id
          ORDER BY enrollment_count DESC
          LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$popular_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Taux de complétion par thème
$query = "SELECT c.theme,
          COUNT(DISTINCT ce.id) as total_enrollments,
          COUNT(DISTINCT cc.id) as total_completions,
          (COUNT(DISTINCT cc.id) * 100.0 / COUNT(DISTINCT ce.id)) as completion_rate
          FROM courses c
          LEFT JOIN course_enrollments ce ON c.id = ce.course_id
          LEFT JOIN course_completions cc ON c.id = cc.course_id
          GROUP BY c.theme
          HAVING total_enrollments > 0
          ORDER BY completion_rate DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$theme_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Activité récente (derniers 30 jours)
$query = "SELECT 
            (SELECT COUNT(*) FROM course_enrollments WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as new_enrollments,
            (SELECT COUNT(*) FROM course_completions WHERE completed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as new_completions,
            (SELECT COUNT(*) FROM users WHERE role = 'student' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as new_students";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_activity = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
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
        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 40px;
        }
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 40px;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f5f5f5;
        }
        .progress-bar {
            background-color: #f0f0f0;
            border-radius: 10px;
            height: 10px;
            overflow: hidden;
        }
        .progress-fill {
            background-color: var(--primary-color);
            height: 100%;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>
    <?php include '../includes/admin_navbar.php'; ?>

    <div class="admin-container">
        <h1>Statistiques Globales</h1>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_students']; ?></div>
                <div class="stat-label">Étudiants inscrits</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['active_students']; ?></div>
                <div class="stat-label">Étudiants actifs</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_courses']; ?></div>
                <div class="stat-label">Formations disponibles</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $recent_activity['new_students']; ?></div>
                <div class="stat-label">Nouveaux étudiants (30j)</div>
            </div>
        </div>

        <div class="chart-container">
            <h2>Formations les plus populaires</h2>
            <canvas id="popularCoursesChart"></canvas>
        </div>

        <div class="table-container">
            <h2>Taux de complétion par thème</h2>
            <table>
                <thead>
                    <tr>
                        <th>Thème</th>
                        <th>Inscriptions</th>
                        <th>Complétions</th>
                        <th>Taux de complétion</th>
                        <th>Progression</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($theme_stats as $theme): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($theme['theme']); ?></td>
                            <td><?php echo $theme['total_enrollments']; ?></td>
                            <td><?php echo $theme['total_completions']; ?></td>
                            <td><?php echo number_format($theme['completion_rate'], 1); ?>%</td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $theme['completion_rate']; ?>%"></div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-container">
            <h2>Activité récente (30 derniers jours)</h2>
            <table>
                <tr>
                    <td>Nouvelles inscriptions aux cours</td>
                    <td><?php echo $recent_activity['new_enrollments']; ?></td>
                </tr>
                <tr>
                    <td>Cours complétés</td>
                    <td><?php echo $recent_activity['new_completions']; ?></td>
                </tr>
                <tr>
                    <td>Nouveaux étudiants</td>
                    <td><?php echo $recent_activity['new_students']; ?></td>
                </tr>
            </table>
        </div>
    </div>

    <script>
        // Graphique des formations populaires
        const popularCoursesCtx = document.getElementById('popularCoursesChart').getContext('2d');
        new Chart(popularCoursesCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($popular_courses, 'title')); ?>,
                datasets: [{
                    label: 'Nombre d\'inscriptions',
                    data: <?php echo json_encode(array_column($popular_courses, 'enrollment_count')); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html> 