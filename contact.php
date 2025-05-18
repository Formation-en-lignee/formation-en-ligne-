<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - E-learning Platform</title>
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

        .contact-container {
            max-width: 1200px;
            margin: 60px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .contact-info {
            background: #f5f6fa;
            padding: 40px;
            border-radius: 8px;
        }

        .contact-info-item {
            margin-bottom: 30px;
            display: flex;
            align-items: flex-start;
            gap: 15px;
        }

        .contact-info-item i {
            font-size: 1.5em;
            color: #3498db;
            width: 30px;
            text-align: center;
        }

        .contact-info-content h3 {
            color: var(--text-color);
            margin-bottom: 5px;
        }

        .contact-info-content p {
            color: #666;
            line-height: 1.6;
        }

        .contact-form {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1em;
        }

        .form-group textarea {
            height: 150px;
            resize: vertical;
        }

        .btn-submit {
            background: #3498db;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            transition: background 0.3s;
        }

        .btn-submit:hover {
            background: #2980b9;
        }

        .success-message {
            display: none;
            background: #2ecc71;
            color: white;
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
            text-align: center;
        }

        .map-container {
            margin-top: 60px;
            border-radius: var(--border-radius);
            overflow: hidden;
            height: 400px;
        }

        .map-container iframe {
            width: 100%;
            height: 100%;
            border: 0;
        }

        @media (max-width: 768px) {
            .contact-container {
                grid-template-columns: 1fr;
            }

            .navbar {
                flex-direction: column;
                padding: 1rem;
            }

            .nav-links {
                margin-top: 1rem;
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

    <div class="contact-container">
        <div class="contact-info">
            <h2>Informations de contact</h2>
            <div class="contact-info-item">
                <i>📍</i>
                <div>
                    <h3>Adresse</h3>
                    <p>123 Rue de l'Innovation<br>75000 Paris, France</p>
                </div>
            </div>
            <div class="contact-info-item">
                <i>📧</i>
                <div>
                    <h3>Email</h3>
                    <p>contact@elearning.com</p>
                </div>
            </div>
            <div class="contact-info-item">
                <i>📞</i>
                <div>
                    <h3>Téléphone</h3>
                    <p>+33 1 23 45 67 89</p>
                </div>
            </div>
            <div class="contact-info-item">
                <i>⏰</i>
                <div>
                    <h3>Horaires</h3>
                    <p>Lundi - Vendredi : 9h00 - 18h00<br>
                    Support en ligne 24/7</p>
                </div>
            </div>
        </div>

        <div class="contact-form">
            <h2>Envoyez-nous un message</h2>
            <form id="contactForm">
                <div class="form-group">
                    <label for="name">Nom complet</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="subject">Sujet</label>
                    <input type="text" id="subject" name="subject" required>
                </div>
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" required></textarea>
                </div>
                <button type="submit" class="btn-submit">Envoyer le message</button>
            </form>
            <div class="success-message" id="successMessage">
                Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.
            </div>
        </div>
    </div>

    <div class="map-container">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2624.9916256937595!2d2.292292615509614!3d48.85837007928757!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47e66e2964e34e2d%3A0x8ddca9ee380ef7e0!2sTour%20Eiffel!5m2!1s!2sfr" allowfullscreen="" loading="lazy"></iframe>
    </div>

    <script>
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            // Simuler l'envoi du message
            document.getElementById('successMessage').style.display = 'block';
            this.reset();
            setTimeout(() => {
                document.getElementById('successMessage').style.display = 'none';
            }, 5000);
        });
    </script>
</body>
</html> 