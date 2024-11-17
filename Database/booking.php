<?php
require_once '../model/Database/BookingDatabase.php';
require_once '../model/Database/Database.php';

$action = $_POST['action'] ?? null;

$database = new Database();
$bookingDb = new BookingDatabase($database->getConnection());

$response = ["success" => false, "message" => "Invalid action"];

if ($action === 'check_availability') {
    $arrivalDate = $_POST['arrival_date'];
    $leavingDate = $_POST['leaving_date'];

    $result = $bookingDb->checkAvailability($arrivalDate, $leavingDate);
    if ($result->num_rows > 0) {
        $response = ["success" => false, "message" => "Dates are not available."];
    } else {
        $response = ["success" => true, "message" => "Dates are available!"];
    }
} elseif ($action === 'book_now') {
    $result = $bookingDb->book(
        $_POST['name'],
        $_POST['email'],
        $_POST['contact_number'],
        $_POST['event_type'],
        $_POST['number_of_people'],
        $_POST['arrival_date'],
        $_POST['leaving_date']
    );
    $response = $result
        ? ["success" => true, "message" => "Booking successful!"]
        : ["success" => false, "message" => "Booking failed."];
}

echo json_encode($response);
$database->close();
?>
