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
    $user_id = $_POST['user_id'] ?? '';

    switch($action) {
        case 'toggle_status':
            $stmt = $db->prepare("UPDATE users SET is_active = NOT is_active WHERE id = :id");
            $stmt->execute(['id' => $user_id]);
            break;

        case 'delete':
            $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute(['id' => $user_id]);
            break;

        case 'update':
            $stmt = $db->prepare("UPDATE users SET 
                firstname = :firstname,
                lastname = :lastname,
                email = :email,
                department_id = :department_id
                WHERE id = :id");
            $stmt->execute([
                'firstname' => $_POST['firstname'],
                'lastname' => $_POST['lastname'],
                'email' => $_POST['email'],
                'department_id' => $_POST['department_id'],
                'id' => $user_id
            ]);
            break;
    }
    
    header('Location: manage_users.php');
    exit();
}

// Récupération des utilisateurs
$query = "SELECT u.*, d.name as department_name 
          FROM users u 
          LEFT JOIN departments d ON u.department_id = d.id 
          WHERE u.role = 'student' 
          ORDER BY u.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération des départements pour le formulaire
$dept_query = "SELECT * FROM departments ORDER BY name";
$dept_stmt = $db->prepare($dept_query);
$dept_stmt->execute();
$departments = $dept_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .users-table th, .users-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .users-table th {
            background-color: #f5f5f5;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
        }
        .status-active {
            background-color: #2ecc71;
            color: white;
        }
        .status-inactive {
            background-color: #e74c3c;
            color: white;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .btn-edit, .btn-delete, .btn-toggle {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
        }
        .btn-edit {
            background-color: #3498db;
            color: white;
        }
        .btn-delete {
            background-color: #e74c3c;
            color: white;
        }
        .btn-toggle {
            background-color: #f1c40f;
            color: black;
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
    </style>
</head>
<body>
    <?php include '../includes/admin_navbar.php'; ?>

    <div class="admin-container">
        <h1>Gestion des Utilisateurs</h1>

        <table class="users-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Département</th>
                    <th>Statut</th>
                    <th>Date d'inscription</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['department_name'] ?? 'Non assigné'); ?></td>
                        <td>
                            <span class="status-badge <?php echo $user['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo $user['is_active'] ? 'Actif' : 'Inactif'; ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                        <td class="action-buttons">
                            <button class="btn-edit" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                Modifier
                            </button>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="toggle_status">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" class="btn-toggle">
                                    <?php echo $user['is_active'] ? 'Désactiver' : 'Activer'; ?>
                                </button>
                            </form>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" class="btn-delete">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal de modification -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Modifier l'utilisateur</h2>
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="user_id" id="edit_user_id">
                
                <div class="form-group">
                    <label for="firstname">Prénom</label>
                    <input type="text" id="edit_firstname" name="firstname" required>
                </div>

                <div class="form-group">
                    <label for="lastname">Nom</label>
                    <input type="text" id="edit_lastname" name="lastname" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="edit_email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="department_id">Département</label>
                    <select id="edit_department_id" name="department_id">
                        <option value="">Sélectionner un département</option>
                        <?php foreach($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>">
                                <?php echo htmlspecialchars($dept['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(user) {
            document.getElementById('editModal').style.display = 'block';
            document.getElementById('edit_user_id').value = user.id;
            document.getElementById('edit_firstname').value = user.firstname;
            document.getElementById('edit_lastname').value = user.lastname;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_department_id').value = user.department_id || '';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Fermer la modal si on clique en dehors
        window.onclick = function(event) {
            if (event.target == document.getElementById('editModal')) {
                closeEditModal();
            }
        }
    </script>
</body>
</html> 