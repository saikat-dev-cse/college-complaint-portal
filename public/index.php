<?php
session_start();
require '../config/db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $conn->real_escape_string($_POST['loginRole']);
    $identifier = $conn->real_escape_string($_POST['loginEmail']); 
    $password = $_POST['loginPass'];

    $sql = "SELECT * FROM users WHERE (email='$identifier' OR reg_no='$identifier') AND role='$role'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['reg_no'] = $user['reg_no'];
            $_SESSION['role'] = $user['role'];
            header("Location: " . ($role === 'student' ? "student.php" : "admin.php"));
            exit();
        } else {
            $message = "<script>document.addEventListener('DOMContentLoaded', function() { Swal.fire({icon: 'error', title: 'Login Failed', text: 'Incorrect Password!', confirmButtonColor: '#0f766e'}); });</script>";
        }
    } else {
        $message = "<script>document.addEventListener('DOMContentLoaded', function() { Swal.fire({icon: 'error', title: 'Access Denied', text: 'Account not found or incorrect role selected.', confirmButtonColor: '#0f766e'}); });</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GCE Keonjhar</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gradient">
    <?php echo $message; ?>

    <nav class="top-nav">
        <div class="logo">Complaint Portal</div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <button id="themeBtn" onclick="toggleTheme()" class="theme-toggle">🌙</button>
            <a href="index.php" class="btn-primary btn-small">Login</a>
            <a href="signup.php" class="btn-outline btn-small">Sign Up</a>
        </div>
    </nav>

    <div class="center-layout">
        <div class="glass-card login-box fade-in">
            <img src="assets/images/logo.png" alt="Logo" class="college-logo">
            <h2 class="title" style="font-size: 20px;">Government College of Engineering, Keonjhar</h2>
            <p class="subtitle">Complaint Management Portal</p>

            <form method="POST" action="">
                <label style="text-align: left;">Select Role</label>
                <select name="loginRole">
                    <option value="student">Student</option>
                    <option value="admin">Admin</option>
                </select>

                <label style="text-align: left;">College Email or Reg. No</label>
                <input type="text" name="loginEmail" placeholder="e.g. 2101010000" required>

                <label style="text-align: left;">Password</label>
                <input type="password" name="loginPass" placeholder="••••••••" required>

                <button type="submit" class="btn-primary w-100 mt-20">Login</button>
            </form>
            <p style="margin-top: 15px; font-size: 14px;">Don't have an account? <a href="signup.php" style="color: var(--primary); font-weight: bold;">Sign Up Here</a></p>
        </div>
    </div>
    <script src="assets/js/script.js"></script>
</body>
</html>
