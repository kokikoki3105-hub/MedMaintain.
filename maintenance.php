<?php
require "config.php";
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success = $error = "";

/* Handle Scheduling Preventive Maintenance */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['schedule_m'])) {
    $device_id = $_POST['device_id'];
    $last_date = $_POST['last_maintenance_date'];
    $months = $_POST['frequency_months'];

    $next_date = date('Y-m-d', strtotime("+$months months", strtotime($last_date)));
    $status = "Planned";

    $stmt = $conn->prepare("INSERT INTO preventive_maintenance (device_id, last_maintenance_date, frequency_months, next_maintenance_date, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isiss", $device_id, $last_date, $months, $next_date, $status);
    
    if($stmt->execute()) $success = "Maintenance scheduled successfully!";
    else $error = "Execution error!";
}

/* Fetch Devices for the dropdown */
$devices_list = $conn->query("SELECT id, device_name FROM devices WHERE user_id=$user_id");

/* Fetch Combined Records (Preventive + Logs) */
$maintenance_records = $conn->query(
    "SELECT pm.*, d.device_name FROM preventive_maintenance pm 
     JOIN devices d ON pm.device_id=d.id 
     WHERE d.user_id=$user_id ORDER BY pm.next_maintenance_date ASC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Maintenance Hub | MedMaintain</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&family=Orbitron:wght@500&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
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
            color: var(--text-light); display: flex; min-height: 100vh;
        }

        .sidebar {
            width: 260px; background: var(--glass); backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255,255,255,0.1); padding: 30px 20px; position: fixed; height: 100vh;
        }

        .main-content { margin-left: 260px; flex: 1; padding: 40px; }

        .glass-card {
            background: var(--glass); border-radius: 24px; padding: 30px;
            border: 1px solid rgba(255,255,255,0.05); margin-bottom: 30px;
        }

        input, select, button {
            width: 100%; padding: 12px; margin-bottom: 15px;
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
            color: white; border-radius: 10px; outline: none;
        }

        button { background: var(--accent); color: #000; font-weight: 600; cursor: pointer; border: none; }

        .record-item {
            display: flex; justify-content: space-between; align-items: center;
            padding: 15px; background: rgba(255,255,255,0.02);
            border-radius: 12px; margin-bottom: 10px; border-left: 4px solid var(--accent);
        }

        .status-badge { background: rgba(56, 189, 248, 0.1); color: var(--accent); padding: 4px 10px; border-radius: 8px; font-size: 0.75rem; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div style="font-family:'Orbitron'; color:var(--accent); font-size:1.2rem; margin-bottom:50px;">MEDMAINTAIN</div>
        <nav>
            <a href="devices.php" style="color:#94a3b8; text-decoration:none; display:block; padding:10px 0;"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="maintenance.php" style="color:var(--accent); text-decoration:none; display:block; padding:10px 0;"><i class="fas fa-tools"></i> Maintenance</a>
            <a href="logout.php" style="color:#f87171; text-decoration:none; display:block; padding:10px 0; margin-top:20px;"><i class="fas fa-power-off"></i> Logout</a>
        </nav>
    </aside>

    <main class="main-content">
        <header style="margin-bottom: 30px;">
            <h1 style="font-family:'Orbitron'; font-size: 1.5rem;">Maintenance Scheduling</h1>
            <p style="color:#94a3b8;">Plan preventive maintenance cycles for your medical assets.</p>
        </header>

        <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 30px;">
            <div class="glass-card">
                <h3 style="margin-top:0;">Schedule New Cycle</h3>
                <form method="POST">
                    <select name="device_id" required>
                        <option value="">Select Asset</option>
                        <?php while($d = $devices_list->fetch_assoc()): ?>
                            <option value="<?php echo $d['id']; ?>"><?php echo $d['device_name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                    <label style="font-size: 0.8rem; color:#94a3b8;">Last Maintenance Date:</label>
                    <input type="date" name="last_maintenance_date" required>
                    <input type="number" name="frequency_months" placeholder="Frequency (Interval in Months)" required>
                    <button type="submit" name="schedule_m">Create Schedule</button>
                </form>
                <?php if($success) echo "<p style='color:#4ade80;'>$success</p>"; ?>
            </div>

            <div class="glass-card">
                <h3 style="margin-top:0;">Planned Tasks</h3>
                <div style="max-height: 400px; overflow-y: auto;">
                    <?php if($maintenance_records->num_rows > 0): ?>
                        <?php while($record = $maintenance_records->fetch_assoc()): ?>
                            <div class="record-item">
                                <div>
                                    <div style="font-weight:600;"><?php echo $record['device_name']; ?></div>
                                    <div style="font-size:0.75rem; color:#94a3b8;">Next Date: <span style="color:#f1f5f9;"><?php echo $record['next_maintenance_date']; ?></span></div>
                                </div>
                                <span class="status-badge"><?php echo $record['status']; ?></span>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="color:#64748b; text-align:center;">No upcoming schedules found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

</body>
</html>