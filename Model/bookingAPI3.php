<?php
require_once "../Database/Database.php";
require_once "../Database/BookingDatabase3.php";

$method = $_SERVER['REQUEST_METHOD'];
$database = new Database();
$dbConnection = $database->getConnection();
$bookingDb = new BookingDatabase3($dbConnection);

if ($method === 'GET') {
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

    try {
        $data = $bookingDb->getBookingSummary($limit, $offset);

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'data' => $data,
            'total_count' => count($data),
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage(),
        ]);
    }

    exit;
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method',
    ]);
    exit;
}
?>
