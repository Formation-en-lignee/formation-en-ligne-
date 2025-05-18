<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../auth/login.php');
    exit();
}
?>

<nav class="navbar">
    <div class="nav-brand">
        <a href="../student/dashboard.php">E-Learning Platform</a>
    </div>
    <div class="nav-links">
        <a href="../student/home.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'home.php' ? 'active' : ''; ?>">
            Accueil
        </a>
        <a href="../student/dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            Tableau de bord
        </a>
        <a href="../student/available_courses.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'available_courses.php' ? 'active' : ''; ?>">
            Catalogue des cours
        </a>
        <a href="../student/favorites.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'favorites.php' ? 'active' : ''; ?>">
            Favoris
        </a>
        <a href="../student/contact.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>">
            Contact
        </a>
        <a href="../student/profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
            Mon profil
        </a>
    </div>
    <div class="nav-user">
        <span class="user-name"><?php echo htmlspecialchars($_SESSION['firstname'] . ' ' . $_SESSION['lastname']); ?></span>
        <a href="../auth/logout.php" class="btn-logout">Déconnexion</a>
    </div>
</nav>

<style>
.navbar {
    background-color: #2c3e50;
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: white;
    position: sticky;
    top: 0;
    z-index: 1000;
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
    flex-wrap: wrap;
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
    transform: translateY(-2px);
}

.nav-links a.active {
    background-color: #3498db;
}

.nav-user {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.user-name {
    font-weight: 500;
}

.btn-logout {
    background-color: #e74c3c;
    color: white;
    text-decoration: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.btn-logout:hover {
    background-color: #c0392b;
    transform: translateY(-2px);
}

@media (max-width: 992px) {
    .navbar {
        padding: 1rem;
    }

    .nav-links {
        gap: 0.8rem;
    }

    .nav-links a {
        padding: 0.4rem 0.8rem;
        font-size: 0.9rem;
    }
}

@media (max-width: 768px) {
    .navbar {
        flex-direction: column;
        padding: 1rem;
    }

    .nav-links {
        flex-direction: column;
        width: 100%;
        gap: 0.5rem;
        margin: 1rem 0;
    }

    .nav-links a {
        text-align: center;
    }

    .nav-user {
        flex-direction: column;
        width: 100%;
        text-align: center;
        gap: 0.5rem;
    }
}
</style> 