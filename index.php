<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-learning Platform - Accueil</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .navbar {
            background-color: #2c3e50;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }

        .nav-brand a {
            color: white;
            text-decoration: none;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .nav-links {
            display: flex;
            gap: 1.5rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .nav-links a:hover {
            background-color: #34495e;
        }

        .hero {
            background: linear-gradient(135deg, #3498db, #2c3e50);
            color: white;
            padding: 80px 0;
            text-align: center;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .hero h1 {
            font-size: 2.5em;
            margin-bottom: 20px;
        }

        .hero p {
            font-size: 1.2em;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .features {
            padding: 80px 0;
            background: #f5f6fa;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .feature-card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .feature-icon {
            font-size: 2.5em;
            color: #3498db;
            margin-bottom: 20px;
        }

        .cta {
            background: #2ecc71;
            color: white;
            padding: 80px 0;
            text-align: center;
        }

        .cta-content {
            max-width: 600px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background 0.3s ease;
        }

        .btn:hover {
            background: #2980b9;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid white;
        }

        .btn-outline:hover {
            background: rgba(255,255,255,0.1);
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                padding: 1rem;
            }

            .nav-links {
                margin-top: 1rem;
            }

            .hero h1 {
                font-size: 2em;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <a href="index.php">E-Learning Platform</a>
        </div>
        <div class="nav-links">
            <a href="index.php">Accueil</a>
            <a href="contact.php">Contact</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="student/dashboard.php">Tableau de bord</a>
                <a href="auth/logout.php">Déconnexion</a>
            <?php else: ?>
                <a href="auth/login.php">Connexion</a>
                <a href="auth/register.php">Inscription</a>
            <?php endif; ?>
        </div>
    </nav>

    <section class="hero">
        <div class="hero-content">
            <h1>Bienvenue sur notre plateforme d'apprentissage en ligne</h1>
            <p>Développez vos compétences avec nos formations de qualité et notre accompagnement personnalisé.</p>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="auth/register.php" class="btn btn-outline">Commencer maintenant</a>
            <?php else: ?>
                <a href="student/dashboard.php" class="btn btn-outline">Accéder à mon espace</a>
            <?php endif; ?>
        </div>
    </section>

    <section class="features">
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">📚</div>
                <h3>Formations variées</h3>
                <p>Des cours dans différents domaines pour répondre à tous vos besoins.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🎯</div>
                <h3>Apprentissage personnalisé</h3>
                <p>Un parcours adapté à votre rythme et vos objectifs.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">👨‍🏫</div>
                <h3>Experts qualifiés</h3>
                <p>Des formateurs expérimentés pour vous guider.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🏆</div>
                <h3>Certifications</h3>
                <p>Obtenez des certificats reconnus dans votre domaine.</p>
            </div>
        </div>
    </section>

    <section class="cta">
        <div class="cta-content">
            <h2>Prêt à commencer votre apprentissage ?</h2>
            <p>Rejoignez notre communauté d'apprenants dès aujourd'hui.</p>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="auth/register.php" class="btn btn-outline">S'inscrire gratuitement</a>
            <?php else: ?>
                <a href="student/dashboard.php" class="btn btn-outline">Voir les formations</a>
            <?php endif; ?>
        </div>
    </section>
</body>
</html> 