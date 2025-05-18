<?php
session_start();
require_once '../config/database.php';

// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non authentifié']);
    exit();
}

// Récupération des données
$data = json_decode(file_get_contents('php://input'), true);
$course_id = $data['course_id'] ?? null;

if (!$course_id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID du cours manquant']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Vérifier si le cours existe
$stmt = $db->prepare("SELECT id FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
if (!$stmt->fetch()) {
    http_response_code(404);
    echo json_encode(['error' => 'Cours non trouvé']);
    exit();
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        // Ajouter aux favoris
        $stmt = $db->prepare("INSERT IGNORE INTO favorites (user_id, course_id, created_at) VALUES (?, ?, NOW())");
        if ($stmt->execute([$_SESSION['user_id'], $course_id])) {
            echo json_encode(['success' => true, 'message' => 'Ajouté aux favoris']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de l\'ajout aux favoris']);
        }
        break;

    case 'DELETE':
        // Retirer des favoris
        $stmt = $db->prepare("DELETE FROM favorites WHERE user_id = ? AND course_id = ?");
        if ($stmt->execute([$_SESSION['user_id'], $course_id])) {
            echo json_encode(['success' => true, 'message' => 'Retiré des favoris']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la suppression des favoris']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Méthode non autorisée']);
        break;
}
?> 