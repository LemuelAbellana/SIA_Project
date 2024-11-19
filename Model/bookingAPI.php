<?php
require_once "../Database/database.php";
require_once "../Database/BookingDatabase.php";

$method = $_SERVER['REQUEST_METHOD'];

// Initialize the Database and BookingDatabase
try {
    $db = new Database();
    $conn = $db->getConnection();
    $bookingDb = new BookingDatabase($conn);
} catch (Exception $e) {
    error_log("Database initialization error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed."]);
    exit;
}

header("Content-Type: application/json");

try {
    if ($method === 'GET') {
        // Pagination parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
        $offset = ($page - 1) * $limit;
        $search = $_GET['search'] ?? "";

        // Fetch bookings and total count
        $data = $bookingDb->getAll($limit, $offset, $search);

        echo json_encode([
            "bookings" => $data['bookings'],
            "totalEntries" => $data['totalEntries']
        ]);
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);

        $name = $data['name'];
        $email = $data['email'];
        $arrivalDate = $data['arrival_date'];
        $leavingDate = $data['leaving_date'];
        $numberOfPeople = $data['number_of_people'];
        $contactNumber = $data['contact_number'];
        $eventType = $data['event_type'];

        $bookingId = $bookingDb->book($name, $email, $contactNumber, $eventType, $arrivalDate, $leavingDate, $numberOfPeople);
        echo json_encode(["success" => true, "booking_id" => $bookingId]);
    } elseif ($method === 'PUT') {
        parse_str(file_get_contents("php://input"), $data);

        $id = $data['id'];
        $name = $data['name'];
        $email = $data['email'];
        $arrivalDate = $data['arrival_date'];
        $leavingDate = $data['leaving_date'];
        $numberOfPeople = $data['number_of_people'];
        $contactNumber = $data['contact_number'];

        $result = $bookingDb->updateById($id, $name, $email, $arrivalDate, $leavingDate, $numberOfPeople, $contactNumber);
        echo json_encode(["success" => $result]);
    } elseif ($method === 'DELETE') {
        parse_str(file_get_contents("php://input"), $data);
        $id = $data['id'];

        $result = $bookingDb->deleteById($id);
        echo json_encode(["success" => $result]);
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed."]);
    }
} catch (Exception $e) {
    error_log("Error in bookingAPI: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}

if ($method === 'DELETE') {
    // Delete booking
    parse_str(file_get_contents("php://input"), $data);
    $id = $data['id'];

    // Delete record
    $result = $bookingDb->deleteById($id);

    header("Content-Type: application/json");
    echo json_encode(["success" => $result]);
}

if ($method === 'POST') {
    // Add new booking (optional feature if needed)
    $data = json_decode(file_get_contents("php://input"), true);

    $name = $data['name'];
    $email = $data['email'];
    $arrivalDate = $data['arrival_date'];
    $leavingDate = $data['leaving_date'];
    $numberOfPeople = $data['number_of_people'];
    $contactNumber = $data['contact_number'];
    $eventType = $data['event_type'];

    try {
        $bookingId = $bookingDb->book($name, $email, $contactNumber, $eventType, $arrivalDate, $leavingDate, $numberOfPeople);

        header("Content-Type: application/json");
        echo json_encode(["success" => true, "booking_id" => $bookingId]);
    } catch (Exception $e) {
        header("Content-Type: application/json", true, 500);
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
}
?>
