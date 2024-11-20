<?php
require_once "../Database/Database.php";
require_once "../Database/BookingDatabase3.php";

class BookingAPI3
{
    private $dbConnection;
    private $bookingDb;

    public function __construct()
    {
        $database = new Database();
        $this->dbConnection = $database->getConnection();
        $this->bookingDb = new BookingDatabase3($this->dbConnection);
    }

    public function handleRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        switch ($method) {
            case 'GET':
                $this->handleGet();
                break;
            default:
                $this->sendResponse(405, [
                    'status' => 'error',
                    'message' => 'Invalid request method',
                ]);
                break;
        }
    }

    private function handleGet()
    {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

        try {
            $data = $this->bookingDb->getBookingSummary($limit, $offset);

            $this->sendResponse(200, [
                'status' => 'success',
                'data' => $data,
                'total_count' => count($data),
            ]);
        } catch (Exception $e) {
            $this->sendResponse(500, [
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function sendResponse(int $statusCode, array $responseBody)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($responseBody);
        exit;
    }
}

// Instantiate and handle the request
$api = new BookingAPI3();
$api->handleRequest();
?>
