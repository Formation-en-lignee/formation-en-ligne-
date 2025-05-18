<style>
    .footer {
        background: var(--text-color);
        color: white;
        padding: 60px 0 20px;
    }

    .footer-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .footer-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 40px;
        margin-bottom: 40px;
    }

    .footer-section h3 {
        color: var(--primary-color);
        margin-bottom: 20px;
        font-size: 1.2em;
    }

    .footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-links li {
        margin-bottom: 10px;
    }

    .footer-links a {
        color: #fff;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .footer-links a:hover {
        color: var(--primary-color);
    }

    .footer-social {
        display: flex;
        gap: 15px;
        margin-top: 20px;
    }

    .footer-social a {
        color: white;
        text-decoration: none;
        font-size: 1.5em;
        transition: color 0.3s ease;
    }

    .footer-social a:hover {
        color: var(--primary-color);
    }

    .footer-bottom {
        text-align: center;
        padding-top: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .footer-bottom p {
        margin: 0;
        font-size: 0.9em;
        color: rgba(255, 255, 255, 0.7);
    }
</style>

<footer class="footer">
    <div class="footer-container">
        <div class="footer-grid">
            <div class="footer-section">
                <h3>À propos</h3>
                <p>Notre plateforme E-learning propose des formations de qualité pour développer vos compétences professionnelles.</p>
                <div class="footer-social">
                    <a href="#" title="Facebook">📱</a>
                    <a href="#" title="Twitter">🐦</a>
                    <a href="#" title="LinkedIn">💼</a>
                    <a href="#" title="Instagram">📸</a>
                </div>
            </div>

            <div class="footer-section">
                <h3>Liens rapides</h3>
                <ul class="footer-links">
                    <li><a href="services.php">Nos services</a></li>
                    <li><a href="available_courses.php">Formations</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="auth/register.php">Inscription</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>Formations populaires</h3>
                <ul class="footer-links">
                    <li><a href="available_courses.php?category=web-development">Développement Web</a></li>
                    <li><a href="available_courses.php?category=ux-ui">Design UX/UI</a></li>
                    <li><a href="available_courses.php?category=marketing">Marketing Digital</a></li>
                    <li><a href="available_courses.php?category=data-science">Data Science</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>Contact</h3>
                <ul class="footer-links">
                    <li>📍 123 Rue de l'Innovation</li>
                    <li>75000 Paris, France</li>
                    <li>📧 contact@elearning.com</li>
                    <li>📞 +33 1 23 45 67 89</li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> E-Learning Platform. Tous droits réservés.</p>
        </div>
    </div>
</footer> 