<?php
include 'db_connect.php';

$query = "SELECT * FROM bookings"; // Replace 'bookings' with your actual table name
$stmt = $conn->prepare($query);
$stmt->execute();

$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
