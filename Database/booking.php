<?php 
require_once 'Database.php';
require_once 'BookingDatabase.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$action = $_POST['action'] ?? null;
$database = new Database();
$bookingDb = new BookingDatabase($database);

$response = ["success" => false, "message" => "Invalid action"];

// Helper function to validate and format datetime values
// Helper function to validate and format datetime values
function validateAndFormatDate($dateTime, $format = 'Y-m-d H:i:s') {
    $dateTimeObject = DateTime::createFromFormat('Y-m-d\TH:i', $dateTime); // Parse ISO format
    if (!$dateTimeObject) {
        return null; // Return null if the date is invalid
    }
    return $dateTimeObject->format($format); // Convert to database-compatible format
}

// Action handling
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
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            $response = ["success" => false, "message" => "Missing required field: $field"];
            echo json_encode($response);
            $database->close();
            exit;
        }
    }

    $arrivalDate = validateAndFormatDate($_POST['arrival_date']);
    $leavingDate = validateAndFormatDate($_POST['leaving_date']);

    if (!$arrivalDate || !$leavingDate || strtotime($arrivalDate) >= strtotime($leavingDate)) {
        $response = ["success" => false, "message" => "Invalid or improperly formatted dates."];
    } else {
        try {
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


// Log the incoming request for debugging
error_log("Request received: " . print_r($_POST, true));

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
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                $response = ["success" => false, "message" => "Missing required field: $field"];
                echo json_encode($response);
                $database->close();
                exit;
            }
        }

        $arrivalDate = validateAndFormatDate($_POST['arrival_date']);
        $leavingDate = validateAndFormatDate($_POST['leaving_date']);

        if (!$arrivalDate || !$leavingDate || strtotime($arrivalDate) >= strtotime($leavingDate)) {
            $response = ["success" => false, "message" => "Invalid or improperly formatted dates."];
        } else {
            try {
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
