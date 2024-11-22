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

    private function validateBookingId()
    {
        $bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
        if (!$bookingId) {
            error_log("Invalid booking ID provided.");
            return null;
        }
        return $bookingId;
    }

    private function canCancelBooking($arrivalDate)
    {
        try {
            $arrivalDateObj = new DateTime($arrivalDate);
            $currentDate = new DateTime();
            $twoDaysBeforeArrival = (clone $arrivalDateObj)->modify('-2 days');
            return $currentDate < $twoDaysBeforeArrival;
        } catch (Exception $e) {
            error_log("Error in canCancelBooking: " . $e->getMessage());
            return false;
        }
    }

    public function getBookingDetails($bookingId)
    {
        try {
            $details = $this->bookingDb->getDetailsById($bookingId);
            if (!$details) {
                return ["success" => false, "message" => "Booking not found."];
            }

            $details['can_cancel'] = $this->canCancelBooking($details['arrival_date']);
            return ["success" => true, "details" => $details];
        } catch (Exception $e) {
            error_log("Error fetching booking details: " . $e->getMessage());
            return ["success" => false, "message" => "An error occurred while fetching booking details."];
        }
    }

    public function cancelBooking($bookingId)
    {
        try {
            $bookingDetails = $this->bookingDb->getDetailsById($bookingId);

            if (!$bookingDetails) {
                return ["success" => false, "message" => "Booking not found."];
            }

            if (!$this->canCancelBooking($bookingDetails['arrival_date'])) {
                return [
                    "success" => false,
                    "message" => "Cancellations are only allowed at least 2 days before the arrival date."
                ];
            }

            $result = $this->bookingDb->deleteBooking($bookingId);

            return $result
                ? ["success" => true, "message" => "Booking canceled successfully."]
                : ["success" => false, "message" => "Failed to cancel the booking."];
        } catch (Exception $e) {
            error_log("Error during cancellation: " . $e->getMessage());
            return ["success" => false, "message" => "An error occurred while canceling the booking."];
        }
    }

    public function handleRequest()
    {
        header('Content-Type: application/json');

        $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);

        if (!$action) {
            echo json_encode(["success" => false, "message" => "Invalid action."]);
            exit;
        }

        switch ($action) {
            case 'get_details':
                $bookingId = $this->validateBookingId();
                if (!$bookingId) {
                    echo json_encode(["success" => false, "message" => "Invalid booking ID."]);
                    exit;
                }

                echo json_encode($this->getBookingDetails($bookingId));
                break;

            case 'cancel_booking':
                $bookingId = $this->validateBookingId();
                if (!$bookingId) {
                    echo json_encode(["success" => false, "message" => "Invalid booking ID."]);
                    exit;
                }

                echo json_encode($this->cancelBooking($bookingId));
                break;

            default:
                echo json_encode(["success" => false, "message" => "Unknown action."]);
                break;
        }
    }

    public function closeConnection()
    {
        $this->database->getConnection()->close();
    }
}

// Call and handle the request
$cancelBooking = new CancelBooking();
$cancelBooking->handleRequest();


?>