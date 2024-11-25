<?php
require_once 'BaseDatabase3.php';
require_once 'Database.php';

class BookingDatabase3 extends BaseDatabase3 {
    public function __construct(mysqli $connection) {
        parent::__construct($connection);
    }

    public function getBookingSummary($limit, $offset, $search = '') {
        $query = "
            SELECT e.event_type AS event_name, COUNT(b.event_id) AS total_bookings
            FROM booking_information b
            JOIN event e ON b.event_id = e.event_id
            WHERE e.event_type LIKE ? 
            GROUP BY e.event_type
            ORDER BY b.event_id ASC
            LIMIT ? OFFSET ?
        ";
    
        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            throw new Exception("Database error: " . $this->db->error);
        }
    
        $searchTerm = "%$search%";
        $stmt->bind_param("sii", $searchTerm, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    
        $stmt->close();
        return $data;
    }
}
    
