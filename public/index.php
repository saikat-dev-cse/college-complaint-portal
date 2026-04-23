require '../config/db.php';

$message = "";
// Branding
$customLogo = 'uploads/site_logo.png';
$displayLogo = file_exists($customLogo) ? $customLogo . '?v=' . filemtime($customLogo) : 'assets/images/logo.png';
$customNameFile = 'uploads/site_name.txt';
@@ -28,10 +27,10 @@
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
@@ -40,7 +39,7 @@
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GCE Keonjhar Grievance Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
@@ -64,22 +63,22 @@

            <form method="POST" action="">
                <label style="text-align: left;">Select Role</label>
                <select name="loginRole">
                    <option value="student">Student</option>
                    <option value="admin">Faculty / Admin</option>
                </select>

                 <label style="text-align: left;">Email or Reg. No</label>
                 <input type="text" name="loginEmail" placeholder="e.g., 2101010000 or your@gcekjr.ac.in" required>

                 <label style="text-align: left;">Password</label>
                 <input type="password" name="loginPass" placeholder="Enter your password" required>

                <button type="submit" class="btn-primary w-100 mt-20">Secure Login</button>
            </form>
            <p style="margin-top: 15px; font-size: 14px;">Don't have an account? <a href="signup.php" style="color: var(--primary); font-weight: bold;">Sign Up Here</a></p>
        </div>
    </div>
    <script src="assets/js/script.js"></script>
</body>
</html>
