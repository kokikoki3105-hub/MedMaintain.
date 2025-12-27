<?php
ob_start();
require_once "config.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$device_id = $_GET['id'];

/* Fetch current device data */
$stmt = $conn->prepare("SELECT * FROM devices WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $device_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$device = $result->fetch_assoc();

if(!$device) {
    die("Unauthorized access or device not found.");
}

/* Handle Update Logic */
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_device'])){
    $stmt = $conn->prepare("UPDATE devices SET device_name=?, device_type=?, department=?, usage_hours=?, last_maintenance=?, priority=?, status=? WHERE id=? AND user_id=?");
    $stmt->bind_param("sssisssii", $_POST['d_name'], $_POST['d_type'], $_POST['dept'], $_POST['hours'], $_POST['last_m'], $_POST['prio'], $_POST['status'], $device_id, $user_id);
    
    if($stmt->execute()){
        header("Location: devices.php?msg=updated");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Asset | MedMaintain</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&family=Orbitron:wght@500&display=swap" rel="stylesheet">
    <style>
        :root {
            --main-bg: #0f172a;
            --glass: rgba(30, 41, 59, 0.7);
            --accent: #38bdf8;
            --text-light: #f1f5f9;
        }

        body {
            margin: 0; font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at top right, #1e293b, #0f172a);
            color: var(--text-light);
            display: flex; justify-content: center; align-items: center;
            min-height: 100vh;
        }

        .edit-container {
            background: var(--glass);
            backdrop-filter: blur(20px);
            padding: 40px;
            border-radius: 24px;
            width: 100%;
            max-width: 500px;
            border: 1px solid rgba(255,255,255,0.1);
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
        }

        h2 { font-family: 'Orbitron'; color: var(--accent); margin-top: 0; margin-bottom: 30px; text-align: center; }

        label { display: block; margin-bottom: 8px; font-size: 0.85rem; color: #94a3b8; }

        input, select {
            width: 100%; padding: 12px; margin-bottom: 20px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            color: white; border-radius: 12px;
            box-sizing: border-box; outline: none;
        }

        input:focus { border-color: var(--accent); }

        .btn-update {
            background: var(--accent); color: #000;
            border: none; padding: 15px; border-radius: 12px;
            font-weight: 600; cursor: pointer; width: 100%;
            transition: 0.3s; font-family: 'Orbitron';
        }

        .btn-update:hover { box-shadow: 0 0 20px rgba(56, 189, 248, 0.4); }

        .back-link {
            display: block; text-align: center; margin-top: 20px;
            color: #94a3b8; text-decoration: none; font-size: 0.9rem;
        }
    </style>
</head>
<body>

    <div class="edit-container">
        <h2>Update Asset Data</h2>
        <form method="POST">
            <label>Device Name</label>
            <input type="text" name="d_name" value="<?php echo $device['device_name']; ?>" required>

            <label>Asset Type</label>
            <input type="text" name="d_type" value="<?php echo $device['device_type']; ?>">

            <label>Department</label>
            <input type="text" name="dept" value="<?php echo $device['department']; ?>">

            <div style="display: flex; gap: 15px;">
                <div style="flex: 1;">
                    <label>Runtime (Hours)</label>
                    <input type="number" name="hours" value="<?php echo $device['usage_hours']; ?>">
                </div>
                <div style="flex: 1;">
                    <label>Last Maintenance</label>
                    <input type="date" name="last_m" value="<?php echo $device['last_maintenance']; ?>">
                </div>
            </div>

            <label>Priority Level</label>
            <select name="prio">
                <option value="Normal" <?php if($device['priority'] == 'Normal') echo 'selected'; ?>>Normal</option>
                <option value="Urgent" <?php if($device['priority'] == 'Urgent') echo 'selected'; ?>>Urgent</option>
                <option value="Critical" <?php if($device['priority'] == 'Critical') echo 'selected'; ?>>Critical</option>
            </select>

            <label>System Status</label>
            <select name="status">
                <option value="Operational" <?php if($device['status'] == 'Operational') echo 'selected'; ?>>Operational</option>
                <option value="Under Repair" <?php if($device['status'] == 'Under Repair') echo 'selected'; ?>>Under Repair</option>
                <option value="Broken" <?php if($device['status'] == 'Broken') echo 'selected'; ?>>Broken</option>
            </select>

            <button type="submit" name="update_device" class="btn-update">Update System</button>
            <a href="devices.php" class="back-link">Cancel and Return</a>
        </form>
    </div>

</body>
</html>