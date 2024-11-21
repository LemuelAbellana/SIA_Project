<?php
require_once 'BaseDatabase.php';
require_once 'Database.php';

class BookingDatabase extends BaseDatabase {
    public function checkAvailability($arrivalDate, $leavingDate) {
        $query = "SELECT * FROM booking_information 
                  WHERE (arrival_date BETWEEN ? AND ?) 
                  OR (leaving_date BETWEEN ? AND ?)
                  OR (? BETWEEN arrival_date AND leaving_date) 
                  OR (? BETWEEN arrival_date AND leaving_date)";
        $stmt = $this->db->prepare($query);

        if (!$stmt) {
            throw new Exception("Error preparing statement: " . $this->db->error);
        }

        $stmt->bind_param("ssssss", $arrivalDate, $leavingDate, $arrivalDate, $leavingDate, $arrivalDate, $leavingDate);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function book($name, $email, $contactNumber, $eventType, $arrivalDate, $leavingDate, $numberOfPeople) {
        $this->db->begin_transaction();

        try {
            if (strtotime($arrivalDate) >= strtotime($leavingDate)) {
                throw new Exception("Arrival date must be before leaving date.");
            }

            $customerId = $this->getCustomerIdByContactNumber($contactNumber) ?? $this->addCustomer($name, $email, $contactNumber);
            $eventId = $this->getEventId($eventType);
            $bookingId = $this->addBookingInformation($customerId, $eventId, $arrivalDate, $leavingDate);

            $this->addNumberOfPeople($bookingId, $numberOfPeople);

            $this->db->commit();
            return $bookingId;

        } catch (Exception $e) {
            $this->db->rollback();
            throw new Exception("Booking failed: " . $e->getMessage());
        }
    }

// Delete a booking
public function deleteBooking($bookingId) {
    try {
        $query = "DELETE FROM booking_information WHERE booking_id = ?";
        $stmt = $this->db->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        // Bind the booking_id parameter
        $stmt->bind_param("i", $bookingId);

        // Execute the query
        $stmt->execute();

        // Check if any row was affected by the delete operation
        if ($stmt->affected_rows > 0) {
            return true; // Booking deleted successfully
        } else {
            return false; // No rows were deleted (maybe the booking does not exist)
        }
    } catch (Exception $e) {
        error_log("Error in deleteBooking: " . $e->getMessage());
        return false; // Return false if there was an error
    }
}

    // Get customer ID by contact number
    private function getCustomerIdByContactNumber($contactNumber) {
        try {
            $query = "SELECT customer_id FROM customer WHERE contact_number = ?";
            $stmt = $this->db->prepare($query);
            if ($stmt === false) {
                throw new Exception("Prepare failed in getCustomerIdByContactNumber: " . $this->db->error);
            }

            $stmt->bind_param("s", $contactNumber);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result === false) {
                throw new Exception("Execute failed in getCustomerIdByContactNumber: " . $this->db->error);
            }

            return $result->num_rows > 0 ? $result->fetch_assoc()['customer_id'] : null;
        } catch (Exception $e) {
            error_log("Error in getCustomerIdByContactNumber: " . $e->getMessage());
            throw $e;
        }
    }

    // Add a new customer
    private function addCustomer($name, $email, $contactNumber) {
        try {
            $query = "INSERT INTO customer (name, email, contact_number) VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($query);
            if ($stmt === false) {
                throw new Exception("Prepare failed in addCustomer: " . $this->db->error);
            }

            $stmt->bind_param("sss", $name, $email, $contactNumber);
            $stmt->execute();

            if ($stmt->error) {
                throw new Exception("Execute failed in addCustomer: " . $stmt->error);
            }

            return $this->db->insert_id;
        } catch (Exception $e) {
            error_log("Error in addCustomer: " . $e->getMessage());
            throw $e;
        }
    }

