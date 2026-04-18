<?php
//Start session safely (prevents duplicate warnings)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dragonstone";

//Create and verify connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

