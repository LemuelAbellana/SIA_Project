<?php
session_start();

require_once __DIR__ . '/../Database/database.php';
require_once __DIR__ . '/../Database/bookingdatabase.php';

class CancelBooking
{
    private $database;
    private $bookingDb;

    public function __construct()
    {
        $this->database = new Database();
        $this->bookingDb = new BookingDatabase($this->database->getConnection());
    }

    /**
     * Validate the booking ID from the POST request
     *
     * @return int|null Returns booking ID if valid, otherwise null
     */
    private function validateBookingId()
    {
        return filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
    }

    /**
     * Check if cancellation is allowed (at least 2 days before arrival).
     * 
     * @param string $arrivalDate The arrival date in 'Y-m-d' format.
     * @return bool True if cancellation is allowed, false otherwise.
     */
    private function canCancelBooking($arrivalDate)
    {
        $arrivalDate = new DateTime($arrivalDate);
        $currentDate = new DateTime();
        $twoDaysBeforeArrival = (clone $arrivalDate)->modify('-2 days');
        
        return $currentDate < $twoDaysBeforeArrival;
    }

    /**
     * Cancel the booking process
     *
     * @param int $bookingId The ID of the booking to cancel
     * @return array Response array indicating success or failure
     */
    public function cancelBooking($bookingId)
    {
        // Retrieve booking details
        $bookingDetails = $this->bookingDb->getDetailsById($bookingId);

        // Check if booking exists
        if (!$bookingDetails) {
            return ["success" => false, "message" => "Booking not found."];
        }

        // Check if cancellation is allowed (at least 2 days before arrival)
        if (!$this->canCancelBooking($bookingDetails['arrival_date'])) {
            return [
                "success" => false,
                "message" => "Cancellations are only allowed at least 2 days before the arrival date."
            ];
        }

        // Attempt to delete the booking
        try {
            $result = $this->bookingDb->deleteBooking($bookingId);
            if ($result) {
                // Remove the booking from the session if it matches
                if (isset($_SESSION['booking_details']) && $_SESSION['booking_details']['booking_id'] == $bookingId) {
                    unset($_SESSION['booking_details']);
                }
                return ["success" => true, "message" => "Booking canceled successfully."];
            } else {
                return ["success" => false, "message" => "Booking cancellation failed."];
            }
        } catch (Exception $e) {
            error_log("Error during cancellation: " . $e->getMessage());
            return ["success" => false, "message" => "An error occurred while canceling the booking."];
        }
    }

    /**
     * Handle the request to cancel a booking
     *
     * @return void
     */
    public function handleRequest()
    {
        header('Content-Type: application/json');

        // Validate the booking ID
        $bookingId = $this->validateBookingId();

        if (!$bookingId) {
            echo json_encode(["success" => false, "message" => "Invalid booking ID."]);
            exit;
        }

        // Perform the cancellation
        $response = $this->cancelBooking($bookingId);
        echo json_encode($response);
    }

    /**
     * Close the database connection
     *
     * @return void
     */
    public function closeConnection()
    {
        $this->database->close();
    }
}

// Instantiate the CancelBooking class and handle the request
$cancelBooking = new CancelBooking();
$cancelBooking->handleRequest();
$cancelBooking->closeConnection();
?>
