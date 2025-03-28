<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST["first_name"] ?? '';
    $last_name = $_POST["last_name"] ?? '';
    $email = $_POST["email"] ?? '';
    $password = $_POST["password"] ?? '';

    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        die("All fields are required!");
    }

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare and execute the SQL query
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $first_name, $last_name, $email, $hashed_password);

    if ($stmt->execute()) {
        echo "Registration successful!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
