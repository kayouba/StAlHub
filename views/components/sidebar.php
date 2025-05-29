<!-- Bouton mobile -->
<div class="sidebar-toggle" onclick="toggleSidebar()">☰</div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="logo">
        <h2><span>St</span>AlHub</h2>
    </div>

    <nav class="nav-links">
        <a href="/stalhub/dashboard"><span>🏠</span> Tableau de bord</a>
        <a href="/stalhub/profile"><span>👤</span> Profil</a>
        <?php if (!empty($_SESSION['user']) && !empty($_SESSION['user']['is_admin'])): ?>
            <a href="/stalhub/admin/dashboard"><span>⚙️</span> Administration</a>
            <a href="/stalhub/admin/stats"><span>📊</span> Statistiques</a>
        <?php endif; ?>
        <a href="/stalhub/logout"><span>⏻</span> Déconnexion</a>
    </nav>
</aside>

<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap" rel="stylesheet">
<style>
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: 240px;
        background: linear-gradient(180deg, #001F3F, #003a70);
        color: white;
        padding: 25px;
        box-sizing: border-box;
        transition: transform 0.3s ease;
        z-index: 998;
        box-shadow: 3px 0 15px rgba(0, 204, 255, 0.2);
    }

    .sidebar .logo {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
        height: 60px;
    }

    .sidebar .logo h2 {
        margin: 0;
        padding: 0;
        font-family: 'Orbitron', sans-serif;
        font-size: 22px;
        color: white;
        letter-spacing: 2px;
    }

    .sidebar .logo h2 span {
        color: #00cfff !important;
    }

    .nav-links a {
        display: flex;
        align-items: center;
        color: white;
        text-decoration: none;
        padding: 12px 16px;
        margin-bottom: 10px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.05);
        transition: 0.3s;
    }

    .nav-links a:hover {
        background-color: rgba(0, 204, 255, 0.2);
        padding-left: 20px;
        color: #00cfff;
    }

    .nav-links span {
        margin-right: 12px;
    }

    .sidebar-toggle {
        display: none;
        position: fixed;
        top: 15px;
        left: 15px;
        font-size: 26px;
        background: #003a70;
        color: white;
        padding: 8px 12px;
        border-radius: 8px;
        cursor: pointer;
        z-index: 999;
        box-shadow: 0 0 10px rgba(0, 204, 255, 0.4);
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
        document.getElementById('sidebar').classList.toggle('visible');
    }
</script>