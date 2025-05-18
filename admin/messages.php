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
    $message_id = $_POST['message_id'] ?? '';

    switch($action) {
        case 'mark_read':
            $stmt = $db->prepare("UPDATE contact_messages SET status = 'read' WHERE id = :id");
            $stmt->execute(['id' => $message_id]);
            break;

        case 'mark_replied':
            $stmt = $db->prepare("UPDATE contact_messages SET status = 'replied' WHERE id = :id");
            $stmt->execute(['id' => $message_id]);
            break;

        case 'delete':
            $stmt = $db->prepare("DELETE FROM contact_messages WHERE id = :id");
            $stmt->execute(['id' => $message_id]);
            break;
    }
    
    header('Location: messages.php');
    exit();
}

// Récupération des messages
$query = "SELECT * FROM contact_messages ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Messages - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .messages-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .message-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .message-info {
            flex-grow: 1;
        }
        .message-actions {
            display: flex;
            gap: 10px;
        }
        .message-subject {
            font-size: 1.2em;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .message-meta {
            color: #7f8c8d;
            font-size: 0.9em;
        }
        .message-content {
            color: #34495e;
            line-height: 1.6;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8em;
            margin-left: 10px;
        }
        .status-new {
            background-color: #e74c3c;
            color: white;
        }
        .status-read {
            background-color: #3498db;
            color: white;
        }
        .status-replied {
            background-color: #2ecc71;
            color: white;
        }
        .btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-read {
            background-color: #3498db;
            color: white;
        }
        .btn-reply {
            background-color: #2ecc71;
            color: white;
        }
        .btn-delete {
            background-color: #e74c3c;
            color: white;
        }
        .filters {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        .filter-btn {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 20px;
            background: white;
            cursor: pointer;
        }
        .filter-btn.active {
            background-color: #3498db;
            color: white;
            border-color: #3498db;
        }
    </style>
</head>
<body>
    <?php include '../includes/admin_navbar.php'; ?>

    <div class="messages-container">
        <h1>Messages de contact</h1>

        <div class="filters">
            <button class="filter-btn active" data-filter="all">Tous</button>
            <button class="filter-btn" data-filter="new">Nouveaux</button>
            <button class="filter-btn" data-filter="read">Lus</button>
            <button class="filter-btn" data-filter="replied">Répondus</button>
        </div>

        <?php foreach($messages as $message): ?>
            <div class="message-card" data-status="<?php echo $message['status']; ?>">
                <div class="message-header">
                    <div class="message-info">
                        <div class="message-subject">
                            <?php echo htmlspecialchars($message['subject']); ?>
                            <span class="status-badge status-<?php echo $message['status']; ?>">
                                <?php 
                                    switch($message['status']) {
                                        case 'new': echo 'Nouveau';
                                            break;
                                        case 'read': echo 'Lu';
                                            break;
                                        case 'replied': echo 'Répondu';
                                            break;
                                    }
                                ?>
                            </span>
                        </div>
                        <div class="message-meta">
                            De: <?php echo htmlspecialchars($message['name']); ?> 
                            (<?php echo htmlspecialchars($message['email']); ?>)
                            - <?php echo date('d/m/Y H:i', strtotime($message['created_at'])); ?>
                        </div>
                    </div>
                    <div class="message-actions">
                        <?php if($message['status'] === 'new'): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="mark_read">
                                <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                <button type="submit" class="btn btn-read">Marquer comme lu</button>
                            </form>
                        <?php endif; ?>
                        
                        <?php if($message['status'] !== 'replied'): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="mark_replied">
                                <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                <button type="submit" class="btn btn-reply">Marquer comme répondu</button>
                            </form>
                        <?php endif; ?>

                        <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                            <button type="submit" class="btn btn-delete">Supprimer</button>
                        </form>
                    </div>
                </div>
                <div class="message-content">
                    <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        // Filtrage des messages
        document.querySelectorAll('.filter-btn').forEach(button => {
            button.addEventListener('click', function() {
                // Mise à jour des boutons actifs
                document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                // Filtrage des messages
                const filter = this.dataset.filter;
                document.querySelectorAll('.message-card').forEach(card => {
                    if (filter === 'all' || card.dataset.status === filter) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html> 