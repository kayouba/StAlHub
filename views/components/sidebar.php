<!-- views/components/sidebar.php -->
<aside class="sidebar">
    <div class="logo">
        <h2>StAlHub</h2>
    </div>

    <nav class="nav-links">
        <a href="/stalhub/dashboard">
            <span>🏠</span>
            Tableau de bord
        </a>
        <a href="/profile">
            <span>👤</span>
            Profil
        </a>
        <a href="/logout">
            <span>⏻</span>
            Déconnexion
        </a>
    </nav>
</aside>

<style>
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: 220px;
        background-color: #074f76;
        color: white;
        padding: 20px;
        box-sizing: border-box;
    }

    .sidebar .logo {
        font-size: 20px;
        font-weight: bold;
        margin-bottom: 30px;
    }

    .nav-links a {
        display: block;
        color: white;
        text-decoration: none;
        padding: 10px 0;
        font-size: 16px;
        transition: 0.3s;
    }

    .nav-links a:hover {
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 5px;
        padding-left: 10px;
    }

    .nav-links span {
        margin-right: 8px;
    }
</style>
