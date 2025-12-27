<?php
require "config.php";

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $code = trim($_POST['code']);
    $new_password = trim($_POST['new_password']);

    if(isset($_SESSION['reset_code']) && $code == $_SESSION['reset_code']){
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE user_profile SET password=? WHERE user_id=?");
        $stmt->bind_param("si", $hashed_password, $_SESSION['reset_user']);
        if($stmt->execute()){
            unset($_SESSION['reset_code'], $_SESSION['reset_user']);
            $success = "Password updated successfully!";
        } else {
            $error = "Error updating password!";
        }
    } else {
        $error = "Invalid code!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reset Password | MedMaintain</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="login-bg">
<form class="form" method="POST">
    <h2>Reset Password</h2>
    <input type="text" name="code" placeholder="Enter code" required>
    <input type="password" name="new_password" placeholder="New Password" required>
    <button type="submit">Reset Password</button>
    <?php
    if(isset($success)) echo "<p style='color:green;'>$success</p>";
    if(isset($error)) echo "<p style='color:red;'>$error</p>";
    ?>
</form>
</body>
</html>
