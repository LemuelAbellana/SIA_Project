<?php
require_once 'database.php';

abstract class BaseDatabase {
    protected $db;

    public function __construct(Database $database) {
        $this->db = $database->getConnection();
    }
}

class BookingDatabase extends BaseDatabase {

    // Check availability for a given event and dates
    public function checkAvailability($arrivalDate, $leavingDate) {
        $query = "SELECT * FROM booking_information 
                  WHERE (arrival_date BETWEEN ? AND ?) 
                  OR (leaving_date BETWEEN ? AND ?)
                  OR (? BETWEEN arrival_date AND leaving_date) 
                  OR (? BETWEEN arrival_date AND leaving_date)";
        $stmt = $this->db->prepare($query);
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        $stmt->bind_param("ssssss", $arrivalDate, $leavingDate, $arrivalDate, $leavingDate, $arrivalDate, $leavingDate);
        $stmt->execute();
        $result = $stmt->get_result(); // Return result set

        if ($result === false) {
            throw new Exception("Execute failed: " . $this->db->error);
        }

        return $result;
    }

    // Book a new event
    public function book($name, $email, $contactNumber, $eventType, $arrivalDate, $leavingDate, $numberOfPeople) {
        // Validate and check dates
        if (strtotime($arrivalDate) >= strtotime($leavingDate)) {
            throw new Exception("Arrival date must be before leaving date.");
        }

        // Get or create customer ID
        $customerId = $this->getCustomerIdByContactNumber($contactNumber);
        if (!$customerId) {
            $customerId = $this->addCustomer($name, $email, $contactNumber);
        }

        // Get or create event ID
        $eventId = $this->getEventId($eventType);

        // Insert booking information
        $bookingId = $this->addBookingInformation($customerId, $eventId, $arrivalDate, $leavingDate);

        // Insert number of people
        $this->addNumberOfPeople($bookingId, $numberOfPeople);

        return true;
    }

    // Get customer ID by contact number
    private function getCustomerIdByContactNumber($contactNumber) {
        $query = "SELECT customer_id FROM customer WHERE contact_number = ?";
        $stmt = $this->db->prepare($query);
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        $stmt->bind_param("s", $contactNumber);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result === false) {
            throw new Exception("Execute failed: " . $this->db->error);
        }

        return $result->num_rows > 0 ? $result->fetch_assoc()['customer_id'] : null;
    }

    // Add a new customer
    private function addCustomer($name, $email, $contactNumber) {
        $query = "INSERT INTO customer (name, email, contact_number) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($query);
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        $stmt->bind_param("sss", $name, $email, $contactNumber);
        $stmt->execute();
        if ($stmt->error) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        return $this->db->insert_id;
    }

    // Get event ID by event type or create a new event
    private function getEventId($eventType) {
        $query = "SELECT event_id FROM event WHERE event_type = ?";
        $stmt = $this->db->prepare($query);
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        $stmt->bind_param("s", $eventType);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result === false) {
            throw new Exception("Execute failed: " . $this->db->error);
        }

        if ($result->num_rows > 0) {
            return $result->fetch_assoc()['event_id'];
        }

        // If no event type exists, create it
        $query = "INSERT INTO event (event_type) VALUES (?)";
        $stmt = $this->db->prepare($query);
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        $stmt->bind_param("s", $eventType);
        $stmt->execute();
        if ($stmt->error) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        return $this->db->insert_id;
    }

    // Add booking information
    private function addBookingInformation($customerId, $eventId, $arrivalDate, $leavingDate) {
        $query = "INSERT INTO booking_information (customer_id, event_id, arrival_date, leaving_date) 
                  VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        $stmt->bind_param("iiss", $customerId, $eventId, $arrivalDate, $leavingDate);
        $stmt->execute();
        if ($stmt->error) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        return $this->db->insert_id;
    }

    // Add number of people
    private function addNumberOfPeople($bookingId, $numberOfPeople) {
        $query = "INSERT INTO number_of_people (booking_id, number_of_people) VALUES (?, ?)";
        $stmt = $this->db->prepare($query);
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        $stmt->bind_param("ii", $bookingId, $numberOfPeople);
        $stmt->execute();
        if ($stmt->error) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
    }
}
?>
