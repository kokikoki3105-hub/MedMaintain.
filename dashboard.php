<?php
require "config.php";
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$profile = $conn->query("SELECT * FROM user_profile WHERE user_id=".$_SESSION['user_id'])->fetch_assoc();
$devices = $conn->query("SELECT * FROM devices WHERE user_id=".$_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | MedMaintain</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="header">
    <div class="logo"><span>MedMaintain</span></div>
    <nav class="navbar">
        <a href="devices.php">Add Device</a>
        <a href="maintenance.php">Maintenance</a>
        <span class="toggle-btn" onclick="toggleTheme()">🌙</span>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<section class="home">
    <div class="home-content glow-box">
        <h1>Welcome, <?php echo $profile['full_name']; ?></h1>
        <p>Track medical devices and schedule preventive maintenance.</p>
        <h2>Your Devices:</h2>
        <ul>
            <?php while($device = $devices->fetch_assoc()) {
                echo "<li>".$device['device_name']." (".$device['service'].")</li>";
            } ?>
        </ul>
    </div>
</section>
<script src="script.js"></script>
</body>
</html>
