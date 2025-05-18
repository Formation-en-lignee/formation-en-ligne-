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

// Récupération des statistiques
// Étudiants
$query = "SELECT 
    COUNT(*) as total_students,
    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_students
    FROM users 
    WHERE role = 'student'";
$stmt = $db->prepare($query);
$stmt->execute();
$student_stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Formations
$query = "SELECT COUNT(*) as total_courses FROM courses";
$stmt = $db->prepare($query);
$stmt->execute();
$course_stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Derniers étudiants inscrits
$query = "SELECT firstname, lastname, email, created_at 
          FROM users 
          WHERE role = 'student' 
          ORDER BY created_at DESC 
          LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Formations les plus populaires
$query = "SELECT c.title, c.theme, 
          COUNT(ce.id) as enrollment_count,
          (SELECT COUNT(*) FROM course_completions cc WHERE cc.course_id = c.id) as completion_count
          FROM courses c
          LEFT JOIN course_enrollments ce ON c.id = ce.course_id
          GROUP BY c.id
          ORDER BY enrollment_count DESC
          LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$popular_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Messages de contact non lus
$query = "SELECT COUNT(*) as unread_count FROM contact_messages WHERE status = 'new'";
$stmt = $db->prepare($query);
$stmt->execute();
$unread_messages = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - E-learning Platform</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .stat-number {
            font-size: 2.5em;
            color: #2c3e50;
            margin: 10px 0;
        }
        .stat-label {
            color: #7f8c8d;
            font-size: 0.9em;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }
        .dashboard-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .action-button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: #3498db;
            color: white;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .action-button:hover {
            background-color: #2980b9;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8em;
        }
        .badge-success {
            background-color: #2ecc71;
            color: white;
        }
        .badge-warning {
            background-color: #f1c40f;
            color: white;
        }
        .chart-container {
            margin-top: 20px;
            height: 300px;
        }
    </style>
</head>
<body>
    <?php include '../includes/admin_navbar.php'; ?>

    <div class="dashboard-container">
        <div class="action-buttons">
            <a href="manage_users.php" class="action-button">
                <span>👥</span> Gérer les utilisateurs
            </a>
            <a href="manage_courses.php" class="action-button">
                <span>📚</span> Gérer les formations
            </a>
            <a href="messages.php" class="action-button">
                <span>📧</span> Messages 
                <?php if($unread_messages['unread_count'] > 0): ?>
                    <span class="badge badge-warning"><?php echo $unread_messages['unread_count']; ?></span>
                <?php endif; ?>
            </a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Étudiants</div>
                <div class="stat-number"><?php echo $student_stats['total_students']; ?></div>
                <div class="stat-info">
                    <?php echo $student_stats['active_students']; ?> actifs
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Formations</div>
                <div class="stat-number"><?php echo $course_stats['total_courses']; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Messages non lus</div>
                <div class="stat-number"><?php echo $unread_messages['unread_count']; ?></div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="main-content">
                <div class="dashboard-card">
                    <h2>Formations populaires</h2>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Formation</th>
                                <th>Thème</th>
                                <th>Inscrits</th>
                                <th>Complétions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($popular_courses as $course): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($course['title']); ?></td>
                                    <td><?php echo htmlspecialchars($course['theme']); ?></td>
                                    <td><?php echo $course['enrollment_count']; ?></td>
                                    <td><?php echo $course['completion_count']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="dashboard-card">
                    <h2>Statistiques d'inscription</h2>
                    <div class="chart-container">
                        <canvas id="enrollmentChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="sidebar">
                <div class="dashboard-card">
                    <h2>Derniers inscrits</h2>
                    <table class="table">
                        <tbody>
                            <?php foreach($recent_students as $student): ?>
                                <tr>
                                    <td>
                                        <?php echo htmlspecialchars($student['firstname'] . ' ' . $student['lastname']); ?>
                                        <br>
                                        <small><?php echo htmlspecialchars($student['email']); ?></small>
                                    </td>
                                    <td>
                                        <small>
                                            <?php echo date('d/m/Y', strtotime($student['created_at'])); ?>
                                        </small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="dashboard-card">
                    <h2>Actions rapides</h2>
                    <div style="display: grid; gap: 10px;">
                        <a href="add_course.php" class="action-button">
                            <span>➕</span> Nouvelle formation
                        </a>
                        <a href="reports.php" class="action-button">
                            <span>📊</span> Voir les rapports
                        </a>
                        <a href="settings.php" class="action-button">
                            <span>⚙️</span> Paramètres
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Graphique des inscriptions
        const ctx = document.getElementById('enrollmentChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'],
                datasets: [{
                    label: 'Inscriptions',
                    data: [12, 19, 3, 5, 2, 3],
                    borderColor: '#3498db',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
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