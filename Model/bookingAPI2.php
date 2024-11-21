<?php

require_once "../Database/database.php";
require_once "../Database/bookingdatabase2.php";

// Main Controller Class for Booking API
class BookingAPI2 {
    private $db;
    private $bookingDb;
    private $method;

    public function __construct() {
        $this->initializeDatabase();
        $this->method = $_SERVER['REQUEST_METHOD'];
        header("Content-Type: application/json");
    }

    // Initialize the database connection and BookingDatabase
    private function initializeDatabase() {
        try {
            $this->db = new Database();
            $conn = $this->db->getConnection();
            $this->bookingDb = new BookingDatabase2($conn);
        } catch (Exception $e) {
            $this->handleError("Database initialization error", $e, 500);
        }
    }

    // Process the incoming API request
    public function processRequest() {
        try {
            if ($this->method === 'POST') {
                $this->handlePostRequest();
            } else {
                $this->sendResponse(["error" => "Method not allowed."], 405);
            }
        } catch (Exception $e) {
            $this->handleError("Error processing request", $e, 500);
        }
    }

    // Handle POST requests
    private function handlePostRequest() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (isset($data['action']) && $data['action'] === 'get_event_summary') {
            $this->handleGetEventSummary($data);
        } else {
            $this->sendResponse(["success" => false, "message" => "Invalid action"], 400);
        }
    }

    // Fetch event guest summary with pagination
    private function handleGetEventSummary($data) {
        $limit = isset($data['limit']) ? (int)$data['limit'] : 10;
        $offset = isset($data['offset']) ? (int)$data['offset'] : 0;

        $summary = $this->bookingDb->getEventGuestSummary($limit, $offset);

        // Get startIndex and endIndex based on offset and limit
        $startIndex = $offset + 1;
        $endIndex = min($offset + $limit, $summary['totalCount']);

        $response = [
            "success" => true,
            "data" => $summary['data'],
            "totalCount" => $summary['totalCount'],
            "startIndex" => $startIndex,
            "endIndex" => $endIndex
        ];
        $this->sendResponse($response);
    }

    // Send a JSON response with the given data and HTTP status code
    private function sendResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    // Handle errors by logging them and sending an error response
    private function handleError($message, Exception $e, $statusCode = 500) {
        error_log("$message: " . $e->getMessage());
        $this->sendResponse(["error" => $e->getMessage()], $statusCode);
    }
}

// Instantiate and process the API request
$bookingAPI = new BookingAPI2();
$bookingAPI->processRequest();

?>
