<?php
require_once "config.php";

/* Security Check: Ensure user is logged in */
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

if(isset($_GET['id'])){
    $device_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    /* Security: Ensure the device belongs to the logged-in user before deleting */
    $stmt = $conn->prepare("DELETE FROM devices WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $device_id, $user_id);
    
    if($stmt->execute()){
        header("Location: devices.php?msg=deleted");
    } else {
        echo "Error: Could not delete the asset.";
    }
}
?>