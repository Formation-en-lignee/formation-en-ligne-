<?php
session_start();
require_once '../config/database.php';

// Vérifier si l'utilisateur est connecté et est un étudiant
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../auth/login.php');
    exit();
}

// Vérifier si l'ID du cours est fourni
if(!isset($_GET['id'])) {
    header('Location: browse_courses.php');
    exit();
}

$course_id = $_GET['id'];
$database = new Database();
$db = $database->getConnection();

// Récupérer les informations du cours et vérifier l'inscription
$query = "SELECT c.*, 
          ce.completion_status,
          (SELECT COUNT(*) FROM course_materials WHERE course_id = c.id) as total_materials,
          (SELECT COUNT(*) FROM favorites WHERE course_id = c.id AND user_id = :user_id) as is_favorite
          FROM courses c
          LEFT JOIN course_enrollments ce ON c.id = ce.course_id AND ce.user_id = :user_id
          WHERE c.id = :course_id";

$stmt = $db->prepare($query);
$stmt->bindParam(':course_id', $course_id);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();

if($stmt->rowCount() === 0) {
    header('Location: browse_courses.php');
    exit();
}

$course = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer les supports de cours
$materials_query = "SELECT * FROM course_materials WHERE course_id = :course_id ORDER BY id";
$materials_stmt = $db->prepare($materials_query);
$materials_stmt->bindParam(':course_id', $course_id);
$materials_stmt->execute();
$materials = $materials_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['title']); ?> - E-learning Platform</title>
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

        .course-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .course-header {
            background: white;
            padding: 40px;
            border-radius: var(--border-radius);
            margin-bottom: 40px;
            box-shadow: var(--box-shadow);
            position: relative;
        }

        .course-title {
            font-size: 2.5em;
            color: var(--text-color);
            margin-bottom: 1rem;
            padding-right: 50px;
        }

        .course-description {
            color: #666;
            font-size: 1.1em;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .course-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin: 25px 0;
            color: #666;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1em;
            color: var(--text-color);
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .status-not-started { 
            background: var(--light-gray);
            color: #666;
        }

        .status-in-progress { 
            background: #e3f2fd; 
            color: #1976d2;
        }

        .status-completed { 
            background: #e8f5e9; 
            color: #2e7d32;
        }

        .course-progress {
            margin: 30px 0;
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .progress-title {
            font-size: 1.2em;
            color: var(--text-color);
            font-weight: 500;
        }

        .progress-percentage {
            font-size: 1.1em;
            color: var(--primary-color);
            font-weight: 600;
        }

        .progress-bar {
            width: 100%;
            height: 12px;
            background: var(--light-gray);
            border-radius: 6px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: var(--primary-color);
            transition: width 0.5s ease;
        }

        .materials-section {
            margin-top: 40px;
        }

        .materials-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .materials-title {
            font-size: 1.8em;
            color: var(--text-color);
        }

        .materials-tabs {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }

        .material-tab {
            padding: 10px 20px;
            border: none;
            background: var(--light-gray);
            border-radius: 20px;
            cursor: pointer;
            font-size: 1em;
            color: var(--text-color);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .material-tab.active {
            background: var(--primary-color);
            color: white;
        }

        .material-tab:hover {
            transform: translateY(-2px);
        }

        .materials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
        }

        .material-card {
            background: white;
            padding: 25px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .material-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .material-card.video {
            grid-column: 1 / -1;
        }

        .material-title {
            font-size: 1.2em;
            color: var(--text-color);
            margin-bottom: 10px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .material-type {
            display: inline-block;
            padding: 4px 12px;
            background: var(--light-gray);
            border-radius: 15px;
            font-size: 0.9em;
            color: #666;
            margin-bottom: 15px;
        }

        .video-container {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            border-radius: 8px;
            margin-top: 15px;
            background: #000;
        }

        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }

        .document-content {
            margin-top: 15px;
            padding: 20px;
            background: var(--light-gray);
            border-radius: 8px;
            font-size: 1em;
            line-height: 1.6;
            max-height: 300px;
            overflow-y: auto;
        }

        .document-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-download {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: var(--light-gray);
            color: var(--text-color);
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.9em;
            transition: all 0.3s ease;
        }

        .btn-download:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        .material-preview {
            margin-top: 15px;
            text-align: center;
        }

        .material-preview img {
            max-width: 100%;
            border-radius: 8px;
            box-shadow: var(--box-shadow);
        }

        .material-meta {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 15px;
            color: #666;
            font-size: 0.9em;
        }

        .material-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .favorite-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.5em;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .favorite-btn:hover {
            transform: scale(1.1);
        }

        .favorite-btn.active {
            color: #e74c3c;
        }

        .course-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn-action {
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--light-gray);
            color: var(--text-color);
        }

        .btn-secondary:hover {
            background: #e0e0e0;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .course-header {
                padding: 25px;
            }

            .course-title {
                font-size: 2em;
            }

            .course-meta {
                flex-direction: column;
                gap: 15px;
            }

            .materials-tabs {
                flex-wrap: wrap;
            }

            .material-tab {
                flex: 1;
                text-align: center;
                justify-content: center;
            }

            .materials-grid {
                grid-template-columns: 1fr;
            }

            .document-actions {
                flex-direction: column;
            }

            .btn-download {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/student_navbar.php'; ?>

    <div class="course-container">
        <div class="course-header">
            <button class="favorite-btn <?php echo $course['is_favorite'] ? 'active' : ''; ?>" 
                    onclick="toggleFavorite(this, <?php echo $course_id; ?>)"
                    title="Ajouter aux favoris">
                ❤
            </button>

            <h1 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h1>
            <p class="course-description"><?php echo htmlspecialchars($course['description']); ?></p>

            <div class="course-meta">
                <span class="meta-item">
                    📚 Thème: <?php echo htmlspecialchars($course['theme']); ?>
                </span>
                <span class="meta-item">
                    ⏱ Durée: <?php echo $course['duration']; ?> minutes
                </span>
                <span class="status-badge status-<?php echo $course['completion_status'] ?? 'not-started'; ?>">
                    <?php 
                    $icon = '';
                    switch($course['completion_status']) {
                        case 'completed':
                            $icon = '✓';
                            echo $icon . ' Terminé';
                            break;
                        case 'in_progress':
                            $icon = '▶';
                            echo $icon . ' En cours';
                            break;
                        default:
                            $icon = '○';
                            echo $icon . ' Non commencé';
                    }
                    ?>
                </span>
            </div>

            <?php if($course['completion_status']): ?>
                <div class="course-progress">
                    <div class="progress-header">
                        <h3 class="progress-title">Progression du cours</h3>
                        <span class="progress-percentage">
                            <?php 
                            $percentage = $course['completion_status'] === 'completed' ? 100 : 
                                ($course['completion_status'] === 'in_progress' ? 50 : 0);
                            echo $percentage . '%';
                            ?>
                        </span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $percentage; ?>%"></div>
                    </div>
                </div>

                <div class="course-actions">
                    <?php if($course['completion_status'] !== 'completed'): ?>
                        <button onclick="updateProgress(<?php echo $course_id; ?>, 'completed')" 
                                class="btn-action btn-primary">
                            ✓ Marquer comme terminé
                        </button>
                    <?php endif; ?>
                    <?php if($course['completion_status'] === 'not_started'): ?>
                        <button onclick="updateProgress(<?php echo $course_id; ?>, 'in_progress')" 
                                class="btn-action btn-secondary">
                            ▶ Commencer le cours
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="materials-section">
            <div class="materials-header">
                <h2 class="materials-title">Supports de cours</h2>
                <span class="meta-item">📑 <?php echo $course['total_materials']; ?> ressources</span>
            </div>

            <div class="materials-tabs">
                <button class="material-tab active" onclick="filterMaterials('all')">
                    🗂 Tous les supports
                </button>
                <button class="material-tab" onclick="filterMaterials('video')">
                    🎥 Vidéos
                </button>
                <button class="material-tab" onclick="filterMaterials('document')">
                    📄 Documents
                </button>
            </div>

            <div class="materials-grid">
                <?php foreach($materials as $material): ?>
                    <div class="material-card <?php echo $material['type']; ?>" data-type="<?php echo $material['type']; ?>">
                        <h3 class="material-title">
                            <?php 
                            $icon = $material['type'] === 'video' ? '🎥' : 
                                   ($material['type'] === 'document' ? '📄' : '📁');
                            echo $icon . ' ' . htmlspecialchars($material['title']); 
                            ?>
                        </h3>
                        
                        <div class="material-meta">
                            <span>
                                <?php 
                                switch($material['type']) {
                                    case 'video':
                                        echo '⏱ Durée: 10 min';
                                        break;
                                    case 'document':
                                        echo '📎 Format: PDF';
                                        break;
                                }
                                ?>
                            </span>
                            <span>📅 Ajouté le <?php echo date('d/m/Y', strtotime($material['created_at'] ?? 'now')); ?></span>
                        </div>

                        <?php if($material['type'] === 'video' && !empty($material['url'])): ?>
                            <div class="video-container">
                                <iframe src="<?php echo htmlspecialchars($material['url']); ?>" 
                                        allowfullscreen
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
                                </iframe>
                            </div>
                        <?php elseif($material['type'] === 'document'): ?>
                            <?php if(!empty($material['content'])): ?>
                                <div class="document-content">
                                    <?php echo nl2br(htmlspecialchars($material['content'])); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if(!empty($material['url'])): ?>
                                <div class="document-actions">
                                    <a href="<?php echo htmlspecialchars($material['url']); ?>" 
                                       class="btn-download" 
                                       download 
                                       target="_blank">
                                        ⬇️ Télécharger le document
                                    </a>
                                    <button class="btn-download" onclick="previewDocument('<?php echo htmlspecialchars($material['url']); ?>')">
                                        👁 Aperçu
                                    </button>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
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

                if (response.ok) {
                    button.classList.toggle('active');
                }
            } catch (error) {
                console.error('Erreur:', error);
            }
        }

        async function updateProgress(courseId, status) {
            try {
                const response = await fetch('../api/enrollments.php', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ 
                        course_id: courseId,
                        status: status 
                    })
                });

                if (response.ok) {
                    location.reload();
                }
            } catch (error) {
                console.error('Erreur:', error);
            }
        }

        function filterMaterials(type) {
            const tabs = document.querySelectorAll('.material-tab');
            const materials = document.querySelectorAll('.material-card');

            // Mettre à jour les onglets actifs
            tabs.forEach(tab => {
                tab.classList.remove('active');
                if(tab.textContent.toLowerCase().includes(type) || (type === 'all' && tab.textContent.includes('Tous'))) {
                    tab.classList.add('active');
                }
            });

            // Filtrer les supports
            materials.forEach(material => {
                if(type === 'all' || material.dataset.type === type) {
                    material.style.display = 'block';
                } else {
                    material.style.display = 'none';
                }
            });
        }

        function previewDocument(url) {
            // Vérifier si c'est une URL d'image
            if(url.match(/\.(jpg|jpeg|png|gif)$/i)) {
                const preview = document.createElement('div');
                preview.className = 'material-preview';
                preview.innerHTML = `<img src="${url}" alt="Aperçu du document">`;
                
                const existingPreview = event.target.parentElement.querySelector('.material-preview');
                if(existingPreview) {
                    existingPreview.remove();
                } else {
                    event.target.parentElement.appendChild(preview);
                }
            } else {
                // Ouvrir dans un nouvel onglet pour les autres types de documents
                window.open(url, '_blank');
            }
        }
    </script>
</body>
</html> 