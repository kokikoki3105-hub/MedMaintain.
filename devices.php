<?php
/* --- ADDED: Persistent Session (30 Days) --- */
ini_set('session.gc_maxlifetime', 86400 * 30);
session_set_cookie_params(86400 * 30);

ob_start();
require_once "config.php";

/* Security Check: Redirect if not logged in */
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success_msg = "";

/* --- FETCH USER DATA --- */
$user_res = $conn->query("SELECT full_name FROM users WHERE id = $user_id");
$user_data = $user_res ? $user_res->fetch_assoc() : null;
$current_username = $user_data['full_name'] ?? 'User';

/* --- FETCH DATA FOR THE CHART --- */
$chart_query = $conn->query("SELECT status, COUNT(*) as count FROM devices WHERE user_id=$user_id GROUP BY status");
$chart_labels = [];
$chart_counts = [];
if($chart_query) {
    while($row = $chart_query->fetch_assoc()){
        $chart_labels[] = $row['status'];
        $chart_counts[] = $row['count'];
    }
}

/* --- FETCH UPCOMING MAINTENANCE --- */
$upcoming_maint = $conn->query("
    SELECT pm.*, d.device_name 
    FROM preventive_maintenance pm 
    JOIN devices d ON pm.device_id = d.id 
    WHERE d.user_id = $user_id 
    AND pm.next_maintenance_date <= DATE_ADD(CURDATE(), INTERVAL 10 DAY)
    AND pm.status != 'Completed'
    ORDER BY pm.next_maintenance_date ASC
");

/* Handle Device Registration */
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_device'])){
    $stmt = $conn->prepare("INSERT INTO devices (user_id, device_name, device_type, department, usage_hours, last_maintenance, priority, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssisss", $user_id, $_POST['d_name'], $_POST['d_type'], $_POST['dept'], $_POST['hours'], $_POST['last_m'], $_POST['prio'], $_POST['status']);
    
    if($stmt->execute()) {
        header("Location: devices.php");
        exit;
    }
}

