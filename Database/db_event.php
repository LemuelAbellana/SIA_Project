<?php
include 'db_connect.php';

$query = "SELECT * FROM events"; // Replace 'events' with your actual table name
$stmt = $conn->prepare($query);
$stmt->execute();

$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
