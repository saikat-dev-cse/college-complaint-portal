<?php
session_start();
require '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: index.php"); exit(); }

$message = "";

// Check if this admin is the Principal (Super Admin)
$isSuperAdmin = (isset($_SESSION['email']) && $_SESSION['email'] === 'principal@gcekjr.ac.in');

// Dynamic Logo Logic
$customLogo = 'uploads/site_logo.png';
$defaultLogo = 'assets/images/logo.png';
$displayLogo = file_exists($customLogo) ? $customLogo . '?v=' . filemtime($customLogo) : $defaultLogo;

// Resolve Complaint
if (isset($_GET['resolve_id'])) {
    $conn->query("UPDATE complaints SET status='Resolved' WHERE id=".intval($_GET['resolve_id']));
    header("Location: admin.php"); exit();
}

// Add New Admin
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_admin'])) {
    $adminName = $conn->real_escape_string($_POST['adminName']); $empId = $conn->real_escape_string($_POST['empId']); 
    $email = $conn->real_escape_string($_POST['adminEmail']); $password = password_hash($_POST['adminPass'], PASSWORD_DEFAULT);
    if (!str_ends_with($email, '@gcekjr.ac.in')) {
        $message = "<script>document.addEventListener('DOMContentLoaded', function() { Swal.fire({icon: 'error', title: 'Invalid Email'}); });</script>";
    } else {
        if ($conn->query("INSERT INTO users (role, full_name, reg_no, email, password) VALUES ('admin', '$adminName', '$empId', '$email', '$password')") === TRUE) {
            $message = "<script>document.addEventListener('DOMContentLoaded', function() { Swal.fire({icon: 'success', title: 'Admin Created!'}); });</script>";
        } else { $message = "<script>document.addEventListener('DOMContentLoaded', function() { Swal.fire({icon: 'error', title: 'Error', text: 'Already exists!'}); });</script>"; }
    }
}

// Super Admin Update Logo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_logo']) && $isSuperAdmin) {
    if (isset($_FILES['newLogo']) && $_FILES['newLogo']['error'] == 0) {
        $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
        if (in_array($_FILES['newLogo']['type'], $allowedTypes)) {
            move_uploaded_file($_FILES['newLogo']['tmp_name'], $customLogo);
            $message = "<script>document.addEventListener('DOMContentLoaded', function() { Swal.fire({icon: 'success', title: 'Logo Updated!', text: 'The global website logo has been changed.'}).then(() => { window.location.href = 'admin.php'; }); });</script>";
        } else {
            $message = "<script>document.addEventListener('DOMContentLoaded', function() { Swal.fire({icon: 'error', title: 'Invalid Format', text: 'Please upload a PNG or JPG file.'}); });</script>";
        }
    }
}

$total = $conn->query("SELECT COUNT(*) as count FROM complaints")->fetch_assoc()['count'];
$pending = $conn->query("SELECT COUNT(*) as count FROM complaints WHERE status='Pending'")->fetch_assoc()['count'];
$resolved = $conn->query("SELECT COUNT(*) as count FROM complaints WHERE status='Resolved'")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .analytics-panel { display: flex; gap: 20px; align-items: center; background: var(--glass-bg); padding: 20px; border-radius: 12px; box-shadow: var(--shadow); margin-bottom: 20px;}
        .stats-grid { flex: 1; display: flex; flex-direction: column; gap: 10px; }
        @media print { .sidebar, .controls-bar, .admin-header, .analytics-panel { display: none; } body { background: white; color: black; } }
    </style>
