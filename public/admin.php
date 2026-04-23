<?php
session_start();
require '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: index.php"); exit(); }

$message = "";
$isSuperAdmin = (isset($_SESSION['email']) && $_SESSION['email'] === 'principal@gcekjr.ac.in');

$customLogo = 'uploads/site_logo.png';
$displayLogo = file_exists($customLogo) ? $customLogo . '?v=' . filemtime($customLogo) : 'assets/images/logo.png';
$customNameFile = 'uploads/site_name.txt';
$displaySiteName = file_exists($customNameFile) ? htmlspecialchars(file_get_contents($customNameFile)) : 'Government College of Engineering, Keonjhar';

if (isset($_GET['resolve_id'])) {
    $conn->query("UPDATE complaints SET status='Resolved' WHERE id=".intval($_GET['resolve_id']));
    header("Location: admin.php"); exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_admin']) && $isSuperAdmin) {
    $adminName = $conn->real_escape_string($_POST['adminName']); $empId = $conn->real_escape_string($_POST['empId']); 
    $email = $conn->real_escape_string($_POST['adminEmail']); $password = password_hash($_POST['adminPass'], PASSWORD_DEFAULT);
    if (!str_ends_with($email, '@gcekjr.ac.in')) {
        $message = "<script>document.addEventListener('DOMContentLoaded', function() { Swal.fire({icon: 'error', title: 'Invalid Email'}); });</script>";
    } else {
        if ($conn->query("INSERT INTO users (role, full_name, reg_no, email, password) VALUES ('admin', '$adminName', '$empId', '$email', '$password')") === TRUE) {
            $message = "<script>document.addEventListener('DOMContentLoaded', function() { Swal.fire({icon: 'success', title: 'Staff Created!'}); });</script>";
        } else { $message = "<script>document.addEventListener('DOMContentLoaded', function() { Swal.fire({icon: 'error', title: 'Error', text: 'Email/ID already exists!'}); });</script>"; }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_settings']) && $isSuperAdmin) {
    if (isset($_FILES['newLogo']) && $_FILES['newLogo']['error'] == 0) {
        $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
        if (in_array($_FILES['newLogo']['type'], $allowedTypes)) { move_uploaded_file($_FILES['newLogo']['tmp_name'], $customLogo); }
    }
    if (!empty($_POST['newSiteName'])) { file_put_contents($customNameFile, strip_tags($_POST['newSiteName'])); }
    $message = "<script>document.addEventListener('DOMContentLoaded', function() { Swal.fire({icon: 'success', title: 'Platform Branding Updated!'}).then(() => { window.location.href = 'admin.php'; }); });</script>";
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
</head>
<body class="admin-layout">
    <?php echo $message; ?>
    
    <div id="sidebarOverlay" class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <aside id="mobileSidebar" class="sidebar">
        <div class="logo sidebar-logo"><img src="<?php echo $displayLogo; ?>" alt="Logo"><div class="sidebar-text"><?php echo $displaySiteName; ?></div></div>
        <ul class="nav-links">
            <li id="tab-complaints" class="active" onclick="switchTab('complaints'); toggleSidebar();">📊 All Complaints</li>
            <?php if ($isSuperAdmin): ?>
            <li id="tab-admin" onclick="switchTab('admin'); toggleSidebar();">👨‍💼 Add Staff</li>
            <li id="tab-settings" onclick="switchTab('settings'); toggleSidebar();" style="background: rgba(234, 179, 8, 0.1); color: #eab308; border: 1px solid rgba(234, 179, 8, 0.3);">⚙️ Site Settings</li>
            <?php endif; ?>
            <li class="logout"><a href="logout.php" style="color:inherit; display:block; width:100%;">🚪 Logout</a></li>
        </ul>
    </aside>

    <main class="main-content fade-in">
        <header class="admin-header">
            <div style="display: flex; align-items: center; gap: 15px;">
                <button class="hamburger-btn" onclick="toggleSidebar()">☰</button>
                <h2 id="page-title" style="margin: 0;">Admin Dashboard</h2>
            </div>
            <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                <button class="btn-outline btn-small" onclick="exportTableToCSV('complaints_report.csv')">📊 Export Excel</button>
                <button class="btn-outline btn-small" onclick="window.print()">🖨️ Print PDF</button>
                <button id="themeBtn" onclick="toggleTheme()" class="theme-toggle">🌙</button>
                <div class="admin-profile">Role: <?php echo $isSuperAdmin ? 'Principal' : 'Staff'; ?> (<?php echo $_SESSION['full_name']; ?>)</div>
            </div>
        </header>

        <div id="section-complaints">
            <div class="analytics-panel glass-card" style="padding: 20px; display:flex; gap:20px; align-items:center; flex-wrap: wrap;">
                <div class="stats-grid" style="flex:1; display:flex; flex-direction:column; gap:10px; min-width: 200px;">
                    <div class="stat-card" style="padding: 15px;">Total: <strong><?php echo $total; ?></strong></div>
                    <div class="stat-card pending" style="padding: 15px;">Pending: <strong><?php echo $pending; ?></strong></div>
                    <div class="stat-card resolved" style="padding: 15px;">Resolved: <strong><?php echo $resolved; ?></strong></div>
                </div>
                <div style="width: 150px; height: 150px; padding: 10px; margin: 0 auto;"><canvas id="statusChart"></canvas></div>
            </div>

            <div class="admin-table-container glass-card mb-40 mt-20">
                <div class="controls-bar">
                    <input type="text" id="searchInput" placeholder="🔍 Search Name or Reg No..." onkeyup="filterLiveTable()" style="flex:1;">
                    <select id="filterStatus" onchange="filterLiveTable()" style="flex:1;"><option value="All">All Grievances</option><option value="Pending">Pending</option><option value="Resolved">Resolved</option></select>
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
                                    $btn = ($row['status'] == 'Pending') ? "<a href='admin.php?resolve_id={$row['id']}' class='btn-primary btn-small'>Resolve</a>" : "<span style='color:#166534; font-weight:bold;'>✔ Resolved</span>";
                                    $pStyle = ($row['priority'] == 'High') ? "color:red; font-weight:bold;" : "color:var(--text-light);";

                                    echo "<tr class='complaint-row' data-status='{$row['status']}'>
                                        <td><strong>{$row['name']}</strong><br><small class='searchable-text'>{$row['reg_no']}</small><br><small style='color:var(--text-light);'>{$row['branch']} ({$row['study_year']})</small></td>
                                        <td>{$row['category']}<br><small style='$pStyle'>Urgency: {$row['priority']}</small></td>
                                        <td style='max-width: 280px; font-size: 13px;'>{$row['description']}$att</td>
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

        <?php if ($isSuperAdmin): ?>
        <div id="section-admin" style="display: none;">
            <div class="glass-card fade-in" style="max-width: 600px; margin: 0 auto;">
                <h3 style="color: var(--primary);">🛡️ Register New Staff</h3>
                <form method="POST" action=""><input type="hidden" name="add_admin" value="1">
                    <div class="input-grid">
                        <div><label>Staff Name</label><input type="text" name="adminName" placeholder="e.g., Amit Sharma" required></div>
                        <div><label>Emp ID</label><input type="text" name="empId" placeholder="e.g., EMP-204" required></div>
                    </div>
                    <label>College Email</label><input type="email" name="adminEmail" placeholder="e.g., amit.sharma@gcekjr.ac.in" required>
                    <label>Assign Password</label><input type="password" name="adminPass" placeholder="Create a strong password" required>
                    <button type="submit" class="btn-primary w-100 mt-20">Create Staff Account</button>
                </form>
            </div>
        </div>

        <div id="section-settings" style="display: none;">
            <div class="glass-card fade-in" style="max-width: 600px; margin: 0 auto; border-top: 5px solid #eab308;">
                <h3 style="color: #ca8a04; display: flex; align-items: center; gap: 10px;">⚙️ Global Site Settings</h3>
                <p style="color: var(--text-light); margin-bottom: 20px;">Upload a new image or change the college name to update the platform branding globally for all users instantly.</p>
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="update_settings" value="1">
                    <label>College/Site Name</label>
                    <input type="text" name="newSiteName" value="<?php echo $displaySiteName; ?>" placeholder="e.g., GCE Keonjhar" required>
                    <label style="margin-top: 15px;">Website Logo (Optional: PNG or JPG)</label>
                    <input type="file" name="newLogo" accept="image/png, image/jpeg">
                    <button type="submit" class="btn-primary w-100 mt-20" style="background: #ca8a04; border-color: #ca8a04;">Update Platform Branding</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <script src="assets/js/script.js"></script>
    <script>
        new Chart(document.getElementById('statusChart').getContext('2d'), { type: 'doughnut', data: { labels: ['Pending', 'Resolved'], datasets: [{ data: [<?php echo $pending; ?>, <?php echo $resolved; ?>], backgroundColor: ['#eab308', '#22c55e'], borderWidth: 0, hoverOffset: 4 }] }, options: { cutout: '75%', plugins: { legend: { display: false } } } });
        function switchTab(t) { 
            ['complaints','admin', 'settings'].forEach(x => { 
                let sec = document.getElementById('section-'+x); let tab = document.getElementById('tab-'+x);
                if(sec && tab) { sec.style.display = (x===t)?'block':'none'; tab.classList[(x===t)?'add':'remove']('active'); }
            }); 
            let titles = { 'complaints': 'All Grievances', 'admin': 'Staff Management', 'settings': 'Site Settings' };
            document.getElementById('page-title').innerText = titles[t]; 
        }
        function filterLiveTable() { let s=document.getElementById("searchInput").value.toLowerCase(), st=document.getElementById("filterStatus").value; document.querySelectorAll(".complaint-row").forEach(r => { let match = r.innerText.toLowerCase().includes(s) && (st==="All" || r.getAttribute("data-status")===st); r.style.display = match ? "" : "none"; }); }
        function exportTableToCSV(filename) {
            let csv = []; let rows = document.querySelectorAll("#complaintsTable tr");
            for (let i = 0; i < rows.length; i++) {
                let row = [], cols = rows[i].querySelectorAll("td, th");
                for (let j = 0; j < cols.length - 1; j++) { row.push('"' + cols[j].innerText.replace(/"/g, '""') + '"'); }
                csv.push(row.join(","));
            }
            let csvFile = new Blob([csv.join("\n")], {type: "text/csv"});
            let downloadLink = document.createElement("a"); downloadLink.download = filename; downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = "none"; document.body.appendChild(downloadLink); downloadLink.click();
        }
    </script>
</body>
</html>
