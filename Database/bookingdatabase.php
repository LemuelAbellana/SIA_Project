<?php

class BookingDatabase {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    // Check availability
    public function checkAvailability($arrivalDate, $leavingDate) {
        $query = "SELECT * FROM bookings WHERE 
                  (arrival_date BETWEEN ? AND ?) 
                  OR (leaving_date BETWEEN ? AND ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssss", $arrivalDate, $leavingDate, $arrivalDate, $leavingDate);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Create booking
    public function book($name, $email, $contactNumber, $eventType, $numberOfPeople, $arrivalDate, $leavingDate) {
        $query = "INSERT INTO bookings (name, email, contact_number, event_type, number_of_people, arrival_date, leaving_date) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sssssss", $name, $email, $contactNumber, $eventType, $numberOfPeople, $arrivalDate, $leavingDate);
        return $stmt->execute();
    }

    // Cancel booking
    public function cancelBooking($bookingId) {
        $query = "DELETE FROM bookings WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $bookingId);
        return $stmt->execute();
    }
}
?>
