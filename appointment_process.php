<?php
require 'db_config.php'; // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Debugging: Check received POST data (Remove in production)
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        http_response_code(405);
        die("405 Method Not Allowed");
    }
    

    // Validate required fields
    if (
        empty($_POST['fullname']) || empty($_POST['email']) || empty($_POST['phone']) || 
        empty($_POST['reason']) || empty($_POST['specialist']) || 
        empty($_POST['preferred_date'])
    ) {
        die("<script>alert('Error: All required fields must be filled!'); window.history.back();</script>");
    }

    // Escape input data to prevent SQL injection
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);
    $specialist = mysqli_real_escape_string($conn, $_POST['specialist']);
    $preferred_date = mysqli_real_escape_string($conn, $_POST['preferred_date']);
    $preferred_time = !empty($_POST['preferred_time']) ? mysqli_real_escape_string($conn, $_POST['preferred_time']) : NULL;
    $notes = !empty($_POST['notes']) ? mysqli_real_escape_string($conn, $_POST['notes']) : NULL;

    // Handle user_id (if using sessions)
    session_start(); // Start session if needed
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL; // Get user ID if logged in

    // Corrected SQL query (handling NULL values properly)
    $sql = "INSERT INTO appointments (user_id, full_name, email, phone_number, reason, specialist, preferred_date, preferred_time, additional_notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("issssssss", $user_id, $fullname, $email, $phone, $reason, $specialist, $preferred_date, $preferred_time, $notes);
        
        if ($stmt->execute()) {
            echo "<script>alert('Appointment booked successfully!'); window.location.href='appointment.html';</script>";
        } else {
            echo "<script>alert('Error: Could not book appointment.'); window.history.back();</script>";
        }
        
        $stmt->close();
    } else {
        echo "<script>alert('Database error! Could not prepare statement.'); window.history.back();</script>";
    }

    $conn->close();
} else {
    header("Location: appointment.html");
    exit();
}
?>
