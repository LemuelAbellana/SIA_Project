<?php
session_start();
require 'bookingdatabase.php'; // Database connection

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the booking ID from POST data
    $booking_id = $_POST['booking_id'] ?? null;

    if (!$booking_id) {
        $response['message'] = 'Booking ID is required.';
        echo json_encode($response);
        exit;
    }

    // Begin database transaction
    $conn->begin_transaction();

    try {
        // Delete the number of people record first
        $query = "DELETE FROM number_of_people WHERE booking_id = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) throw new Exception($conn->error);

        $stmt->bind_param('i', $booking_id);
        $stmt->execute();

        // Delete the booking information
        $query = "DELETE FROM booking_information WHERE booking_id = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) throw new Exception($conn->error);

        $stmt->bind_param('i', $booking_id);
        $stmt->execute();

        // Optionally delete customer information (if they don't have other bookings)
        $query = "DELETE FROM customer 
                  WHERE customer_id = (
                      SELECT customer_id FROM booking_information WHERE booking_id = ?
                  ) AND NOT EXISTS (
                      SELECT 1 FROM booking_information WHERE customer_id = (
                          SELECT customer_id FROM booking_information WHERE booking_id = ?
                      )
                  )";
        $stmt = $conn->prepare($query);
        if (!$stmt) throw new Exception($conn->error);

        $stmt->bind_param('ii', $booking_id, $booking_id);
        $stmt->execute();

        // Commit transaction
        $conn->commit();

        // Clear the booking data from the session
        unset($_SESSION['booking_data']);

        $response['success'] = true;
        $response['message'] = 'Your booking has been successfully canceled.';
        echo json_encode($response);
        exit;
    } catch (Exception $e) {
        // Rollback transaction if something goes wrong
        $conn->rollback();
        $response['message'] = "Cancellation failed. Please try again later. Error: " . $e->getMessage();
        echo json_encode($response);
        exit;
    }
}
?>
