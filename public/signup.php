<?php
require '../config/db.php';
$message = "";

// Site Branding
$customLogo = 'uploads/site_logo.png';
$displayLogo = file_exists($customLogo) ? $customLogo . '?v=' . filemtime($customLogo) : 'assets/images/logo.png';
$customNameFile = 'uploads/site_name.txt';
$displaySiteName = file_exists($customNameFile) ? htmlspecialchars(file_get_contents($customNameFile)) : 'Government College of Engineering, Keonjhar';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['regName']);
    $regNo = $conn->real_escape_string($_POST['regNo']);
    $email = $conn->real_escape_string($_POST['regEmail']);
    $password = $_POST['regPass'];
    $role = "student"; 

    if (!str_ends_with($email, '@gcekjr.ac.in')) {
        $message = "<script>document.addEventListener('DOMContentLoaded', function() { Swal.fire({icon: 'error', title: 'Invalid Email', text: 'Please register using your official college email.', confirmButtonColor: '#8b5cf6'}); });</script>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (role, full_name, reg_no, email, password) VALUES ('$role', '$name', '$regNo', '$email', '$hashed_password')";

        if ($conn->query($sql) === TRUE) {
            $message = "<script>document.addEventListener('DOMContentLoaded', function() { Swal.fire({icon: 'success', title: 'Registration Successful!', text: 'Welcome to the Portal. Please login now.', confirmButtonColor: '#8b5cf6'}).then(() => { window.location.href = 'index.php'; }); });</script>";
        } else {
            $message = "<script>document.addEventListener('DOMContentLoaded', function() { Swal.fire({icon: 'warning', title: 'Registration Failed', text: 'This Registration Number or Email is already registered!', confirmButtonColor: '#8b5cf6'}); });</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Sign Up - GCE Keonjhar</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gradient">
    <?php echo $message; ?>

    <nav class="top-nav">
        <div class="logo nav-logo-box"><img src="<?php echo $displayLogo; ?>" alt="Logo" class="nav-mini-logo"><span>Grievance Portal</span></div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <button id="themeBtn" onclick="toggleTheme()" class="theme-toggle">🌙</button>
            <a href="index.php" class="btn-outline btn-small">Login</a>
            <a href="signup.php" class="btn-primary btn-small">Sign Up</a>
        </div>
    </nav>

    <div class="center-layout">
        <div class="glass-card login-box fade-in" style="overflow: visible;">
            <img src="<?php echo $displayLogo; ?>" alt="Logo" class="college-logo">
            <h2 class="title" style="font-size: 20px;"><?php echo $displaySiteName; ?></h2>
            <p class="subtitle" style="margin-bottom: 20px;">Create Your Student Account</p>

            <form method="POST" action="">
                <div class="input-grid">
                    <div><label style="text-align: left;">Full Name</label><input type="text" name="regName" placeholder="e.g., Rahul Kumar" required></div>
                    <div><label style="text-align: left;">Reg. Number</label><input type="text" name="regNo" placeholder="e.g., 2101010000" required></div>
                </div>
                <label style="text-align: left;">College Email Address</label>
                <input type="email" name="regEmail" placeholder="e.g., rahul.kumar@gcekjr.ac.in" required>
                <label style="text-align: left;">Create Password</label>
                <input type="password" name="regPass" placeholder="Create a strong password" required>
                <button type="submit" class="btn-primary w-100 mt-20">Create Account</button>
            </form>
            <p style="margin-top: 15px; font-size: 14px;">Already registered? <a href="index.php" style="color: var(--primary); font-weight: bold;">Login Here</a></p>
        </div>
    </div>
    <script src="assets/js/script.js"></script>
</body>
</html>
