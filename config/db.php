<?php
// config/db.php

$servername = "sql.freedb.tech"; // Your FreeDB Server
$username = "freedb_admin_user"; // Your FreeDB Username
$password = "zWknQmZf&Ky5?TT"; // Your FreeDB Password
$dbname = "freedb_gcekjr_grievance"; // Your FreeDB Database Name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
?>
