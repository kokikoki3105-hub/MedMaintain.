<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedMaintain | Professional Medical Management</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0a192f;
            --text-color: #ffffff;
            --accent-color: #00fff2;
            --hero-gradient: radial-gradient(circle at center, rgba(0, 255, 242, 0.15) 0%, transparent 70%);
        }

        /* Light Mode Variables */
        body.light-mode {
            --bg-color: #f0f4f8;
            --text-color: #1a2a3a;
            --accent-color: #007bff;
            --hero-gradient: radial-gradient(circle at center, rgba(0, 123, 255, 0.1) 0%, transparent 70%);
        }

        body {
            margin: 0;
            padding: 0;
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: background-color 0.4s ease, color 0.4s ease;
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
        }

        .hero {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: var(--hero-gradient);
        }

        /* Sophisticated Logo Styling */
        .logo-wrapper {
            position: relative;
            display: inline-block;
            padding: 20px;
            margin-bottom: 30px;
            animation: floatAnim 5s ease-in-out infinite;
        }

        .hero-logo-img {
            width: 200px;
            height: auto;
            /* Removes the harsh white box look and blends it with the UI */
            mix-blend-mode: screen; 
            filter: drop-shadow(0 0 15px var(--accent-color));
            border-radius: 50%; /* Softens the square edges */
            opacity: 0.9;
        }

        /* Cybernetic Glow Aura */
        .logo-wrapper::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 140px;
            height: 140px;
            background: var(--accent-color);
            filter: blur(60px);
            opacity: 0.2;
            border-radius: 50%;
            z-index: -1;
        }

        @keyframes floatAnim {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .project-name {
            font-family: 'Orbitron', sans-serif;
            font-size: 4rem;
            letter-spacing: 8px;
            margin: 0;
            background: linear-gradient(180deg, #fff, var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-transform: uppercase;
        }

        .project-tagline {
            color: #8892b0;
            font-size: 1.1rem;
            letter-spacing: 2px;
            margin-bottom: 40px;
            text-transform: uppercase;
        }

        .cta-container {
            display: flex;
            gap: 25px;
        }

        .btn-tech {
            padding: 14px 40px;
            border: 1px solid var(--accent-color);
            color: var(--accent-color);
            text-decoration: none;
            font-family: 'Orbitron', sans-serif;
            font-size: 0.85rem;
            letter-spacing: 2px;
            border-radius: 2px;
            transition: all 0.3s;
            background: rgba(0, 255, 242, 0.03);
        }

        .btn-tech:hover {
            background: var(--accent-color);
            color: #0a192f;
            box-shadow: 0 0 25px rgba(0, 255, 242, 0.5);
            transform: translateY(-3px);
        }

        .header {
            padding: 20px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: absolute;
            width: 100%;
            box-sizing: border-box;
        }

        .nav-links a {
            color: var(--text-color);
            text-decoration: none;
            margin-left: 30px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: 0.3s;
            opacity: 0.8;
        }

        .nav-links a:hover {
            color: var(--accent-color);
            opacity: 1;
        }

        .theme-toggle {
            cursor: pointer;
            font-size: 1.4rem;
            margin-left: 30px;
            user-select: none;
        }
    </style>
</head>
<body id="appBody">

<header class="header">
    <div class="header-logo">
        <img src="logo.png" alt="MedMaintain" style="height: 35px; mix-blend-mode: screen; filter: brightness(1.2);">
    </div>
    <nav class="nav-links">
        <a href="index.php">DASHBOARD_HOME</a>
        <a href="register.php">CREATE_ACCOUNT</a>
        <a href="login.php">SYSTEM_LOGIN</a>
        <span class="theme-toggle" onclick="handleThemeToggle()" id="themeSwitcher">🌙</span>
    </nav>
</header>

<section class="hero">
    <div class="logo-wrapper">
        <img src="logo.png" alt="MedMaintain Logo" class="hero-logo-img">
    </div>

    <h1 class="project-name">MedMaintain</h1>
    <p class="project-tagline">Advanced Medical Infrastructure Asset Management</p>
    
    <div class="cta-container">
        <a href="register.php" class="btn-tech">INITIALIZE_SETUP</a>
        <a href="login.php" class="btn-tech" style="border-color: #8892b0; color: #8892b0;">ACCESS_PORTAL</a>
    </div>
</section>

<script>
    /**
     * Theme Toggle Logic
     * Switches between Dark and Light mode and persists the choice
     */
    function handleThemeToggle() {
        const body = document.getElementById('appBody');
        const icon = document.getElementById('themeSwitcher');
        
        body.classList.toggle('light-mode');
        
        if (body.classList.contains('light-mode')) {
            icon.innerText = '☀️';
            localStorage.setItem('med_theme', 'light');
        } else {
            icon.innerText = '🌙';
            localStorage.setItem('med_theme', 'dark');
        }
    }

    /**
     * Load persistent theme on page load
     */
    window.onload = () => {
        const activeTheme = localStorage.getItem('med_theme');
        if (activeTheme === 'light') {
            document.getElementById('appBody').classList.add('light-mode');
            document.getElementById('themeSwitcher').innerText = '☀️';
        }
    };
</script>
</body>
</html>