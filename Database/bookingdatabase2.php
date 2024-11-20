<?php
require_once 'BaseDatabase2.php';  // Assuming this file exists
require_once 'Database.php';  // Assuming this file exists

class BookingDatabase2 extends BaseDatabase2 {  // Extending BaseDatabase2

    // Constructor to initialize the database connection
    public function __construct(mysqli $connection) {
        parent::__construct($connection);
    }

    // Method to fetch event guest summary with pagination
    public function getEventGuestSummary($limit = 10, $offset = 0) {
        try {
            // Modified query to order by highest number of guests
            $query = "SELECT e.event_type AS EventName, SUM(n.number_of_people) AS NumberOfGuests
            FROM booking_information b
            JOIN event e ON e.event_id = b.event_id
            JOIN number_of_people n ON b.booking_id = n.booking_id
            GROUP BY e.event_type
            ORDER BY NumberOfGuests DESC  -- Sort by the total number of guests, highest first
            LIMIT ? OFFSET ?;
            ";

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

            $data = $result->fetch_all(MYSQLI_ASSOC);

            // Now fetch total count for pagination
            $countQuery = "SELECT COUNT(DISTINCT e.event_id) AS total_count
                           FROM booking_information b
                           JOIN event e ON e.event_id = b.event_id
                           JOIN number_of_people n ON b.booking_id = n.booking_id";

            $countResult = $this->db->query($countQuery);
            if (!$countResult) {
                throw new Exception("Error fetching total count: " . $this->db->error);
            }

            $totalCount = $countResult->fetch_assoc()['total_count'];

            return ['data' => $data, 'totalCount' => $totalCount];
        } catch (Exception $e) {
            error_log("Error in getEventGuestSummary: " . $e->getMessage());
            throw $e;
        }
    }

    // Implementing the abstract method getEventSummary from BaseDatabase2
    public function getEventSummary($limit = 10, $offset = 0) {
        return $this->getEventGuestSummary($limit, $offset);  // Return the same method, can be customized further if needed
    }
}
?>
