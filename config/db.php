<?php
// Securely grab credentials from Render's Environment Variables
$servername = getenv("DB_HOST");
$username = getenv("DB_USER");
$password = getenv("DB_PASS");
$dbname = getenv("DB_NAME");

// Fallback for local testing (can be empty or placeholders)
if (!$servername) {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "gcekjr_portal";
}

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
