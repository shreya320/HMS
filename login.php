<?php
session_start();
require 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["userid"] ?? '';
    $password = $_POST["usrpsw"] ?? '';

    // Check if fields are empty
    if (empty($email) || empty($password)) {
        die("Please enter both email and password!");
    }

    // Check if the email exists in the database
    $stmt = $conn->prepare("SELECT id, first_name, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    // If user exists
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $first_name, $hashed_password);
        $stmt->fetch();

        // Verify the password
        if (password_verify($password, $hashed_password)) {
            $_SESSION["user_id"] = $id;
            $_SESSION["user_name"] = $first_name;

            echo "Login successful!";
            header("Location: dashboard.php"); // Redirect to a user dashboard
            exit();
        } else {
            echo "Invalid password!";
        }
    } else {
        echo "No user found with this email!";
    }

    $stmt->close();
    $conn->close();
}
?>
