<?php

require_once "../Database/database.php";
require_once "../Database/BookingDatabase.php";

class BookingAPI {
    private $dbConnection;
    private $bookingDb;

    public function __construct() {
        try {
            $this->initializeDatabase();
        } catch (Exception $e) {
            $this->sendErrorResponse("Database connection failed.", 500);
            exit;
        }
    }

    private function initializeDatabase() {
        $db = new Database();
        $this->dbConnection = $db->getConnection();
        $this->bookingDb = new BookingDatabase($this->dbConnection);
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        header("Content-Type: application/json");

        try {
            switch ($method) {
                case 'GET':
                    $this->handleGet();
                    break;
                case 'POST':
                    $this->handlePost();
                    break;
                case 'PUT':
                    $this->handlePut();
                    break;
                case 'DELETE':
                    $this->handleDelete();
                    break;
                default:
                    $this->sendErrorResponse("Method not allowed.", 405);
            }
        } catch (Exception $e) {
            error_log("Error in BookingAPI: " . $e->getMessage());
            $this->sendErrorResponse($e->getMessage(), 500);
        }
    }

    private function handleGet() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
        $offset = ($page - 1) * $limit;
        $search = $_GET['search'] ?? "";
    
        // Fetch data and total entry count from the database
        $data = $this->bookingDb->getAll($limit, $offset, $search);
    
        // Calculate start and end indices
        $totalEntries = $data['totalEntries'];
        $startIndex = $offset + 1;
        $endIndex = min($offset + $limit, $totalEntries);
    
        // Send the response
        $this->sendSuccessResponse([
            "bookings" => $data['bookings'],
            "totalEntries" => $totalEntries,
            "startIndex" => $startIndex,
            "endIndex" => $endIndex
        ]);
    }
    

    private function handlePost() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$this->validateBookingData($data)) {
            $this->sendErrorResponse("Invalid input data.", 400);
            return;
        }

        $bookingId = $this->bookingDb->book(
            $data['name'],
            $data['email'],
            $data['contact_number'],
            $data['event_type'],
            $data['arrival_date'],
            $data['leaving_date'],
            $data['number_of_people']
        );

        $this->sendSuccessResponse(["success" => true, "booking_id" => $bookingId]);
    }

    private function handlePut() {
        parse_str(file_get_contents("php://input"), $data);

        if (!$this->validateUpdateData($data)) {
            $this->sendErrorResponse("Invalid input data for update.", 400);
            return;
        }

        $result = $this->bookingDb->updateById(
            $data['id'],
            $data['name'],
            $data['email'],
            $data['arrival_date'],
            $data['leaving_date'],
            $data['number_of_people'],
            $data['contact_number']
        );

        $this->sendSuccessResponse(["success" => $result]);
    }

    private function handleDelete() {
        parse_str(file_get_contents("php://input"), $data);

        if (empty($data['id'])) {
            $this->sendErrorResponse("Missing booking ID for deletion.", 400);
            return;
        }

        $result = $this->bookingDb->deleteById($data['id']);
        $this->sendSuccessResponse(["success" => $result]);
    }

    private function validateBookingData($data) {
        return isset($data['name'], $data['email'], $data['arrival_date'], $data['leaving_date'], $data['number_of_people'], $data['contact_number'], $data['event_type']);
    }

    private function validateUpdateData($data) {
        return isset($data['id'], $data['name'], $data['email'], $data['arrival_date'], $data['leaving_date'], $data['number_of_people'], $data['contact_number']);
    }

    private function sendSuccessResponse($data) {
        echo json_encode($data);
        exit;
    }

    private function sendErrorResponse($message, $statusCode = 500) {
        http_response_code($statusCode);
        echo json_encode(["error" => $message]);
        exit;
    }
}

// Instantiate and handle the API request
$bookingAPI = new BookingAPI();
$bookingAPI->handleRequest();

?>
