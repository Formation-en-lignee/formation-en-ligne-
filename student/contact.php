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
    <title>Contact - E-learning Platform</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .contact-hero {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 60px 0;
            text-align: center;
        }

        .contact-hero h1 {
            font-size: 2.5em;
            margin-bottom: 20px;
        }

        .contact-hero p {
            max-width: 800px;
            margin: 0 auto;
            font-size: 1.2em;
            opacity: 0.9;
        }

        .contact-container {
            max-width: 1200px;
            margin: 60px auto;
            padding: 0 20px;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .contact-info {
            background: var(--light-gray);
            padding: 40px;
            border-radius: var(--border-radius);
        }

        .contact-info-item {
            margin-bottom: 30px;
            display: flex;
            align-items: flex-start;
            gap: 15px;
        }

        .contact-info-item i {
            font-size: 1.5em;
            color: var(--primary-color);
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
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-color);
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1em;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        .form-group textarea {
            height: 150px;
            resize: vertical;
        }

        .submit-btn {
            background: var(--primary-color);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1em;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .success-message {
            display: none;
            background: var(--secondary-color);
            color: white;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .contact-grid {
                grid-template-columns: 1fr;
            }

            .contact-hero h1 {
                font-size: 2em;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/student_navbar.php'; ?>

    <section class="contact-hero">
        <h1>Contactez l'administration</h1>
        <p>Notre équipe est là pour vous aider et répondre à toutes vos questions concernant votre formation.</p>
    </section>

    <div class="contact-container">
        <div class="contact-grid">
            <div class="contact-info">
                <h2>Informations de contact</h2>
                <div class="contact-info-item">
                    <i>📍</i>
                    <div class="contact-info-content">
                        <h3>Adresse</h3>
                        <p>123 Rue de l'Innovation<br>75000 Paris, France</p>
                    </div>
                </div>
                <div class="contact-info-item">
                    <i>📧</i>
                    <div class="contact-info-content">
                        <h3>Email</h3>
                        <p>support@elearning.com</p>
                    </div>
                </div>
                <div class="contact-info-item">
                    <i>📞</i>
                    <div class="contact-info-content">
                        <h3>Téléphone</h3>
                        <p>+33 1 23 45 67 89</p>
                    </div>
                </div>
                <div class="contact-info-item">
                    <i>⏰</i>
                    <div class="contact-info-content">
                        <h3>Support disponible</h3>
                        <p>Lundi - Vendredi : 9h00 - 18h00<br>
                        Support en ligne 24/7</p>
                    </div>
                </div>
            </div>

            <div class="contact-form">
                <h2>Envoyez-nous un message</h2>
                <form id="contactForm" action="../api/contact.php" method="POST">
                    <div class="form-group">
                        <label for="subject">Sujet</label>
                        <select id="subject" name="subject" required>
                            <option value="">Sélectionnez un sujet</option>
                            <option value="question_cours">Question sur un cours</option>
                            <option value="probleme_technique">Problème technique</option>
                            <option value="suggestion">Suggestion</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" required></textarea>
                    </div>
                    <button type="submit" class="submit-btn">Envoyer le message</button>
                </form>
                <div class="success-message" id="successMessage">
                    Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('student_id', '<?php echo $_SESSION['user_id']; ?>');
            formData.append('student_name', '<?php echo $_SESSION['firstname'] . ' ' . $_SESSION['lastname']; ?>');
            formData.append('student_email', '<?php echo $_SESSION['email']; ?>');
            
            fetch('../api/contact.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('successMessage').style.display = 'block';
                    this.reset();
                    setTimeout(() => {
                        document.getElementById('successMessage').style.display = 'none';
                    }, 5000);
                } else {
                    alert(data.message || 'Une erreur est survenue. Veuillez réessayer.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Une erreur est survenue. Veuillez réessayer.');
            });
        });
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html> 