<?php
session_start();
require_once __DIR__ . "database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $date = $_POST["date"];
    $guests = (int)$_POST["guests"];

    // Initialize database connection
    $db = new Database();
    $conn = $db->getConnection();

    // Insert booking into the database
    $sql = "INSERT INTO bookings (name, email, booking_date, guests) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $name, $email, $date, $guests);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Booking successfully created!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error creating booking: " . $stmt->error . "</div>";
    }

    $stmt->close();
    $db->close();
}
?>
