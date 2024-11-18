<?php
require_once __DIR__ . '/../Database/database.php';
require_once __DIR__ . '/../Database/bookingdatabase.php';

$database = new Database();
$bookingDb = new BookingDatabase($database);

header('Content-Type: application/json');

// Validate the booking ID
$bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);

if (!$bookingId) {
    echo json_encode(["success" => false, "message" => "Invalid booking ID."]);
    exit;
}

// Retrieve booking details
$bookingDetails = $bookingDb->getBookingDetails($bookingId); // No changes needed here, it's correct now
    
// Check if booking exists
if (!$bookingDetails) {
    echo json_encode(["success" => false, "message" => "Booking not found."]);
    exit;
}

// Check if cancellation is allowed (at least 2 days before arrival)
if (!canCancelBooking($bookingDetails['arrival_date'])) {
    echo json_encode([
        "success" => false,
        "message" => "Cancellations are only allowed at least 2 days before the arrival date."
    ]);
    exit;
}

try {
    // Attempt to delete the booking
    $result = $bookingDb->deleteBooking($bookingId);
    if ($result) {
        // Remove the booking from the session if it matches
        if (isset($_SESSION['booking_details']) && $_SESSION['booking_details']['booking_id'] == $bookingId) {
            unset($_SESSION['booking_details']);
        }
        echo json_encode(["success" => true, "message" => "Booking canceled successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Booking cancellation failed."]);
    }
} catch (Exception $e) {
    error_log("Error during cancellation: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "An error occurred while canceling the booking."]);
}

$database->close();

/**
 * Check if cancellation is allowed (at least 2 days before arrival).
 * 
 * @param string $arrivalDate The arrival date in 'Y-m-d' format.
 * @return bool True if cancellation is allowed, false otherwise.
 */
function canCancelBooking($arrivalDate)
{
    $arrivalDate = new DateTime($arrivalDate);
    $currentDate = new DateTime();
    $twoDaysBeforeArrival = (clone $arrivalDate)->modify('-2 days');
    
    return $currentDate < $twoDaysBeforeArrival;
}
?>
