<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') { header("Location: index.php"); exit(); }

$message = "";

// Site Branding
$customLogo = 'uploads/site_logo.png';
$displayLogo = file_exists($customLogo) ? $customLogo . '?v=' . filemtime($customLogo) : 'assets/images/logo.png';
$customNameFile = 'uploads/site_name.txt';
$displaySiteName = file_exists($customNameFile) ? htmlspecialchars(file_get_contents($customNameFile)) : 'Government College of Engineering, Keonjhar';

// Profile Picture Logic
$regNo = $_SESSION['reg_no'];
$profilePicPath = "uploads/" . $regNo . "_profile.jpg";
// FIX: Generates a beautiful avatar with initials if they haven't uploaded one
$displayProfilePic = file_exists($profilePicPath) ? $profilePicPath . '?v=' . time() : 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['full_name']) . '&background=0f766e&color=fff&rounded=true&bold=true';

// Upload Profile Picture
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_avatar'])) {
    if (isset($_FILES['avatarFile']) && $_FILES['avatarFile']['error'] == 0) {
        $allowed = ['image/jpeg', 'image/png', 'image/jpg'];
        if (in_array($_FILES['avatarFile']['type'], $allowed)) {
            move_uploaded_file($_FILES['avatarFile']['tmp_name'], $profilePicPath);
            $message = "<script>document.addEventListener('DOMContentLoaded', function() { Swal.fire({icon: 'success', title: 'Profile Updated!'}).then(() => { window.location.href = 'student.php'; }); });</script>";
        }
    }
}

// Submit Complaint
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_complaint'])) {
    $name = $_SESSION['full_name'];
    $branch = $conn->real_escape_string($_POST['sBranch']);
    $year = $conn->real_escape_string($_POST['sYear']);
    $category = $conn->real_escape_string($_POST['sCategory']);
    $priority = $conn->real_escape_string($_POST['sPriority']); 
    $desc = $conn->real_escape_string($_POST['sDesc']); 
    $fileName = "None";

    if (isset($_FILES['sFile']) && $_FILES['sFile']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        if ($_FILES['sFile']['size'] > 2 * 1024 * 1024) {
            $message = "<script>document.addEventListener('DOMContentLoaded', function() { Swal.fire({icon: 'error', title: 'File Too Large'}); });</script>";
        } else {
            $fileName = time() . "_" . basename($_FILES["sFile"]["name"]);
            move_uploaded_file($_FILES["sFile"]["tmp_name"], $target_dir . $fileName);
        }
    }

    if ($message === "") {
        $sql = "INSERT INTO complaints (reg_no, name, branch, study_year, category, priority, description, file_name) VALUES ('$regNo', '$name', '$branch', '$year', '$category', '$priority', '$desc', '$fileName')";
        if ($conn->query($sql) === TRUE) {
            $message = "<script>document.addEventListener('DOMContentLoaded', function() { Swal.fire({ icon: 'success', title: 'Complaint Submitted!' }).then(() => { window.location.href = 'student.php'; }); });</script>";
        } else {
            $message = "<script>document.addEventListener('DOMContentLoaded', function() { Swal.fire({icon: 'error', title: 'Oops...', text: 'Something went wrong.'}); });</script>";
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
        <div class="logo nav-logo-box"><img src="<?php echo $displayLogo; ?>" alt="Logo" class="nav-mini-logo"><span class="hide-mobile"><?php echo $displaySiteName; ?></span></div>
        <div style="display: flex; align-items: center; gap: 15px;">
            <button id="themeBtn" onclick="toggleTheme()" class="theme-toggle">🌙</button>
            <a href="logout.php" class="btn-outline btn-small">Logout</a>
            <img src="<?php echo $displayProfilePic; ?>" class="student-avatar" title="<?php echo $_SESSION['full_name']; ?>" onclick="document.getElementById('avatarUploadBox').style.display='block';">
        </div>
    </nav>

    <div class="dashboard-container slide-up">
        
        <div id="avatarUploadBox" class="glass-card mb-40 fade-in" style="display:none; border: 2px solid var(--primary);">
            <h3 style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">📸 Update Profile Picture <span style="cursor:pointer;" onclick="document.getElementById('avatarUploadBox').style.display='none';">❌</span></h3>
            <form method="POST" action="" enctype="multipart/form-data" style="display:flex; gap:15px; align-items:center;">
                <input type="hidden" name="upload_avatar" value="1">
                <input type="file" name="avatarFile" accept="image/png, image/jpeg, image/jpg" required>
                <button type="submit" class="btn-primary">Upload</button>
            </form>
        </div>

        <div class="glass-card form-section">
            <h3>📝 Welcome, <?php echo $_SESSION['full_name']; ?>! File a New Grievance</h3>
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="submit_complaint" value="1">
                <div class="input-grid">
                    <div><label>Full Name</label><input type="text" value="<?php echo $_SESSION['full_name']; ?>" readonly style="background: var(--input-bg); opacity: 0.7;"></div>
                    <div><label>Reg Number</label><input type="text" value="<?php echo $_SESSION['reg_no']; ?>" readonly style="background: var(--input-bg); opacity: 0.7;"></div>
                    <div><label>Branch</label><select name="sBranch" required><option>Computer Science & Engg</option><option>Electrical Engineering</option><option>Civil Engineering</option><option>Mechanical Engineering</option><option>Mineral Engineering</option><option>Mining Engineering</option><option>Metallurgical</option></select></div>
                    <div><label>Year</label><select name="sYear" required><option>1st Year</option><option>2nd Year</option><option>3rd Year</option><option>4th Year</option></select></div>
                    <div><label>Category</label><select name="sCategory" required><option>Hostel & Mess</option><option>Academic</option><option>Infrastructure</option><option>Anti-Ragging</option><option>Other</option></select></div>
                    <div><label>Priority</label><select name="sPriority" required style="font-weight: bold; color: var(--primary);"><option value="High">🔴 High</option><option value="Medium" selected>🟡 Medium</option><option value="Low">🟢 Low</option></select></div>
                </div>
                <label>Complaint Details</label><textarea name="sDesc" placeholder="Describe your issue clearly..." required></textarea>
                <label>Attach Proof (Optional, Max 2MB)</label><input type="file" name="sFile" accept="image/*,.pdf">
                <button type="submit" class="btn-primary mt-20 w-100">Submit Grievance</button>
            </form>
        </div>

        <div class="glass-card mt-20 mb-40">
            <h3>🔍 My Grievance History</h3>
            <div class="complaints-grid">
                <?php
                $result = $conn->query("SELECT * FROM complaints WHERE reg_no='$regNo' ORDER BY created_at DESC");
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
