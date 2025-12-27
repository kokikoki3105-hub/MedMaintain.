<?php
ob_start();
require_once "config.php";

/* Security Check: Redirect if session is not set */
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* --- FETCH MAINTENANCE HISTORY DATA --- */
/* SQL Query joining devices and maintenance tables to fetch history */
$history_query = $conn->query("
    SELECT pm.*, d.device_name, d.department 
    FROM preventive_maintenance pm 
    JOIN devices d ON pm.device_id = d.id 
    WHERE d.user_id = $user_id 
    ORDER BY pm.last_maintenance_date DESC
");

/* Error handling to prevent Fatal Error if query fails */
if (!$history_query) {
    $error_db = "Database error: " . $conn->error;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance History | Archive</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&family=Orbitron:wght@500&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

    <style>
        :root {
            --main-bg: #0f172a;
            --glass: rgba(30, 41, 59, 0.7);
            --accent: #38bdf8;
            --text-light: #f1f5f9;
            --neon-green: #4ade80;
        }

        body {
            margin: 0; 
            font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at top right, #1e293b, #0f172a);
            color: var(--text-light);
            padding: 40px;
            min-height: 100vh;
        }

        .header-section {
            margin-bottom: 40px;
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--glass);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .history-table th, .history-table td {
            padding: 20px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .history-table th {
            background: rgba(56, 189, 248, 0.1);
            color: var(--accent);
            font-family: 'Orbitron', sans-serif;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.75rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--neon-green);
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--accent);
            text-decoration: none;
            margin-top: 30px;
            transition: 0.3s;
        }

        .back-btn:hover {
            opacity: 0.7;
        }
    </style>
</head>
<body>

    <div class="header-section">
        <h1 style="font-family: 'Orbitron', sans-serif; font-size: 1.8rem; margin: 0; color: var(--accent);">Maintenance History</h1>
        <p style="color: #94a3b8;">Full archive of device maintenance logs and service records.</p>
    </div>

    <?php if(isset($error_db)): ?>
        <div style="color: #f87171; background: rgba(248, 113, 113, 0.1); padding: 25px; border-radius: 15px; border: 1px solid #f87171;">
            <i class="fas fa-exclamation-circle"></i> <strong>SQL Error:</strong> <?php echo $error_db; ?>
        </div>
    <?php else: ?>
        <table class="history-table">
            <thead>
                <tr>
                    <th>Device Name</th>
                    <th>Department</th>
                    <th>Last Maintenance</th>
                    <th>Next Schedule</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if($history_query->num_rows > 0): ?>
                    <?php while($row = $history_query->fetch_assoc()): ?>
                    <tr>
                        <td style="font-weight: 600;"><?php echo $row['device_name']; ?></td>
                        <td><?php echo $row['department']; ?></td>
                        <td><?php echo date('M d, Y', strtotime($row['last_maintenance_date'])); ?></td>
                        <td><?php echo date('M d, Y', strtotime($row['next_maintenance_date'])); ?></td>
                        <td><span class="status-badge"><?php echo $row['status']; ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 50px; color: #64748b;">
                            <i class="fas fa-folder-open" style="font-size: 2rem; display: block; margin-bottom: 10px;"></i>
                            No maintenance logs found in the archive.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <a href="devices.php" class="back-btn">
        <i class="fas fa-arrow-left"></i> Return to Dashboard
    </a>

</body>
</html>