<?php
session_start();

require_once __DIR__ . '/../Database/database.php';
require_once __DIR__ . '/../Database/bookingdatabase.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Send a JSON response header once
header('Content-Type: application/json');

$action = $_POST['action'] ?? null; // Retrieve the action from POST request
$database = new Database();
$bookingDb = new BookingDatabase($database);

$response = ["success" => false, "message" => "Invalid action"]; // Default response

// Helper function to validate and format datetime
function validateAndFormatDate($dateTime, $format = 'Y-m-d H:i:s') {
    $dateTimeObject = DateTime::createFromFormat('Y-m-d\TH:i', $dateTime);
    return $dateTimeObject ? $dateTimeObject->format($format) : null;
}

// Helper function to validate contact number
function validateContactNumber($contactNumber) {
    return preg_match('/^\d{11}$/', $contactNumber); // Contact number must be 11 digits
}

// Helper function to validate number of people
function validateNumberOfPeople($numberOfPeople) {
    return is_numeric($numberOfPeople) && $numberOfPeople >= 1 && $numberOfPeople <= 1000;
}

try {
    if ($action === 'check_availability') {
        $arrivalDate = validateAndFormatDate($_POST['arrival_date'] ?? '');
        $leavingDate = validateAndFormatDate($_POST['leaving_date'] ?? '');

        if (!$arrivalDate || !$leavingDate) {
            $response["message"] = "Invalid or improperly formatted dates.";
        } elseif (strtotime($arrivalDate) >= strtotime($leavingDate)) {
            $response["message"] = "Leaving date must be after arrival date.";
        } else {
            $result = $bookingDb->checkAvailability($arrivalDate, $leavingDate);
            $response = $result->num_rows > 0
                ? ["success" => false, "message" => "Dates are not available."]
                : ["success" => true, "message" => "Dates are available!"];
        }
    } elseif ($action === 'book_now') {
        // Validate required fields
        $requiredFields = ['name', 'email', 'contact_number', 'event_type', 'number_of_people', 'arrival_date', 'leaving_date'];
        $missingFields = array_filter($requiredFields, fn($field) => empty($_POST[$field]));

        if (!empty($missingFields)) {
            $response["message"] = "Missing required fields: " . implode(', ', $missingFields);
        } else {
            $contactNumber = $_POST['contact_number'];
            $numberOfPeople = $_POST['number_of_people'];

            if (!validateContactNumber($contactNumber)) {
                $response["message"] = "Contact number must be 11 digits.";
            } elseif (!validateNumberOfPeople($numberOfPeople)) {
                $response["message"] = "Number of people must be between 1 and 1000.";
            } else {
                $arrivalDate = validateAndFormatDate($_POST['arrival_date']);
                $leavingDate = validateAndFormatDate($_POST['leaving_date']);

                if (!$arrivalDate || !$leavingDate) {
                    $response["message"] = "Invalid or improperly formatted dates.";
                } elseif (strtotime($arrivalDate) >= strtotime($leavingDate)) {
                    $response["message"] = "Leaving date must be after arrival date.";
                } else {
                    $availabilityResult = $bookingDb->checkAvailability($arrivalDate, $leavingDate);

                    if ($availabilityResult->num_rows > 0) {
                        $response["message"] = "Dates are not available.";
                    } else {
                        try {
                            $bookingResult = $bookingDb->book(
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

                                $response = [
                                    "success" => true,
                                    "message" => "Booking successful!",
                                    "redirect" => "receipt.php"
                                ];
                            } else {
                                $response["message"] = "Booking failed. Please try again later.";
                                unset($_SESSION['booking_details']);
                            }
                        } catch (Exception $e) {
                            error_log("Error during booking: " . $e->getMessage());
                            $response["message"] = "An error occurred while processing your booking.";
                        }
                    }
                }
            }
        }
    }
} catch (Exception $e) {
    error_log("Error in booking.php: " . $e->getMessage());
    $response["message"] = "An error occurred: " . $e->getMessage();
}

// Output JSON response
echo json_encode($response);

// Close the database connection
$database->close();
?>
