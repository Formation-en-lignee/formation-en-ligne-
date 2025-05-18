<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page non trouvée - E-learning Platform</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .error-container {
            min-height: 80vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px;
        }

        .error-code {
            font-size: 8em;
            color: var(--primary-color);
            font-weight: bold;
            margin: 0;
            line-height: 1;
        }

        .error-message {
            font-size: 2em;
            color: var(--text-color);
            margin: 20px 0;
        }

        .error-description {
            color: #666;
            margin-bottom: 30px;
            max-width: 600px;
        }

        .error-actions {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .error-btn {
            display: inline-block;
            padding: 12px 25px;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .error-btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .error-btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .error-btn-secondary {
            background: var(--light-gray);
            color: var(--text-color);
        }

        .error-btn-secondary:hover {
            background: #e0e0e0;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .error-code {
                font-size: 6em;
            }

            .error-message {
                font-size: 1.5em;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="error-container">
        <h1 class="error-code">404</h1>
        <h2 class="error-message">Page non trouvée</h2>
        <p class="error-description">
            Désolé, la page que vous recherchez n'existe pas ou a été déplacée.
            Vous pouvez retourner à la page d'accueil ou explorer nos formations.
        </p>
        <div class="error-actions">
            <a href="index.php" class="error-btn error-btn-primary">Retour à l'accueil</a>
            <a href="available_courses.php" class="error-btn error-btn-secondary">Voir les formations</a>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 