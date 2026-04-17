<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') { header("Location: index.php"); exit(); }

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_SESSION['full_name'];
    $regNo = $_SESSION['reg_no'];
    $branch = $conn->real_escape_string($_POST['sBranch']);
    $year = $conn->real_escape_string($_POST['sYear']);
    $category = $conn->real_escape_string($_POST['sCategory']);
    $priority = $conn->real_escape_string($_POST['sPriority']); 
    $desc = $conn->real_escape_string($_POST['sDesc']); 
    
    $fileName = "None";

    if (isset($_FILES['sFile']) && $_FILES['sFile']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $fileSize = $_FILES['sFile']['size'];
        if ($fileSize > 2 * 1024 * 1024) {
            $message = "<script>document.addEventListener('DOMContentLoaded', function() { Swal.fire({icon: 'error', title: 'File Too Large', text: 'Max 2MB.', confirmButtonColor: '#0f766e'}); });</script>";
        } else {
            $fileName = time() . "_" . basename($_FILES["sFile"]["name"]);
            move_uploaded_file($_FILES["sFile"]["tmp_name"], $target_dir . $fileName);
        }
    }

    if ($message === "") {
        $sql = "INSERT INTO complaints (reg_no, name, branch, study_year, category, priority, description, file_name) VALUES ('$regNo', '$name', '$branch', '$year', '$category', '$priority', '$desc', '$fileName')";
        if ($conn->query($sql) === TRUE) {
            $message = "<script>document.addEventListener('DOMContentLoaded', function() { Swal.fire({ icon: 'success', title: 'Complaint Submitted!', text: 'Your issue has been securely sent.', confirmButtonColor: '#0f766e' }).then(() => { window.location.href = 'student.php'; }); });</script>";
        } else {
            $message = "<script>document.addEventListener('DOMContentLoaded', function() { Swal.fire({icon: 'error', title: 'Oops...', text: 'Something went wrong.', confirmButtonColor: '#0f766e'}); });</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php echo $message; ?>
    <nav class="top-nav">
        <div class="logo nav-logo-box"><img src="assets/images/logo.png" alt="Logo" class="nav-mini-logo"><span>GCE Keonjhar <span class="hide-mobile">| Student Panel</span></span></div>
        <div style="display: flex; align-items: center; gap: 15px;">
            <button id="themeBtn" onclick="toggleTheme()" class="theme-toggle">🌙</button>
            <a href="logout.php" class="btn-outline btn-small" style="text-decoration: none;">Logout</a>
        </div>
    </nav>

    <div class="dashboard-container slide-up">
        <div class="glass-card form-section">
            <h3>📝 Welcome, <?php echo $_SESSION['full_name']; ?>! File a New Complaint</h3>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="input-grid">
                    <div><label>Full Name</label><input type="text" value="<?php echo $_SESSION['full_name']; ?>" readonly style="background: var(--input-bg); opacity: 0.7;"></div>
                    <div><label>Reg Number</label><input type="text" value="<?php echo $_SESSION['reg_no']; ?>" readonly style="background: var(--input-bg); opacity: 0.7;"></div>
                    <div><label>Branch</label><select name="sBranch" required><option>Computer Science & Engg</option><option>Electrical Engineering</option><option>Civil Engineering</option><option>Mechanical Engineering</option><option>Mineral Engineering</option><option>Mining Engineering</option><option>Metallurgical</option></select></div>
                    <div><label>Year</label><select name="sYear" required><option>1st Year</option><option>2nd Year</option><option>3rd Year</option><option>4th Year</option></select></div>
                    <div><label>Category</label><select name="sCategory" required><option>Hostel & Mess</option><option>Academic</option><option>Infrastructure</option><option>Anti-Ragging</option><option>Other</option></select></div>
                    <div><label>Priority</label><select name="sPriority" required style="font-weight: bold; color: var(--primary);"><option value="High">🔴 High</option><option value="Medium" selected>🟡 Medium</option><option value="Low">🟢 Low</option></select></div>
                </div>
                <label>Complaint Details</label><textarea name="sDesc" required></textarea>
                <label>Attach Proof (Max 2MB)</label><input type="file" name="sFile" accept="image/*,.pdf">
                <button type="submit" class="btn-primary mt-20 w-100">Submit</button>
            </form>
        </div>

        <div class="glass-card mt-20 mb-40">
            <h3>🔍 My Recent Complaints</h3>
            <div class="complaints-grid">
                <?php
                $myRegNo = $_SESSION['reg_no'];
                $result = $conn->query("SELECT * FROM complaints WHERE reg_no='$myRegNo' ORDER BY created_at DESC");
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $sClass = ($row['status'] == 'Pending') ? 'badge-pending' : 'badge-resolved';
                        $att = ($row['file_name'] !== "None") ? "<a href='uploads/{$row['file_name']}' download class='attachment-badge'>⬇️ Proof</a>" : "";
                        $pBadge = ($row['priority'] == 'High') ? "<span style='color:red; font-weight:bold;'>[HIGH]</span>" : "";
                        $date = date('d M Y', strtotime($row['created_at']));
                        echo "<div class='c-card ".(($row['status']=='Pending')?'':'resolved')."'>
                            <div style='display:flex; justify-content:space-between; margin-bottom:8px;'><strong>{$row['category']} $pBadge</strong><span class='c-badge $sClass'>{$row['status']}</span></div>
                            <p style='font-size:14px; margin-bottom: 8px;'>{$row['description']}</p>$att<small style='color:var(--text-light); display:block; margin-top:8px;'>Submitted: $date</small>
                        </div>";
                    }
                } else { echo "<p>No complaints submitted yet.</p>"; }
                ?>
            </div>
        </div>
    </div>
    <script src="assets/js/script.js"></script>
</body>
</html>
