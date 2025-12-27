<?php
require "config.php";

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT user_id FROM user_profile WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id);
    $stmt->fetch();

    if($stmt->num_rows > 0){
        $code = rand(100000, 999999);
        $_SESSION['reset_code'] = $code;
        $_SESSION['reset_user'] = $user_id;
        $success = "Reset code sent to email (simulate): $code";
    } else {
        $error = "Email not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Forgot Password | MedMaintain</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="login-bg">
<form class="form" method="POST">
    <h2>Forgot Password</h2>
    <input type="email" name="email" placeholder="Enter your email" required>
    <button type="submit">Send Code</button>
    <?php
    if(isset($success)) echo "<p style='color:green;'>$success</p>";
    if(isset($error)) echo "<p style='color:red;'>$error</p>";
    ?>
</form>
</body>
</html>