/* Fetch Devices */
$devices = $conn->query("SELECT * FROM devices WHERE user_id=$user_id ORDER BY id DESC");
$total_devices = ($devices) ? $devices->num_rows : 0;
$needs_service_query = $conn->query("SELECT id FROM devices WHERE user_id=$user_id AND (status='Broken' OR status='Under Repair')");
$needs_service_count = ($needs_service_query) ? $needs_service_query->num_rows : 0;
$alerts = $conn->query("SELECT device_name FROM devices WHERE user_id=$user_id AND status='Broken' LIMIT 3");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Assets | Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&family=Orbitron:wght@500&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --main-bg: #0f172a;
            --glass: rgba(30, 41, 59, 0.7);
            --accent: #38bdf8;
            --neon-green: #4ade80;
            --text-light: #f1f5f9;
            --status-op: #4ade80; 
            --status-rep: #fbbf24;
            --status-brk: #f87171;
        }

        body {
            margin: 0; font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at top right, #1e293b, #0f172a);
            color: var(--text-light); display: flex; min-height: 100vh;
        }

        .alert-banner {
            background: rgba(248, 113, 113, 0.1); border: 1px solid var(--status-brk);
            color: var(--status-brk); padding: 15px 25px; border-radius: 12px;
            margin-bottom: 30px; display: flex; align-items: center; gap: 15px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(248, 113, 113, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(248, 113, 113, 0); }
            100% { box-shadow: 0 0 0 0 rgba(248, 113, 113, 0); }
        }

        .sidebar {
            width: 260px; background: var(--glass); backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255,255,255,0.1); display: flex;
            flex-direction: column; padding: 30px 20px; position: fixed; height: 100vh;
        }

        .brand { font-family: 'Orbitron', sans-serif; font-size: 1.2rem; color: var(--accent); margin-bottom: 30px; display: flex; align-items: center; gap: 10px; }
        
        .user-box { 
            background: rgba(255,255,255,0.05); padding: 12px; border-radius: 12px; 
            margin-bottom: 25px; display: flex; align-items: center; gap: 10px; border: 1px solid rgba(255,255,255,0.1);
        }
        .user-initial { width: 35px; height: 35px; background: var(--accent); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #000; font-weight: 600; }

        .nav-menu { list-style: none; padding: 0; flex-grow: 1; }
        .nav-item { margin-bottom: 10px; }
        .nav-link {
            text-decoration: none; color: #94a3b8; padding: 12px 15px;
            border-radius: 12px; display: flex; align-items: center; gap: 12px; transition: 0.3s;
        }
        .nav-link.active { background: rgba(56, 189, 248, 0.1); color: var(--accent); }

        .main-content { margin-left: 260px; flex: 1; padding: 40px; }
        .top-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 25px; margin-bottom: 40px; }
        .stat-card { background: var(--glass); padding: 25px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.05); transition: 0.3s; }
        .stat-card h4 { margin: 0; color: #94a3b8; font-size: 0.9rem; }
        .stat-card .value { font-size: 2rem; font-weight: 600; margin-top: 10px; }
        
        .inventory-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .btn-add { background: var(--accent); color: #000; border: none; padding: 12px 25px; border-radius: 12px; font-weight: 600; cursor: pointer; transition: 0.3s; }
        
        .search-box { width: 100%; max-width: 400px; margin-bottom: 25px; position: relative; }
        .search-box i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
        .search-box input { padding-left: 45px !important; width: 100%; padding: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: white; border-radius: 10px; outline: none; }

        .device-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px; }
        .device-node {
            background: var(--glass); border-radius: 24px; padding: 25px;
            position: relative; overflow: hidden; border: 1px solid rgba(255,255,255,0.05);
            transition: transform 0.3s ease;
        }
        .device-node:hover { transform: translateY(-5px); }
        .device-node::before { content: ''; position: absolute; top: 0; left: 0; width: 5px; height: 100%; background: var(--accent); }
        .node-Operational::before { background: var(--status-op); }
        .node-Under-Repair::before { background: var(--status-rep); }
        .node-Broken::before { background: var(--status-brk); }

        .node-prio { float: right; font-size: 0.7rem; padding: 4px 12px; border-radius: 20px; background: rgba(255,255,255,0.1); text-transform: uppercase; }
        .prio-Critical { color: #f87171; background: rgba(248, 113, 113, 0.1); }

        .node-actions { margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255, 255, 255, 0.1); display: flex; justify-content: flex-end; gap: 15px; }
        .action-link { text-decoration: none; font-size: 0.9rem; transition: 0.3s; }
        .edit-btn { color: var(--accent); }
        .delete-btn { color: var(--status-brk); }

        #addModal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); backdrop-filter: blur(5px); z-index: 1000; justify-content: center; align-items: center; }
        .modal-content { background: #1e293b; padding: 40px; border-radius: 24px; width: 500px; border: 1px solid var(--accent); }

        input, select { width: 100%; padding: 12px; margin-bottom: 15px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: white; border-radius: 10px; outline: none; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="brand">
            <div style="width:30px; height:30px; background:var(--accent); border-radius:8px;"></div>
            MedMaintain
        </div>

        <div class="user-box">
            <div class="user-initial"><?php echo strtoupper(substr($current_username, 0, 1)); ?></div>
            <div style="font-size: 0.85rem;">
                <div style="font-weight: 600;"><?php echo $current_username; ?></div>
                <div style="color: var(--neon-green); font-size: 0.7rem;">Active Now</div>
            </div>
        </div>

        <ul class="nav-menu">
            <li class="nav-item"><a href="devices.php" class="nav-link active"><i class="fas fa-th-large"></i> Dashboard</a></li>
            <li class="nav-item"><a href="maintenance.php" class="nav-link"><i class="fas fa-tools"></i> Maintenance</a></li>
            <li style="margin-top: 20px; padding-left: 15px; font-size: 0.65rem; color: #64748b; text-transform: uppercase; letter-spacing: 1px;">Archive</li>
            <li class="nav-item"><a href="maintenance_history.php" class="nav-link"><i class="fas fa-history"></i> History Logs</a></li>
        </ul>

        <div style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px;">
            <a href="logout.php" class="nav-link" style="color:#f87171;"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
        </div>
    </aside>

    <main class="main-content">
        <?php if($alerts && $alerts->num_rows > 0): ?>
            <div class="alert-banner">
                <i class="fas fa-exclamation-triangle"></i>
                <span>
                    <strong>System Alert:</strong> Critical issue detected in: 
                    <?php 
                        $names = [];
                        while($a = $alerts->fetch_assoc()) $names[] = $a['device_name'];
                        echo implode(", ", $names);
                    ?>
                </span>
            </div>
        <?php endif; ?>

        <header style="margin-bottom: 40px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="font-size: 1.8rem; margin:0;">System Overview</h1>
                <p style="color:#94a3b8; margin: 5px 0 0 0;">Welcome back, <strong><?php echo $current_username; ?></strong>. Monitor asset status in real-time.</p>
            </div>
            <a href="logout.php" style="text-decoration: none; display: flex; align-items: center; gap: 10px; background: rgba(248, 113, 113, 0.1); color: #f87171; padding: 10px 20px; border-radius: 12px; border: 1px solid rgba(248, 113, 113, 0.2); transition: 0.3s; font-weight: 600;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </header>

        <?php if($upcoming_maint && $upcoming_maint->num_rows > 0): ?>
            <div style="margin-bottom: 30px;">
                <h4 style="font-family:'Orbitron'; font-size: 0.8rem; color: var(--status-rep); margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-calendar-check"></i> UPCOMING PREVENTIVE MAINTENANCE
                </h4>
                <div style="display: flex; gap: 15px; overflow-x: auto; padding-bottom: 10px;">
                    <?php while($m = $upcoming_maint->fetch_assoc()): ?>
                        <div style="background: rgba(251, 191, 36, 0.05); border: 1px solid rgba(251, 191, 36, 0.3); padding: 15px 20px; border-radius: 18px; min-width: 260px; backdrop-filter: blur(10px);">
                            <div style="font-weight: 600; font-size: 0.9rem; color: var(--text-light);"><?php echo $m['device_name']; ?></div>
                            <div style="font-size: 0.8rem; color: #94a3b8; margin-top: 5px;">
                                Due: <span style="color: var(--status-rep); font-weight: 600;"><?php echo date('M d, Y', strtotime($m['next_maintenance_date'])); ?></span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>

        <section class="top-stats">
            <div class="stat-card" style="grid-column: span 2; display: flex; align-items: center; justify-content: space-between;">
                <div style="width: 45%;">
                    <h4 style="font-family: 'Orbitron'; color: var(--accent);">Health Distribution</h4>
                    <p style="font-size: 0.8rem; color: #94a3b8; margin-top: 10px;">Visual representation of asset health status.</p>
                </div>
                <div style="width: 50%; height: 160px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
            <div class="stat-card">
                <h4>Total Assets</h4>
                <div class="value"><?php echo $total_devices; ?></div>
            </div>
            <div class="stat-card">
                <h4>Needs Service</h4>
                <div class="value" style="color:var(--status-brk);"><?php echo $needs_service_count; ?></div>
            </div>
        </section>

        <div class="inventory-header">
            <h2 style="font-family:'Orbitron'; font-size:1.1rem;">Asset Inventory</h2>
            <button class="btn-add" onclick="toggleModal(true)">+ Add New Device</button>
        </div>

        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="assetSearch" placeholder="Search by name or department...">
        </div>

        <div class="device-grid" id="deviceGrid">
            <?php if($devices): ?>
                <?php while($row = $devices->fetch_assoc()): ?>
                <div class="device-node node-<?php echo str_replace(' ', '-', $row['status']); ?>">
                    <span class="node-prio prio-<?php echo $row['priority']; ?>"><?php echo $row['priority']; ?></span>
                    <div style="font-size: 0.65rem; color: var(--accent); margin-bottom: 5px; text-transform: uppercase;">● <?php echo $row['status']; ?></div>
                    <h3 style="margin:0 0 5px 0; font-size:1.1rem;"><?php echo $row['device_name']; ?></h3>
                    <p style="color:#94a3b8; font-size:0.85rem; margin-bottom:20px;"><?php echo $row['device_type']; ?> • <?php echo $row['department']; ?></p>
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <div style="font-size:0.75rem;">
                            <div style="color:#64748b;">Last Service</div>
                            <div><?php echo $row['last_maintenance']; ?></div>
                        </div>
                        <div style="text-align:right; font-size:0.75rem;">
                            <div style="color:#64748b;">Runtime</div>
                            <div><?php echo $row['usage_hours']; ?>h</div>
                        </div>
                    </div>
                    <div class="node-actions">
                        <a href="edit_device.php?id=<?php echo $row['id']; ?>" class="action-link edit-btn"><i class="fas fa-pen-nib"></i> Edit</a>
                        <a href="delete_device.php?id=<?php echo $row['id']; ?>" class="action-link delete-btn" onclick="return confirm('Delete?')"><i class="fas fa-trash-alt"></i> Delete</a>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </main>

    <div id="addModal">
        <div class="modal-content">
            <h2 style="margin-top:0; font-family:'Orbitron'; font-size: 1.2rem; color: var(--accent);">Register Device</h2>
            <form method="POST">
                <input type="text" name="d_name" placeholder="Device Name" required>
                <input type="text" name="d_type" placeholder="Type">
                <input type="text" name="dept" placeholder="Department">
                <input type="number" name="hours" placeholder="Hours">
                <input type="date" name="last_m">
                <select name="prio">
                    <option value="Normal">Normal</option>
                    <option value="Urgent">Urgent</option>
                    <option value="Critical">Critical</option>
                </select>
                <select name="status">
                    <option value="Operational">Operational</option>
                    <option value="Under Repair">Under Repair</option>
                    <option value="Broken">Broken</option>
                </select>
                <button type="submit" name="add_device" class="btn-add" style="width:100%;">Sync Asset</button>
                <button type="button" onclick="toggleModal(false)" style="width:100%; background:none; border:none; color:#94a3b8; margin-top:10px; cursor:pointer;">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('statusChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($chart_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($chart_counts); ?>,
                    backgroundColor: ['#4ade80', '#fbbf24', '#f87171'],
                    borderWidth: 0, hoverOffset: 15
                }]
            },
            options: {
                plugins: { legend: { position: 'right', labels: { color: '#94a3b8', font: { family: 'Outfit', size: 11 } } } },
                maintainAspectRatio: false, cutout: '75%'
            }
        });

        function toggleModal(show) { document.getElementById('addModal').style.display = show ? 'flex' : 'none'; }
        document.getElementById('assetSearch').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let nodes = document.getElementsByClassName('device-node');
            Array.from(nodes).forEach(function(node) {
                let text = node.innerText.toLowerCase();
                node.style.display = text.includes(filter) ? "" : "none";
            });
        });
    </script>
</body>
</html>