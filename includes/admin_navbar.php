<nav class="navbar">
    <div class="navbar-container">
        <a href="../index.php" class="logo">E-Learning Platform</a>
        <div class="nav-links">
            <a href="manage_users.php" class="btn">Gestion Utilisateurs</a>
            <a href="manage_courses.php" class="btn">Gestion Formations</a>
            <a href="statistics.php" class="btn">Statistiques</a>
            <a href="../auth/logout.php" class="btn">Déconnexion</a>
        </div>
    </div>
</nav>

<style>
.navbar {
    background-color: white;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    padding: 15px 0;
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

.logo {
    font-size: 1.5em;
    color: var(--primary-color);
    text-decoration: none;
    font-weight: bold;
}

.nav-links {
    display: flex;
    gap: 20px;
}

.nav-links .btn {
    text-decoration: none;
    padding: 8px 15px;
    border-radius: 4px;
    transition: background-color 0.3s;
}

.nav-links .btn:hover {
    background-color: var(--primary-color);
    color: white;
}
</style> 