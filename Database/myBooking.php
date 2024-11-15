<?php
session_start();

if (isset($_SESSION['booking_id'])) {
    $booking_id = $_SESSION['booking_id'];
    $name = $_SESSION['name'];
    $email = $_SESSION['email'];
    $contact_number = $_SESSION['contact_number'];
    $event_type = $_SESSION['event_type'];
    $number_of_people = $_SESSION['number_of_people'];
    $arrival_date = $_SESSION['arrival_date'];
    $leaving_date = $_SESSION['leaving_date'];

    // Clear session after displaying booking details
    session_unset();
    session_destroy();
} else {
    echo "No booking information found.";
    exit();
}
?>
