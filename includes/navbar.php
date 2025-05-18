<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
    .navbar {
        background: white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        padding: 1rem 0;
        position: sticky;
        top: 0;
        z-index: 1000;
    }

    .navbar-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .navbar-logo {
        font-size: 1.5em;
        font-weight: bold;
        color: var(--primary-color);
        text-decoration: none;
    }

    .navbar-menu {
        display: flex;
        gap: 20px;
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .navbar-menu li a {
        color: var(--text-color);
        text-decoration: none;
        padding: 8px 12px;
        border-radius: 6px;
        transition: all 0.3s ease;
    }

    .navbar-menu li a:hover,
    .navbar-menu li a.active {
        background: var(--primary-color);
        color: white;
    }

    .navbar-toggle {
        display: none;
        background: none;
        border: none;
        font-size: 1.5em;
        cursor: pointer;
        color: var(--text-color);
    }

    @media (max-width: 768px) {
        .navbar-toggle {
            display: block;
        }

        .navbar-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            flex-direction: column;
            text-align: center;
        }

        .navbar-menu.active {
            display: flex;
        }
    }
</style>

<nav class="navbar">
    <div class="navbar-container">
        <a href="index.php" class="navbar-logo">E-Learning</a>
        <button class="navbar-toggle" id="navbarToggle">☰</button>
        <ul class="navbar-menu" id="navbarMenu">
            <li><a href="index.php" <?php echo $current_page == 'index.php' ? 'class="active"' : ''; ?>>Accueil</a></li>
            <li><a href="services.php" <?php echo $current_page == 'services.php' ? 'class="active"' : ''; ?>>Services</a></li>
            <li><a href="available_courses.php" <?php echo $current_page == 'available_courses.php' ? 'class="active"' : ''; ?>>Formations</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="student/dashboard.php" <?php echo $current_page == 'dashboard.php' ? 'class="active"' : ''; ?>>Mon Tableau de Bord</a></li>
                <li><a href="auth/logout.php">Déconnexion</a></li>
            <?php else: ?>
                <li><a href="auth/login.php" <?php echo $current_page == 'login.php' ? 'class="active"' : ''; ?>>Connexion</a></li>
                <li><a href="auth/register.php" <?php echo $current_page == 'register.php' ? 'class="active"' : ''; ?>>Inscription</a></li>
            <?php endif; ?>
            <li><a href="contact.php" <?php echo $current_page == 'contact.php' ? 'class="active"' : ''; ?>>Contact</a></li>
        </ul>
    </div>
</nav>

<script>
    document.getElementById('navbarToggle').addEventListener('click', function() {
        document.getElementById('navbarMenu').classList.toggle('active');
    });

    // Fermer le menu mobile lors du clic sur un lien
    document.querySelectorAll('.navbar-menu a').forEach(link => {
        link.addEventListener('click', () => {
            document.getElementById('navbarMenu').classList.remove('active');
        });
    });

    // Fermer le menu mobile lors du clic en dehors
    document.addEventListener('click', (e) => {
        const menu = document.getElementById('navbarMenu');
        const toggle = document.getElementById('navbarToggle');
        if (!menu.contains(e.target) && !toggle.contains(e.target) && menu.classList.contains('active')) {
            menu.classList.remove('active');
        }
    });
</script> 