</head>
<body class="admin-layout">
    <?php echo $message; ?>
    <aside class="sidebar">
        <div class="logo sidebar-logo"><img src="<?php echo $displayLogo; ?>" alt="Logo"><div class="sidebar-text">GCE Keonjhar<br><span>Government College</span></div></div>
        <ul class="nav-links">
            <li id="tab-complaints" class="active" onclick="switchTab('complaints')">📊 All Complaints</li>
            <li id="tab-admin" onclick="switchTab('admin')">👨‍💼 Add Admin</li>
            
            <?php if ($isSuperAdmin): ?>
            <li id="tab-settings" onclick="switchTab('settings')" style="background: rgba(234, 179, 8, 0.1); color: #eab308;">⚙️ Site Settings</li>
            <?php endif; ?>

            <li><a href="logout.php" style="color:white; text-decoration:none; display:block;" class="logout mobile-logout">🚪 Logout</a></li>
        </ul>
    </aside>

    <main class="main-content fade-in">
        <header class="admin-header">
            <h2 id="page-title">Admin Dashboard</h2>
            <div style="display: flex; align-items: center; gap: 15px;">
                <button class="btn-outline btn-small" onclick="window.print()">🖨️ Print Report</button>
                <button id="themeBtn" onclick="toggleTheme()" class="theme-toggle" style="background: var(--glass-bg); padding: 5px 10px; border-radius: 8px;">🌙</button>
                <div class="admin-profile">
                    <?php echo $isSuperAdmin ? '👑 Principal' : '👤 Admin'; ?>: <?php echo $_SESSION['full_name']; ?>
                </div>
            </div>
        </header>

        <div id="section-complaints">
            <div class="analytics-panel">
                <div class="stats-grid">
                    <div class="stat-card" style="padding:15px;">Total: <strong><?php echo $total; ?></strong></div>
                    <div class="stat-card pending" style="padding:15px;">Pending: <strong><?php echo $pending; ?></strong></div>
                    <div class="stat-card resolved" style="padding:15px;">Resolved: <strong><?php echo $resolved; ?></strong></div>
                </div>
                <div style="width: 150px; height: 150px;"><canvas id="statusChart"></canvas></div>
            </div>

            <div class="admin-table-container glass-card mb-40">
                <div class="controls-bar">
                    <input type="text" id="searchInput" placeholder="🔍 Search Name or Reg No..." onkeyup="filterLiveTable()">
                    <select id="filterStatus" onchange="filterLiveTable()"><option value="All">All Status</option><option value="Pending">Pending</option><option value="Resolved">Resolved</option></select>
                </div>
                <div class="table-responsive mt-20">
                    <table class="modern-table" id="complaintsTable">
                        <thead><tr><th>Student Info</th><th>Category</th><th>Details</th><th>Status</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php
                            $result = $conn->query("SELECT * FROM complaints ORDER BY created_at DESC");
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    $sClass = ($row['status'] == 'Pending') ? 'badge-pending' : 'badge-resolved';
                                    $att = ($row['file_name'] !== "None") ? "<br><a href='uploads/{$row['file_name']}' download class='attachment-badge' style='margin-top:8px;'>⬇️ Proof</a>" : "";
                                    $btn = ($row['status'] == 'Pending') ? "<a href='admin.php?resolve_id={$row['id']}' class='btn-primary btn-small' style='text-decoration:none;'>Resolve</a>" : "<span style='color:#166534; font-weight:bold;'>✔ Done</span>";
                                    $pStyle = ($row['priority'] == 'High') ? "color:red; font-weight:bold;" : "color:var(--text-light);";

                                    echo "<tr class='complaint-row' data-status='{$row['status']}'>
                                        <td><strong>{$row['name']}</strong><br><small class='searchable-text'>{$row['reg_no']}</small><br><small style='color:var(--text-light);'>{$row['branch']} ({$row['study_year']})</small></td>
                                        <td>{$row['category']}<br><small style='$pStyle'>Urgency: {$row['priority']}</small></td>
                                        <td style='max-width: 250px;'>{$row['description']}$att</td>
                                        <td><span class='c-badge $sClass'>{$row['status']}</span></td>
                                        <td>$btn</td>
                                    </tr>";
                                }
                            } else { echo "<tr><td colspan='5' align='center'>No complaints found.</td></tr>"; }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="section-admin" style="display: none;">
            <div class="glass-card fade-in" style="max-width: 600px; margin: 0 auto;">
                <h3 style="color: var(--primary);">🛡️ Register Admin</h3>
                <form method="POST" action=""><input type="hidden" name="add_admin" value="1">
                    <div class="input-grid"><div><label>Name</label><input type="text" name="adminName" required></div><div><label>Emp ID</label><input type="text" name="empId" required></div></div>
                    <label>Email</label><input type="email" name="adminEmail" placeholder="@gcekjr.ac.in" required>
                    <label>Password</label><input type="password" name="adminPass" required>
                    <button type="submit" class="btn-primary w-100 mt-20">Create</button>
                </form>
            </div>
        </div>

        <?php if ($isSuperAdmin): ?>
        <div id="section-settings" style="display: none;">
            <div class="glass-card fade-in" style="max-width: 600px; margin: 0 auto; border-top: 5px solid #eab308;">
                <h3 style="color: #ca8a04;">👑 Super Admin Settings</h3>
                <p style="color: var(--text-light); margin-bottom: 20px;">Upload a new image here to change the global website logo for all users instantly.</p>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="update_logo" value="1">
                    <label>Upload New Logo (PNG or JPG)</label>
                    <input type="file" name="newLogo" accept="image/png, image/jpeg" required>
                    <button type="submit" class="btn-primary w-100 mt-20" style="background: #ca8a04;">Update Global Logo</button>
                </form>
            </div>
        </div>
        <?php endif; ?>

    </main>

    <script src="assets/js/script.js"></script>
    <script>
        new Chart(document.getElementById('statusChart').getContext('2d'), { type: 'doughnut', data: { labels: ['Pending', 'Resolved'], datasets: [{ data: [<?php echo $pending; ?>, <?php echo $resolved; ?>], backgroundColor: ['#eab308', '#22c55e'], borderWidth: 0 }] }, options: { cutout: '70%', plugins: { legend: { display: false } } } });
        
        function switchTab(t) { 
            ['complaints','admin', 'settings'].forEach(x => { 
                let sec = document.getElementById('section-'+x);
                let tab = document.getElementById('tab-'+x);
                if(sec && tab) {
                    sec.style.display = (x===t)?'block':'none'; 
                    tab.classList[(x===t)?'add':'remove']('active'); 
                }
            }); 
            
            let titles = { 'complaints': 'Admin Dashboard', 'admin': 'Admin Management', 'settings': 'Site Settings' };
            document.getElementById('page-title').innerText = titles[t]; 
        }
        function filterLiveTable() { let s=document.getElementById("searchInput").value.toLowerCase(), st=document.getElementById("filterStatus").value; document.querySelectorAll(".complaint-row").forEach(r => { let match = r.innerText.toLowerCase().includes(s) && (st==="All" || r.getAttribute("data-status")===st); r.style.display = match ? "" : "none"; }); }
    </script>
</body>
</html>
