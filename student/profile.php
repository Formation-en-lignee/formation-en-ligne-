<?php
session_start();
require_once '../config/database.php';

// Vérification de l'authentification
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Récupération des informations de l'utilisateur
$stmt = $db->prepare("SELECT u.*, d.name as department_name 
                      FROM users u 
                      LEFT JOIN departments d ON u.department_id = d.id 
                      WHERE u.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Mise à jour des informations de session
$_SESSION['firstname'] = $user['firstname'];
$_SESSION['lastname'] = $user['lastname'];

// Traitement du formulaire de mise à jour
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($firstname) || empty($lastname) || empty($email)) {
        $error_message = "Tous les champs obligatoires doivent être remplis.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "L'adresse email n'est pas valide.";
    } else {
        // Vérification si l'email existe déjà pour un autre utilisateur
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            $error_message = "Cette adresse email est déjà utilisée.";
        } else {
            // Mise à jour des informations de base
            $stmt = $db->prepare("UPDATE users SET firstname = ?, lastname = ?, email = ? WHERE id = ?");
            if ($stmt->execute([$firstname, $lastname, $email, $_SESSION['user_id']])) {
                $success_message = "Profil mis à jour avec succès.";
                $_SESSION['firstname'] = $firstname;
                $_SESSION['lastname'] = $lastname;
            }

            // Traitement du changement de mot de passe si demandé
            if (!empty($current_password) && !empty($new_password)) {
                if ($new_password !== $confirm_password) {
                    $error_message = "Les nouveaux mots de passe ne correspondent pas.";
                } elseif (strlen($new_password) < 8) {
                    $error_message = "Le nouveau mot de passe doit contenir au moins 8 caractères.";
                } else {
                    // Vérification de l'ancien mot de passe
                    $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $current_hash = $stmt->fetchColumn();

                    if (password_verify($current_password, $current_hash)) {
                        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                        if ($stmt->execute([$new_hash, $_SESSION['user_id']])) {
                            $success_message = "Profil et mot de passe mis à jour avec succès.";
                        }
                    } else {
                        $error_message = "Le mot de passe actuel est incorrect.";
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - E-learning Platform</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .profile-header {
            margin-bottom: 30px;
            text-align: center;
        }
        .form-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .form-section h2 {
            margin-top: 0;
            color: #2c3e50;
            font-size: 1.5em;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .success-message {
            background-color: #2ecc71;
            color: white;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .error-message {
            background-color: #e74c3c;
            color: white;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .user-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .user-info p {
            margin: 5px 0;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <?php include '../includes/student_navbar.php'; ?>

    <div class="profile-container">
        <div class="profile-header">
            <h1>Mon Profil</h1>
        </div>

        <?php if ($success_message): ?>
            <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <div class="user-info">
            <p><strong>Département:</strong> <?php echo htmlspecialchars($user['department_name']); ?></p>
            <p><strong>Inscrit depuis:</strong> <?php echo date('d/m/Y', strtotime($user['created_at'])); ?></p>
        </div>

        <form method="POST" action="">
            <div class="form-section">
                <h2>Informations personnelles</h2>
                <div class="form-group">
                    <label for="firstname">Prénom</label>
                    <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($user['firstname']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="lastname">Nom</label>
                    <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($user['lastname']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
            </div>

            <div class="form-section">
                <h2>Changer le mot de passe</h2>
                <div class="form-group">
                    <label for="current_password">Mot de passe actuel</label>
                    <input type="password" id="current_password" name="current_password">
                </div>

                <div class="form-group">
                    <label for="new_password">Nouveau mot de passe</label>
                    <input type="password" id="new_password" name="new_password">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Mettre à jour le profil</button>
        </form>
    </div>
</body>
</html> 