    // Get event ID by event type or create a new event
    private function getEventId($eventType) {
        try {
            $query = "SELECT event_id FROM event WHERE event_type = ?";
            $stmt = $this->db->prepare($query);
            if ($stmt === false) {
                throw new Exception("Prepare failed in getEventId: " . $this->db->error);
            }

            $stmt->bind_param("s", $eventType);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result === false) {
                throw new Exception("Execute failed in getEventId: " . $this->db->error);
            }

            if ($result->num_rows > 0) {
                return $result->fetch_assoc()['event_id'];
            }

            // If no event type exists, create it
            $query = "INSERT INTO event (event_type) VALUES (?)";
            $stmt = $this->db->prepare($query);
            if ($stmt === false) {
                throw new Exception("Prepare failed in addEvent: " . $this->db->error);
            }

            $stmt->bind_param("s", $eventType);
            $stmt->execute();
            if ($stmt->error) {
                throw new Exception("Execute failed in addEvent: " . $stmt->error);
            }

            return $this->db->insert_id;
        } catch (Exception $e) {
            error_log("Error in getEventId: " . $e->getMessage());
            throw $e;
        }
    }

    // Add booking information
    private function addBookingInformation($customerId, $eventId, $arrivalDate, $leavingDate) {
        try {
            $query = "INSERT INTO booking_information (customer_id, event_id, arrival_date, leaving_date) 
                      VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            if ($stmt === false) {
                throw new Exception("Prepare failed in addBookingInformation: " . $this->db->error);
            }

            $stmt->bind_param("iiss", $customerId, $eventId, $arrivalDate, $leavingDate);
            $stmt->execute();

            if ($stmt->error) {
                throw new Exception("Execute failed in addBookingInformation: " . $stmt->error);
            }

            return $this->db->insert_id;
        } catch (Exception $e) {
            error_log("Error in addBookingInformation: " . $e->getMessage());
            throw $e;
        }
    }

    // Add number of people
    private function addNumberOfPeople($bookingId, $numberOfPeople) {
        try {
            $query = "INSERT INTO number_of_people (booking_id, number_of_people) VALUES (?, ?)";
            $stmt = $this->db->prepare($query);
            if ($stmt === false) {
                throw new Exception("Prepare failed in addNumberOfPeople: " . $this->db->error);
            }

            $stmt->bind_param("ii", $bookingId, $numberOfPeople);
            $stmt->execute();

            if ($stmt->error) {
                throw new Exception("Execute failed in addNumberOfPeople: " . $stmt->error);
            }
        } catch (Exception $e) {
            error_log("Error in addNumberOfPeople: " . $e->getMessage());
            throw $e;
        }
    }

        // Fetch all bookings with search, pagination, and encapsulated logic
        public function getAll($limit, $offset, $search = '') {
            // Prepare query for paginated results
            $query = "SELECT 
                        b.booking_id,
                        b.customer_id,
                        c.name,
                        c.email,
                        b.event_id,
                        e.event_type,
                        b.arrival_date,
                        b.leaving_date,
                        np.number_of_people,
                        c.contact_number
                      FROM booking_information b
                      INNER JOIN customer c ON b.customer_id = c.customer_id
                      INNER JOIN event e ON b.event_id = e.event_id
                      LEFT JOIN number_of_people np ON b.booking_id = np.booking_id
                      WHERE c.name LIKE ? OR c.email LIKE ?
                      LIMIT ? OFFSET ?";
            
            $stmt = $this->db->prepare($query);
            if (!$stmt) {
                throw new Exception("Error preparing paginated query: " . $this->db->error);
            }
        
            $searchTerm = "%$search%";
            $stmt->bind_param("ssii", $searchTerm, $searchTerm, $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            $bookings = $result->fetch_all(MYSQLI_ASSOC);
        
            // Prepare query for total count
            $countQuery = "SELECT COUNT(*) AS total 
                           FROM booking_information b
                           INNER JOIN customer c ON b.customer_id = c.customer_id
                           WHERE c.name LIKE ? OR c.email LIKE ?";
            
            $countStmt = $this->db->prepare($countQuery);
            if (!$countStmt) {
                throw new Exception("Error preparing count query: " . $this->db->error);
            }
        
            $countStmt->bind_param("ss", $searchTerm, $searchTerm);
            $countStmt->execute();
            $total = $countStmt->get_result()->fetch_assoc()['total'];
        
            // Calculate startIndex and endIndex
            $startIndex = $offset + 1;
            $endIndex = min($offset + $limit, $total);
        
            // Return bookings, total count, startIndex, and endIndex
            return [
                'bookings' => $bookings,
                'totalEntries' => $total,
                'startIndex' => $startIndex,
                'endIndex' => $endIndex
            ];
        }
        public function getDetailsById($id) {
            $query = "SELECT * FROM booking_information WHERE booking_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        }
    
        public function updateById($id, $name, $email, $arrivalDate, $leavingDate, $numberOfPeople, $contactNumber) {
            $query = "UPDATE booking_information 
                      SET name = ?, email = ?, arrival_date = ?, leaving_date = ?, number_of_people = ?, contact_number = ?
                      WHERE booking_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ssssisi", $name, $email, $arrivalDate, $leavingDate, $numberOfPeople, $contactNumber, $id);
            return $stmt->execute();
        }
    
        public function deleteById($id) {
            $query = "DELETE FROM booking_information WHERE booking_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id);
            return $stmt->execute();
        }
    // Get last inserted booking ID
    public function getLastBookingId() {
        return $this->db->insert_id;
    }
}
?>
