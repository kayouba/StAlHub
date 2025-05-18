<!-- Bouton mobile (affiché uniquement sur petits écrans) -->
<div class="sidebar-toggle" onclick="toggleSidebar()">☰</div>

<!-- Barre latérale -->
<aside class="sidebar" id="sidebar">
    <div class="logo">
        <h2>StAlHub</h2>
    </div>

    <nav class="nav-links">
        <a href="/stalhub/dashboard">
            <span>🏠</span>
            Tableau de bord
        </a>
        <a href="/stalhub/profile">
            <span>👤</span>
            Profil
        </a>
        <a href="/stalhub">
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
        transition: transform 0.3s ease;
        z-index: 998;
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

    /* Bouton hamburger pour mobile */
    .sidebar-toggle {
        display: none;
        position: fixed;
        top: 10px;
        left: 10px;
        font-size: 28px;
        background-color: #074f76;
        color: white;
        padding: 5px 10px;
        border-radius: 5px;
        cursor: pointer;
        z-index: 999;
    }

    @media screen and (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
        }

        .sidebar.visible {
            transform: translateX(0);
        }

        .sidebar-toggle {
            display: block;
        }
    }
</style>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('visible');
    }
</script>
