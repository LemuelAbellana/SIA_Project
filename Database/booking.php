<?php 
session_start();
require_once 'Database.php';
require_once 'BookingDatabase.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$action = $_POST['action'] ?? null;
$database = new Database();
$bookingDb = new BookingDatabase($database);

$response = ["success" => false, "message" => "Invalid action"];

// Helper function to validate and format datetime values
function validateAndFormatDate($dateTime, $format = 'Y-m-d H:i:s') {
    $dateTimeObject = DateTime::createFromFormat('Y-m-d\TH:i', $dateTime);
    if (!$dateTimeObject) {
        return null;
    }
    return $dateTimeObject->format($format);
}

try {
    if ($action === 'check_availability') {
        $arrivalDate = validateAndFormatDate($_POST['arrival_date'] ?? '');
        $leavingDate = validateAndFormatDate($_POST['leaving_date'] ?? '');

        if (!$arrivalDate || !$leavingDate) {
            $response = ["success" => false, "message" => "Invalid or improperly formatted dates."];
        } elseif (strtotime($arrivalDate) >= strtotime($leavingDate)) {
            $response = ["success" => false, "message" => "Leaving date must be after arrival date."];
        } else {
            $result = $bookingDb->checkAvailability($arrivalDate, $leavingDate);
            $response = $result->num_rows > 0
                ? ["success" => false, "message" => "Dates are not available."]
                : ["success" => true, "message" => "Dates are available!"];
        }
    } elseif ($action === 'book_now') {
        $requiredFields = ['name', 'email', 'contact_number', 'event_type', 'number_of_people', 'arrival_date', 'leaving_date'];
        $missingFields = [];
        
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            $response = ["success" => false, "message" => "Missing required fields: " . implode(', ', $missingFields)];
        } else {
            $arrivalDate = validateAndFormatDate($_POST['arrival_date']);
            $leavingDate = validateAndFormatDate($_POST['leaving_date']);

            if (!$arrivalDate || !$leavingDate) {
                $response = ["success" => false, "message" => "Invalid or improperly formatted dates."];
            } elseif (strtotime($arrivalDate) >= strtotime($leavingDate)) {
                $response = ["success" => false, "message" => "Leaving date must be after arrival date."];
            } else {
                $availabilityResult = $bookingDb->checkAvailability($arrivalDate, $leavingDate);
                
                if ($availabilityResult->num_rows > 0) {
                    $response = ["success" => false, "message" => "Dates are not available."];
                } else {
                    $bookingResult = $bookingDb->book(
                        $_POST['name'],
                        $_POST['email'],
                        $_POST['contact_number'],
                        $_POST['event_type'],
                        $arrivalDate,
                        $leavingDate,
                        $_POST['number_of_people']
                    );

                    if ($bookingResult) {
                        $_SESSION['booking_details'] = [
                            'booking_id' => $bookingResult,
                            'name' => $_POST['name'],
                            'email' => $_POST['email'],
                            'contact_number' => $_POST['contact_number'],
                            'event_type' => $_POST['event_type'],
                            'number_of_people' => $_POST['number_of_people'],
                            'arrival_date' => $arrivalDate,
                            'leaving_date' => $leavingDate
                        ];
                        
                        $response = [
                            "success" => true, 
                            "message" => "Booking successful!",
                            "redirect" => "receipt.php"
                        ];
                    } else {
                        $response = ["success" => false, "message" => "Booking failed. Please try again later."];
                        unset($_SESSION['booking_details']);
                    }
                }
            }
        }
    }
} catch (Exception $e) {
    error_log("Error in booking.php: " . $e->getMessage());
    $response = ["success" => false, "message" => "An error occurred: " . $e->getMessage()];
    unset($_SESSION['booking_details']);
}

// Return the response as JSON
header('Content-Type: application/json');
echo json_encode($response);

// Close database connection
$database->close();
?>
