<?php
session_start();
require_once '../config/database.php';

if(isset($_SESSION['user_id'])) {
    header('Location: ../student/dashboard.php');
    exit();
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    // Récupération des données du formulaire
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $department_id = $_POST['department_id'] ?? null;

    // Validation
    if (empty($firstname)) $errors[] = "Le prénom est requis";
    if (empty($lastname)) $errors[] = "Le nom est requis";
    if (empty($email)) $errors[] = "L'email est requis";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "L'email n'est pas valide";
    if (empty($password)) $errors[] = "Le mot de passe est requis";
    if ($password !== $confirm_password) $errors[] = "Les mots de passe ne correspondent pas";
    if (strlen($password) < 8) $errors[] = "Le mot de passe doit contenir au moins 8 caractères";
    if (empty($department_id)) $errors[] = "Le département est requis";

    // Vérifier si le département existe
    if (!empty($department_id)) {
        $stmt = $db->prepare("SELECT id FROM departments WHERE id = ?");
        $stmt->execute([$department_id]);
        if (!$stmt->fetch()) {
            $errors[] = "Le département sélectionné n'existe pas";
        }
    }

    // Vérifier si l'email existe déjà
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = "Cet email est déjà utilisé";
    }

    if (empty($errors)) {
        // Hashage du mot de passe
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insertion dans la base de données
        $stmt = $db->prepare("INSERT INTO users (firstname, lastname, email, password, role, department_id) VALUES (?, ?, ?, ?, 'student', ?)");
        
        if ($stmt->execute([$firstname, $lastname, $email, $hashed_password, $department_id])) {
            $success = true;
            header("refresh:3;url=login.php");
        } else {
            $errors[] = "Une erreur est survenue lors de l'inscription";
        }
    }
}

// Récupération des départements pour le formulaire
$database = new Database();
$db = $database->getConnection();
$stmt = $db->query("SELECT id, name FROM departments ORDER BY name");
$departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - E-learning Platform</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .register-container {
            max-width: 500px;
            margin: 40px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn-register {
            width: 100%;
            padding: 10px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-register:hover {
            background-color: #2980b9;
        }
        .error-message {
            color: #e74c3c;
            margin-bottom: 10px;
        }
        .success-message {
            color: #2ecc71;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h1>Inscription</h1>
        
        <?php if ($success): ?>
            <div class="success-message">
                Inscription réussie ! Vous allez être redirigé vers la page de connexion...
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="firstname">Prénom</label>
                <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($_POST['firstname'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="lastname">Nom</label>
                <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($_POST['lastname'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="department">Département</label>
                <select id="department" name="department_id" required>
                    <option value="">Sélectionnez un département</option>
                    <?php foreach($departments as $department): ?>
                        <option value="<?php echo $department['id']; ?>" 
                            <?php echo (isset($_POST['department_id']) && $_POST['department_id'] == $department['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($department['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit" class="btn-register">S'inscrire</button>
        </form>

        <p style="text-align: center; margin-top: 20px;">
            Déjà inscrit ? <a href="login.php">Connectez-vous</a>
        </p>
    </div>
</body>
</html> 