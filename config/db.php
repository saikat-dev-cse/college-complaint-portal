<?php
// config/db.php

$servername = "sql.freedb.tech"; 
$username = "freedb_admin_user"; 
$password = "zWknQmZf&Ky5?TT"; // Reverted to your original working password
$dbname = "freedb_gcekjr_grievance";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
?>
