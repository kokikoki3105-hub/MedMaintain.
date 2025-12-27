<?php
ob_start();
/* Start session and load database config */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require "config.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

    /* --- Integrity Check: Duplicate Email --- */
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $error = "CRITICAL_ERROR: Email identity already exists in database.";
    } else {
        /* --- Execute System Registration --- */
        $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $email, $password);
        if ($stmt->execute()) {
            $_SESSION['user_id'] = $conn->insert_id;
            header("Location: profile.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Registration | MedMaintain</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-deep: #060b18;
            --neon-cyan: #00f2ff;
            --border-tech: rgba(0, 242, 255, 0.3);
            --card-bg: rgba(10, 25, 47, 0.9);
        }

        body, html {
            height: 100%; margin: 0;
            font-family: 'Share Tech Mono', monospace;
            background-color: var(--bg-deep);
            display: flex; justify-content: center; align-items: center;
            overflow: hidden;
        }

        /* Technical Background Pattern */
        .bg-grid {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-image: linear-gradient(var(--border-tech) 1px, transparent 1px),
                              linear-gradient(90deg, var(--border-tech) 1px, transparent 1px);
            background-size: 50px 50px;
            opacity: 0.1; z-index: -1;
        }

        .register-container {
            width: 420px; padding: 40px;
            background: var(--card-bg);
            border: 1px solid var(--neon-cyan);
            box-shadow: 0 0 30px rgba(0, 242, 255, 0.1);
            position: relative;
        }

        /* Decorative Corners */
        .register-container::before {
            content: ''; position: absolute; top: -5px; left: -5px;
            width: 20px; height: 20px; border-top: 3px solid var(--neon-cyan); border-left: 3px solid var(--neon-cyan);
        }

        h2 {
            font-family: 'Orbitron', sans-serif;
            color: var(--neon-cyan);
            font-size: 1.4rem; text-align: center;
            letter-spacing: 3px; margin-bottom: 30px;
            text-transform: uppercase;
        }

        .field-label {
            font-size: 12px; color: var(--neon-cyan);
            margin-bottom: 8px; display: block; opacity: 0.8;
        }

        input {
            width: 100%; padding: 15px; margin-bottom: 20px;
            background: rgba(0, 242, 255, 0.05);
            border: 1px solid var(--border-tech);
            color: #fff; box-sizing: border-box;
            font-family: 'Share Tech Mono', monospace; outline: none;
            transition: 0.3s;
        }

        input:focus {
            border-color: var(--neon-cyan);
            background: rgba(0, 242, 255, 0.1);
            box-shadow: 0 0 10px rgba(0, 242, 255, 0.2);
        }

        .btn-tech {
            width: 100%; padding: 15px;
            background: transparent;
            border: 1px solid var(--neon-cyan);
            color: var(--neon-cyan);
            font-family: 'Orbitron', sans-serif;
            font-weight: bold; cursor: pointer;
            transition: 0.3s; letter-spacing: 2px;
            clip-path: polygon(10% 0, 100% 0, 90% 100%, 0% 100%);
        }

        .btn-tech:hover {
            background: var(--neon-cyan);
            color: var(--bg-deep);
            box-shadow: 0 0 20px var(--neon-cyan);
        }

        .error-banner {
            color: #ff4d4d; font-size: 12px;
            border: 1px solid #ff4d4d; padding: 10px;
            margin-bottom: 20px; background: rgba(255, 0, 0, 0.1);
        }

        .footer-link {
            text-align: center; margin-top: 25px; font-size: 0.8rem;
        }

        .footer-link a {
            color: var(--neon-cyan); text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="bg-grid"></div>
    
    <div class="register-container">
        <h2>INITIALIZE_ADMIN</h2>
        
        <?php if(!empty($error)): ?>
            <div class="error-banner">[SYSTEM_ALERT] <?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <label class="field-label">ADMIN_EMAIL_IDENTIFIER</label>
            <input type="email" name="email" placeholder="example@med.sys" required>
            
            <label class="field-label">SECURE_ACCESS_KEY</label>
            <input type="password" name="password" placeholder="••••••••••••" required>
            
            <button type="submit" class="btn-tech">EXECUTE_REGISTRATION</button>
        </form>

        <div class="footer-link">
            Already registered? <a href="login.php">[ LOGIN_EXISTING_USER ]</a>
        </div>
    </div>
</body>
</html>