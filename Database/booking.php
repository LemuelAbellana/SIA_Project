<?php 
require_once 'Database.php';
require_once 'BookingDatabase.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$action = $_POST['action'] ?? null;
$database = new Database();
$bookingDb = new BookingDatabase($database);

$response = ["success" => false, "message" => "Invalid action"];

// Helper function to validate and format datetime values
function validateAndFormatDate($dateTime, $format = 'Y-m-d') {
    $dateTimeObject = DateTime::createFromFormat($format, $dateTime);
    return $dateTimeObject ? $dateTimeObject->format('Y-m-d H:i:s') : null; // Database-friendly format
}

// Log the incoming request for debugging
error_log("Request received: " . print_r($_POST, true));

try {
    if ($action === 'check_availability') {
        $arrivalDate = $_POST['arrival_date'] ?? null;
        $leavingDate = $_POST['leaving_date'] ?? null;

        if (!$arrivalDate || !$leavingDate) {
            $response = ["success" => false, "message" => "Missing arrival or leaving date."];
        } else {
            // Validate and format dates
            $arrivalDate = validateAndFormatDate($arrivalDate);
            $leavingDate = validateAndFormatDate($leavingDate);

            if (!$arrivalDate || !$leavingDate || strtotime($arrivalDate) >= strtotime($leavingDate)) {
                $response = ["success" => false, "message" => "Invalid or improperly formatted dates."];
            } else {
                // Check availability in the database
                $result = $bookingDb->checkAvailability($arrivalDate, $leavingDate);

                if ($result) {
                    $response = $result->num_rows > 0
                        ? ["success" => false, "message" => "Dates are not available."]
                        : ["success" => true, "message" => "Dates are available!"];
                } else {
                    // Log SQL error for debugging
                    $response = ["success" => false, "message" => "Database error while checking availability. Please check logs."];
                    error_log("Database query failed during availability check: " . $bookingDb->db->error);
                }
            }
        }
    } elseif ($action === 'book_now') {
        // Required fields for booking
        $requiredFields = ['name', 'email', 'contact_number', 'event_type', 'number_of_people', 'arrival_date', 'leaving_date'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                $response = ["success" => false, "message" => "Missing required field: $field"];
                echo json_encode($response);
                $database->close();
                exit;
            }
        }

        // Validate and format dates
        $arrivalDate = validateAndFormatDate($_POST['arrival_date']);
        $leavingDate = validateAndFormatDate($_POST['leaving_date']);

        if (!$arrivalDate || !$leavingDate || strtotime($arrivalDate) >= strtotime($leavingDate)) {
            $response = ["success" => false, "message" => "Invalid or improperly formatted dates."];
        } else {
            try {
                // Attempt to book the event
                $result = $bookingDb->book(
                    $_POST['name'],
                    $_POST['email'],
                    $_POST['contact_number'],
                    $_POST['event_type'],
                    $arrivalDate,
                    $leavingDate,
                    $_POST['number_of_people']
                );

                $response = $result
                    ? ["success" => true, "message" => "Booking successful!"]
                    : ["success" => false, "message" => "Booking failed. Please try again later."];
            } catch (Exception $e) {
                error_log("Booking error: " . $e->getMessage());
                $response = ["success" => false, "message" => "Error processing booking: " . $e->getMessage()];
            }
        }
    } else {
        $response = ["success" => false, "message" => "Unknown action: $action"];
    }
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    $response = ["success" => false, "message" => "An error occurred. Please try again later."];
}

// Return the response as JSON
header('Content-Type: application/json');
echo json_encode($response);

// Close database connection
$database->close();
?>
