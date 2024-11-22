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
            $this->sendErrorResponse("Database connection failed: " . $e->getMessage(), 500);
        }
    }

    private function initializeDatabase() {
        $db = new Database();
        $this->dbConnection = $db->getConnection();
        $this->bookingDb = new BookingDatabase($this->dbConnection);
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];

        try {
            switch ($method) {
                case 'GET':
                    if (isset($_GET['id'])) {
                        $details = $this->bookingDb->getDetailsById($_GET['id']);
                        echo json_encode(['success' => true, 'booking' => $details]);
                    } else {
                        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                        $entries = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
                        $search = isset($_GET['search']) ? $_GET['search'] : '';
                        $offset = ($page - 1) * $entries;

                        $result = $this->bookingDb->getAll($entries, $offset, $search);
                        echo json_encode($result);
                    }
                    break;

                case 'DELETE':
                    if (!isset($_GET['id'])) {
                        throw new Exception("Booking ID is required");
                    }
                    $result = $this->bookingDb->deleteBooking($_GET['id']);
                    if ($result) {
                        echo json_encode([
                            'success' => true,
                            'message' => 'Booking deleted successfully'
                        ]);
                    } else {
                        throw new Exception("Failed to delete booking");
                    }
                    break;

                case 'PUT':
                    try {
                        $data = json_decode(file_get_contents('php://input'), true);

                        if (!$data || !isset($data['booking_id'])) {
                            throw new Exception("Invalid input data. Booking ID is required.");
                        }

                        $result = $this->bookingDb->updateBooking(
                            $data['booking_id'],
                            $data['name'],
                            $data['email'],
                            $data['contact_number'],
                            $data['event_type'],
                            $data['arrival_date'],
                            $data['leaving_date'],
                            $data['number_of_people']
                        );

                        if ($result) {
                            echo json_encode([
                                'success' => true,
                                'message' => 'Booking updated successfully'
                            ]);
                        } else {
                            throw new Exception("Failed to update booking.");
                        }
                    } catch (Exception $e) {
                        http_response_code(500);
                        echo json_encode([
                            'success' => false,
                            'message' => $e->getMessage()
                        ]);
                    }
                    break;

                default:
                    throw new Exception("Unsupported HTTP method.");
            }
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 500);
        }
    }

    private function handleGet() {
        if (isset($_GET['id'])) {
            $this->handleGetById();
        } else {
            $this->handleGetAll();
        }
    }

    private function handleGetAll() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
        $offset = ($page - 1) * $limit;
        $search = $_GET['search'] ?? "";

        $data = $this->bookingDb->getAll($limit, $offset, $search);

        $totalEntries = $data['totalEntries'];
        $startIndex = $offset + 1;
        $endIndex = min($offset + $limit, $totalEntries);

        $this->sendSuccessResponse([
            "bookings" => $data['bookings'],
            "totalEntries" => $totalEntries,
            "startIndex" => $startIndex,
            "endIndex" => $endIndex
        ]);
    }

    private function handleGetById() {
        $bookingId = isset($_GET['id']) ? (int)$_GET['id'] : null;

        if (!$bookingId) {
            $this->sendErrorResponse("Invalid or missing booking ID.", 400);
            return;
        }

        $booking = $this->bookingDb->getDetailsById($bookingId);

        if ($booking) {
            $this->sendSuccessResponse(["booking" => $booking]);
        } else {
            $this->sendErrorResponse("Booking not found.", 404);
        }
    }

    private function handlePost() {
        $data = json_decode(file_get_contents("php://input"), true);

        $data = $this->fillDefaultValues($data, 'POST');

        if (!$this->validateBookingData($data)) {
            $this->sendErrorResponse("Invalid input data.", 400);
            return;
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->sendErrorResponse("Invalid email format.", 400);
            return;
        }

        if (!strtotime($data['arrival_date']) || !strtotime($data['leaving_date'])) {
            $this->sendErrorResponse("Invalid date format.", 400);
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

    private function fillDefaultValues($data, $method) {
        $defaults = [
            'name' => 'Guest',
            'email' => 'guest@example.com',
            'contact_number' => '0000000000',
            'event_type' => 'General',
            'arrival_date' => date('Y-m-d'),
            'leaving_date' => date('Y-m-d', strtotime('+1 day')),
            'number_of_people' => 1,
        ];

        if ($method === 'PUT') {
            $defaults['id'] = null;
        }

        return array_merge($defaults, $data);
    }

    private function validateBookingData($data) {
        return isset(
            $data['name'],
            $data['email'],
            $data['arrival_date'],
            $data['leaving_date'],
            $data['number_of_people'],
            $data['contact_number'],
            $data['event_type']
        );
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
