@ -0,0 +1,48 @@
<?php
session_start();

// Include the database connection
include 'bookingdatabase.php';

// Check if the request method is POST and booking_id is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    // Sanitize the booking_id
    $booking_id = mysqli_real_escape_string($conn, $_POST['booking_id']);

    // Start transaction
    mysqli_begin_transaction($conn);

    try {
        // Delete from booking_information (will cascade delete from number_of_people due to foreign key)
        $delete_booking = "DELETE FROM booking_information WHERE booking_id = $booking_id";
        if (!mysqli_query($conn, $delete_booking)) {
            throw new Exception("Error deleting booking: " . mysqli_error($conn));
        }

        // Commit transaction
        mysqli_commit($conn);

        // Redirect to a confirmation page or back to the home page
        header("Location: cancellationConfirmation.php");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_roll_back($conn);
        // Optionally, log the error or display an error message
        echo "<script>
                Swal.fire({
                    title: 'Error',
                    text: '" . addslashes($e->getMessage()) . "',
                    icon: 'error',
                    showConfirmButton: true,
                    confirmButtonText: 'OK'
                });
              </script>";
    }

    // Close the connection
    mysqli_close($conn);
} else {
    echo "Invalid request.";
}
?>