<?php
// Database connection
$servername = "sql12.freesqldatabase.com";
$username = "sql12777636"; // Change if needed
$password = "7YypCXcYQm"; // Change if needed
$database = "sql12777636"; // Update your database name

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
