<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../auth/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - E-learning Platform</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .hero {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
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
            background: var(--light-gray);
        }

        .feature-card {
            background: white;
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            font-size: 2.5em;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .stats {
            padding: 60px 0;
            background: white;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 2.5em;
            color: var(--primary-color);
            font-weight: bold;
            margin-bottom: 10px;
        }

        .cta {
            background: linear-gradient(135deg, var(--secondary-color), #27ae60);
            color: white;
            padding: 80px 0;
            text-align: center;
        }

        .cta-content {
            max-width: 600px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .cta h2 {
            font-size: 2em;
            margin-bottom: 20px;
            color: white;
        }

        .testimonials {
            padding: 80px 0;
            background: var(--light-gray);
        }

        .testimonial-card {
            background: white;
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .testimonial-content {
            font-style: italic;
            margin-bottom: 20px;
            color: #666;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .author-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }

        .author-info {
            flex: 1;
        }

        .author-name {
            font-weight: bold;
            color: var(--text-color);
        }

        .author-title {
            color: #666;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <?php include '../includes/student_navbar.php'; ?>

    <section class="hero">
        <div class="hero-content">
            <h1>Bienvenue sur votre plateforme d'apprentissage</h1>
            <p>Développez vos compétences avec nos formations de qualité et notre accompagnement personnalisé.</p>
            <a href="available_courses.php" class="btn btn-primary">Découvrir les formations</a>
        </div>
    </section>

    <section class="features">
        <div class="container">
            <h2 class="text-center mb-20">Nos atouts</h2>
            <div class="grid grid-4">
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
        </div>
    </section>

    <section class="stats">
        <div class="container">
            <div class="grid grid-4">
                <div class="stat-item">
                    <div class="stat-number">1000+</div>
                    <div class="stat-label">Étudiants actifs</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">50+</div>
                    <div class="stat-label">Formations disponibles</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">95%</div>
                    <div class="stat-label">Taux de satisfaction</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Support disponible</div>
                </div>
            </div>
        </div>
    </section>

    <section class="cta">
        <div class="cta-content">
            <h2>Prêt à commencer votre prochaine formation ?</h2>
            <p>Explorez notre catalogue de cours et trouvez la formation qui vous correspond.</p>
            <a href="available_courses.php" class="btn btn-outline">Voir les formations</a>
        </div>
    </section>

    <section class="testimonials">
        <div class="container">
            <h2 class="text-center mb-20">Ce que disent nos étudiants</h2>
            <div class="grid grid-3">
                <div class="testimonial-card">
                    <p class="testimonial-content">"Une excellente plateforme pour apprendre. Les cours sont bien structurés et les formateurs sont très compétents."</p>
                    <div class="testimonial-author">
                        <img src="../assets/images/avatar1.jpg" alt="Sophie Martin" class="author-avatar">
                        <div class="author-info">
                            <div class="author-name">Sophie Martin</div>
                            <div class="author-title">Étudiante en développement web</div>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <p class="testimonial-content">"J'ai pu acquérir de nouvelles compétences rapidement grâce aux formations pratiques."</p>
                    <div class="testimonial-author">
                        <img src="../assets/images/avatar2.jpg" alt="Thomas Dubois" class="author-avatar">
                        <div class="author-info">
                            <div class="author-name">Thomas Dubois</div>
                            <div class="author-title">Designer UX/UI</div>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <p class="testimonial-content">"Le support pédagogique est excellent et la flexibilité des cours est parfaite."</p>
                    <div class="testimonial-author">
                        <img src="../assets/images/avatar3.jpg" alt="Julie Bernard" class="author-avatar">
                        <div class="author-info">
                            <div class="author-name">Julie Bernard</div>
                            <div class="author-title">Responsable marketing</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>
</body>
</html> 