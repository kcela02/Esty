<?php
// db.php - Database connection

$host = "localhost";     // usually localhost in XAMPP/phpMyAdmin
$user = "root";          // default user in XAMPP
$pass = "";              // default password is empty in XAMPP
$dbname = "esty_scents"; // database name (create in phpMyAdmin)

$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
