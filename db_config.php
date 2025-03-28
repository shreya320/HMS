<?php
$servername = "localhost";
$username = "root"; // Change if needed
$password = "22BCE3818"; // Change if needed
$database = "hospital_db"; // Replace with your actual DB name

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
