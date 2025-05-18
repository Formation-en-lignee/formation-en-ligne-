<?php
header('Content-Type: application/json');

// Vérifier si la requête est en POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

// Récupérer et nettoyer les données du formulaire
$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
$message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

// Valider les données
if (!$name || !$email || !$subject || !$message) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tous les champs sont requis']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email invalide']);
    exit;
}

// Configuration pour l'envoi d'email
$to = "contact@elearning.com"; // Remplacer par votre email
$headers = "From: $email\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// Préparer le contenu de l'email
$emailContent = "Nouveau message de contact\n\n";
$emailContent .= "Nom: $name\n";
$emailContent .= "Email: $email\n";
$emailContent .= "Sujet: $subject\n\n";
$emailContent .= "Message:\n$message";

// Envoyer l'email
$mailSent = mail($to, "Contact - $subject", $emailContent, $headers);

if ($mailSent) {
    // Vous pouvez également sauvegarder le message dans une base de données ici
    echo json_encode(['success' => true, 'message' => 'Message envoyé avec succès']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => "Erreur lors de l'envoi du message"]);
} 