<?php
session_start();

require_once __DIR__ . '/../Database/database.php';
require_once __DIR__ . '/../Database/bookingdatabase.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Send a JSON response header
header('Content-Type: application/json');

class Booking
{
    private $database;
    private $bookingDb;

    public function __construct()
    {
        // Initialize the Database and pass its connection to BookingDatabase
        $this->database = new Database();
        $this->bookingDb = new BookingDatabase($this->database->getConnection());
    }

    // Helper function to validate and format datetime
    private function validateAndFormatDate($dateTime, $format = 'Y-m-d H:i:s')
    {
        $dateTimeObject = DateTime::createFromFormat('Y-m-d\TH:i', $dateTime);
        return $dateTimeObject ? $dateTimeObject->format($format) : null;
    }

    // Helper function to validate contact number
    private function validateContactNumber($contactNumber)
    {
        return preg_match('/^\d{11}$/', $contactNumber); // Contact number must be 11 digits
    }

    // Helper function to validate number of people
    private function validateNumberOfPeople($numberOfPeople)
    {
        return is_numeric($numberOfPeople) && $numberOfPeople >= 1 && $numberOfPeople <= 1000;
    }

    // Check availability
    public function checkAvailability()
    {
        $arrivalDate = $this->validateAndFormatDate($_POST['arrival_date'] ?? '');
        $leavingDate = $this->validateAndFormatDate($_POST['leaving_date'] ?? '');

        if (!$arrivalDate || !$leavingDate) {
            return ["success" => false, "message" => "Invalid or improperly formatted dates."];
        } elseif (strtotime($arrivalDate) >= strtotime($leavingDate)) {
            return ["success" => false, "message" => "Leaving date must be after arrival date."];
        } else {
            $result = $this->bookingDb->checkAvailability($arrivalDate, $leavingDate);
            return $result->num_rows > 0
                ? ["success" => false, "message" => "Dates are not available."]
                : ["success" => true, "message" => "Dates are available!"];
        }
    }

    // Make a booking
    public function bookNow()
    {
        $requiredFields = ['name', 'email', 'contact_number', 'event_type', 'number_of_people', 'arrival_date', 'leaving_date'];
        $missingFields = array_filter($requiredFields, fn($field) => empty($_POST[$field]));

        if (!empty($missingFields)) {
            return ["success" => false, "message" => "Missing required fields: " . implode(', ', $missingFields)];
        }

        $contactNumber = $_POST['contact_number'];
        $numberOfPeople = $_POST['number_of_people'];

        if (!$this->validateContactNumber($contactNumber)) {
            return ["success" => false, "message" => "Contact number must be 11 digits."];
        } elseif (!$this->validateNumberOfPeople($numberOfPeople)) {
            return ["success" => false, "message" => "Number of people must be between 1 and 1000."];
        } else {
            $arrivalDate = $this->validateAndFormatDate($_POST['arrival_date']);
            $leavingDate = $this->validateAndFormatDate($_POST['leaving_date']);

            if (!$arrivalDate || !$leavingDate) {
                return ["success" => false, "message" => "Invalid or improperly formatted dates."];
            } elseif (strtotime($arrivalDate) >= strtotime($leavingDate)) {
                return ["success" => false, "message" => "Leaving date must be after arrival date."];
            } else {
                $availabilityResult = $this->bookingDb->checkAvailability($arrivalDate, $leavingDate);

                if ($availabilityResult->num_rows > 0) {
                    return ["success" => false, "message" => "Dates are not available."];
                } else {
                    try {
                        $bookingResult = $this->bookingDb->book(
                            $_POST['name'],
                            $_POST['email'],
                            $contactNumber,
                            $_POST['event_type'],
                            $arrivalDate,
                            $leavingDate,
                            $numberOfPeople
                        );

                        if ($bookingResult) {
                            $_SESSION['booking_details'] = [
                                'booking_id' => $bookingResult,
                                'name' => $_POST['name'],
                                'email' => $_POST['email'],
                                'contact_number' => $contactNumber,
                                'event_type' => $_POST['event_type'],
                                'number_of_people' => $numberOfPeople,
                                'arrival_date' => $arrivalDate,
                                'leaving_date' => $leavingDate
                            ];

                            return [
                                "success" => true,
                                "message" => "Booking successful!",
                                "redirect" => "receipt.php"
                            ];
                        } else {
                            unset($_SESSION['booking_details']);
                            return ["success" => false, "message" => "Booking failed. Please try again later."];
                        }
                    } catch (Exception $e) {
                        error_log("Error during booking: " . $e->getMessage());
                        return ["success" => false, "message" => "An error occurred while processing your booking."];
                    }
                }
            }
        }
    }

    // Handle actions based on the request
    public function handleRequest()
    {
        $action = $_POST['action'] ?? null;
        $response = ["success" => false, "message" => "Invalid action"];

        try {
            if ($action === 'check_availability') {
                $response = $this->checkAvailability();
            } elseif ($action === 'book_now') {
                $response = $this->bookNow();
            }
        } catch (Exception $e) {
            error_log("Error in Booking class: " . $e->getMessage());
            $response = ["success" => false, "message" => "An error occurred: " . $e->getMessage()];
        }

        echo json_encode($response);
    }

    // Close database connection
    public function closeConnection()
    {
        $this->database->close();
    }
}

// Instantiate the Booking class and handle the request
$booking = new Booking();
$booking->handleRequest();
$booking->closeConnection();
?>
