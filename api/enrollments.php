<?php
session_start();
require_once '../config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Récupérer la méthode HTTP
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // Inscrire un étudiant à un cours
        $data = json_decode(file_get_contents('php://input'), true);
        $course_id = $data['course_id'] ?? null;

        if (!$course_id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID du cours manquant']);
            exit();
        }

        // Vérifier si le cours existe
        $check_course = "SELECT id FROM courses WHERE id = :course_id";
        $stmt = $db->prepare($check_course);
        $stmt->bindParam(':course_id', $course_id);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Cours non trouvé']);
            exit();
        }

        // Vérifier si l'étudiant est déjà inscrit
        $check_enrollment = "SELECT id FROM course_enrollments 
                           WHERE user_id = :user_id AND course_id = :course_id";
        $stmt = $db->prepare($check_enrollment);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->bindParam(':course_id', $course_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            http_response_code(409);
            echo json_encode(['error' => 'Déjà inscrit à ce cours']);
            exit();
        }

        // Inscrire l'étudiant
        $query = "INSERT INTO course_enrollments (user_id, course_id) 
                 VALUES (:user_id, :course_id)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->bindParam(':course_id', $course_id);

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(['message' => 'Inscription réussie']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de l\'inscription']);
        }
        break;

    case 'DELETE':
        // Désinscrire un étudiant d'un cours
        $data = json_decode(file_get_contents('php://input'), true);
        $course_id = $data['course_id'] ?? null;

        if (!$course_id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID du cours manquant']);
            exit();
        }

        $query = "DELETE FROM course_enrollments 
                 WHERE user_id = :user_id AND course_id = :course_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->bindParam(':course_id', $course_id);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                http_response_code(200);
                echo json_encode(['message' => 'Désinscription réussie']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Inscription non trouvée']);
            }
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la désinscription']);
        }
        break;

    case 'PUT':
        // Mettre à jour le statut de progression
        $data = json_decode(file_get_contents('php://input'), true);
        $course_id = $data['course_id'] ?? null;
        $status = $data['status'] ?? null;

        if (!$course_id || !$status) {
            http_response_code(400);
            echo json_encode(['error' => 'Paramètres manquants']);
            exit();
        }

        if (!in_array($status, ['not_started', 'in_progress', 'completed'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Statut invalide']);
            exit();
        }

        $query = "UPDATE course_enrollments 
                 SET completion_status = :status 
                 WHERE user_id = :user_id AND course_id = :course_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->bindParam(':course_id', $course_id);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                http_response_code(200);
                echo json_encode(['message' => 'Statut mis à jour']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Inscription non trouvée']);
            }
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la mise à jour']);
        }
        break;

    case 'GET':
        // Récupérer les inscriptions de l'utilisateur
        $query = "SELECT c.*, ce.completion_status, ce.enrollment_date 
                 FROM courses c 
                 INNER JOIN course_enrollments ce ON c.id = ce.course_id 
                 WHERE ce.user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();

        $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($enrollments);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Méthode non autorisée']);
        break;
}
?> 