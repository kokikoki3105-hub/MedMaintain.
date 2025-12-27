<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require "config.php";

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, password, full_name, phone FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            // التحقق إذا كانت البيانات ناقصة (الاسم أو الهاتف)
            if (empty($user['full_name']) || empty($user['phone'])) {
                header("Location: profile.php");
                exit;
            } else {
                header("Location: devices.php");
                exit;
            }
        } else {
            $error = "INVALID_CREDENTIALS: Key mismatch.";
        }
    } else {
        $error = "ACCOUNT_NOT_FOUND: User not recognized. <br> <a href='register.php' style='color:#00f2ff; font-weight:bold;'>[ CLICK HERE TO INITIALIZE ACCOUNT ]</a>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Access Portal | MedMaintain</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&family=Outfit:wght@300;400&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; height: 100vh; display: flex; align-items: center; justify-content: center; background: #060b18; font-family: 'Outfit', sans-serif; color: #fff; }
        .login-card { background: rgba(10, 20, 40, 0.9); padding: 40px; border-radius: 10px; border: 2px solid #00f2ff; width: 100%; max-width: 400px; box-shadow: 0 0 20px rgba(0, 242, 255, 0.2); text-align: center; }
        .header-text { font-family: 'Orbitron', sans-serif; color: #00f2ff; letter-spacing: 4px; margin-bottom: 30px; font-size: 1.5rem; }
        .input-field { width: 100%; padding: 15px; margin-bottom: 20px; background: rgba(0, 242, 255, 0.05); border: 1px solid #00f2ff; border-radius: 5px; color: #fff; box-sizing: border-box; outline: none; }
        .btn-auth { width: 100%; padding: 15px; background: transparent; border: 2px solid #00f2ff; color: #00f2ff; font-family: 'Orbitron', sans-serif; cursor: pointer; transition: 0.3s; text-transform: uppercase; }
        .btn-auth:hover { background: #00f2ff; color: #060b18; box-shadow: 0 0 15px #00f2ff; }
        .error-msg { color: #ff4d4d; font-size: 0.85rem; margin-bottom: 15px; border: 1px solid #ff4d4d; padding: 12px; background: rgba(255, 77, 77, 0.1); text-align: left; line-height: 1.6; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="header-text">ACCESS_PORTAL</div>
    <?php if(!empty($error)): ?> <div class="error-msg"><strong>[ALERT]</strong><br> <?php echo $error; ?> </div> <?php endif; ?>
    <form method="POST">
        <input type="email" name="email" class="input-field" placeholder="USER_IDENTIFIER" required>
        <input type="password" name="password" class="input-field" placeholder="••••••••••••" required>
        <button type="submit" class="btn-auth">AUTHORIZE_NOW</button>
    </form>
    <div style="margin-top:25px; font-size:0.8rem;"><a href="register.php" style="color:#00f2ff; text-decoration:none;">Initialize New Admin Account</a></div>
</div>
</body>
</html>