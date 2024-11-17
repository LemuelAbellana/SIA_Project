<?php

require_once '../model/BookingDatabase.php';

$bookingId = $_POST['booking_id'];

if ($bookingId) {
    $db = new mysqli('localhost', 'root', '', 'booking_db');
    $bookingDb = new BookingDatabase($db);
    $result = $bookingDb->cancelBooking($bookingId);

    if ($result) {
        echo json_encode(["success" => true, "message" => "Booking canceled"]);
    } else {
        echo json_encode(["success" => false, "message" => "Cancellation failed"]);
    }
}
