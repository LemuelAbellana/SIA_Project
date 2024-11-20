<?php
require_once "../Database/database.php";
require_once "../Database/bookingdatabase2.php";  // Correctly include the new file

$method = $_SERVER['REQUEST_METHOD'];

try {
    // Initialize the Database and BookingDatabase2
    $db = new Database();
    $conn = $db->getConnection();
    $bookingDb = new BookingDatabase2($conn);  // Instantiate BookingDatabase2
} catch (Exception $e) {
    error_log("Database initialization error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed."]);
    exit;
}

header("Content-Type: application/json");

try {
    if ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);

        if (isset($data['action']) && $data['action'] === 'get_event_summary') {
            // Fetch event guest summary with pagination
            $limit = isset($data['limit']) ? $data['limit'] : 10;  // Default to 10 entries per page
            $offset = isset($data['offset']) ? $data['offset'] : 0;  // Default to the first page
            
            // Fetch event summary and total count
            $summary = $bookingDb->getEventGuestSummary($limit, $offset);
            echo json_encode(["success" => true, "data" => $summary['data'], "totalCount" => $summary['totalCount']]);
        } else {
            echo json_encode(["success" => false, "message" => "Invalid action"]);
        }
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed."]);
    }
} catch (Exception $e) {
    error_log("Error in bookingAPI: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}

// Ensure getEventGuestSummary is inside the BookingDatabase class
class BookingDatabase {
    private $db;

    // Constructor to initialize the database connection
    public function __construct($db) {
        $this->db = $db;
    }

    // Public method to fetch event guest summary with pagination
    public function getEventGuestSummary($limit = 10, $offset = 0) {
        $query = "SELECT e.event_type AS EventName, SUM(n.number_of_people) AS NumberOfGuests
                  FROM booking_information b
                  JOIN event e ON e.event_id = b.event_id
                  JOIN number_of_people n ON b.booking_id = n.booking_id
                  GROUP BY e.event_type
                  ORDER BY e.event_type DESC
                  LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            throw new Exception("Error preparing event summary query: " . $this->db->error);
        }

        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            throw new Exception("Error executing event summary query: " . $this->db->error);
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>
    