<?php
ob_start();
require_once "config.php";

if(!isset($_SESSION['user_id'])){ header("Location: login.php"); exit; }

$user_id = $_SESSION['user_id'];
$error = "";

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $full_name = trim($_POST['full_name']);
    $specialization = ($_POST['specialization'] == 'Other') ? trim($_POST['custom_spec']) : trim($_POST['specialization']);
    $hospital_name = trim($_POST['hospital_name']);
    $phone = $_POST['country_code'] . " " . trim($_POST['phone']);

    // تحديث جدول users
    $stmt = $conn->prepare("UPDATE users SET full_name=?, phone=?, specialization=?, hospital_name=? WHERE id=?");
    $stmt->bind_param("ssssi", $full_name, $phone, $specialization, $hospital_name, $user_id);
    
    if($stmt->execute()){
        header("Location: devices.php");
        exit();
    } else {
        $error = "Execution Failure: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Identity Setup | MedMaintain</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { --bg-deep: #0a192f; --text-main: #ffffff; --neon-blue: #00fff2; --card-glass: rgba(10, 25, 47, 0.95); --select-bg: #112240; }
        body.light-mode { --bg-deep: #f0f4f8; --text-main: #1a2a3a; --neon-blue: #007bff; --card-glass: rgba(255, 255, 255, 0.98); --select-bg: #ffffff; }
        body, html { height: 100%; margin: 0; font-family: 'Poppins', sans-serif; background-color: var(--bg-deep); display: flex; justify-content: center; align-items: center; transition: 0.4s; }
        .profile-container { width: 500px; padding: 40px; background: var(--card-glass); border-radius: 10px; border: 1px solid var(--neon-blue); position: relative; }
        h2 { font-family: 'Orbitron', sans-serif; color: var(--neon-blue); font-size: 1.3rem; margin-bottom: 30px; letter-spacing: 2px; }
        .field-label { display: block; font-size: 10px; font-family: 'Orbitron', sans-serif; margin-bottom: 8px; color: var(--neon-blue); }
        input, select { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: var(--text-main); padding: 12px; border-radius: 4px; width: 100%; margin-bottom: 20px; box-sizing: border-box; }
        .submit-btn { width: 100%; padding: 15px; background: transparent; border: 1px solid var(--neon-blue); color: var(--neon-blue); font-family: 'Orbitron', sans-serif; cursor: pointer; transition: 0.3s; font-weight: bold; }
        .submit-btn:hover { background: var(--neon-blue); color: #0a192f; }
        #customInput { display: none; }
    </style>
</head>
<body id="pBody">
    <div class="profile-container">
        <div style="display:flex; justify-content:space-between;">
            <h2>IDENTITY_SETUP</h2>
            <span onclick="toggleTheme()" style="cursor:pointer;" id="tIcon">🌙</span>
        </div>
        <form method="POST">
            <label class="field-label">FULL_NAME</label>
            <input type="text" name="full_name" required>
            
            <label class="field-label">PROFESSIONAL_FIELD</label>
            <select name="specialization" id="specSelect" onchange="checkOther(this.value)" required>
                <option value="Biomedical Engineer">Biomedical Engineer</option>
                <option value="Medical Technician">Medical Equipment Technician</option>
                <option value="Other">Other</option>
            </select>
            <input type="text" name="custom_spec" id="customInput" placeholder="Specify field...">

            <label class="field-label">HOSPITAL_FACILITY</label>
            <input type="text" name="hospital_name">

            <label class="field-label">CONTACT_SECURE</label>
            <div style="display: grid; grid-template-columns: 110px 1fr; gap: 10px;">
                <select name="country_code">
                    <option value="+212">+212</option>
                </select>
                <input type="text" name="phone" required>
            </div>
            <button type="submit" class="submit-btn">AUTHORIZE_PROFILE</button>
        </form>
    </div>
    <script>
        function checkOther(val) { document.getElementById('customInput').style.display = (val === 'Other') ? 'block' : 'none'; }
        function toggleTheme() { document.body.classList.toggle('light-mode'); }
    </script>
</body>
</html>