<?php
session_start();
include 'bookingdatabase.php';

$success_msg = [];
$error_msg = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate inputs
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $contact_number = mysqli_real_escape_string($conn, trim($_POST['contact_number']));
    $event_type = mysqli_real_escape_string($conn, trim($_POST['event_type']));
    $number_of_people = (int)$_POST['number_of_people'];
    $arrival_date = mysqli_real_escape_string($conn, $_POST['arrival_date']);
    $leaving_date = mysqli_real_escape_string($conn, $_POST['leaving_date']);
    $action = $_POST['action'];

    if (empty($name) || empty($email) || empty($contact_number) || empty($event_type) || empty($number_of_people) || empty($arrival_date) || empty($leaving_date)) {
        $error_msg[] = "Please fill in all fields.";
    } else {
        $check_query = "SELECT * FROM booking_information 
                        WHERE (arrival_date < '$leaving_date' AND leaving_date > '$arrival_date')";
        $check_result = mysqli_query($conn, $check_query);

        if (mysqli_num_rows($check_result) > 0) {
            $error_msg[] = "The venue is not available for the selected dates.";
        } else {
            if ($action === "check_availability") {
                $success_msg[] = "The venue is available for the selected dates.";
            } elseif ($action === "book_now") {
                mysqli_begin_transaction($conn);
                try {
                    // Retrieve or insert customer data
                    $customer_query = "SELECT customer_id FROM customer WHERE contact_number = '$contact_number'";
                    $customer_result = mysqli_query($conn, $customer_query);
                    if (mysqli_num_rows($customer_result) > 0) {
                        $customer_id = mysqli_fetch_assoc($customer_result)['customer_id'];
                    } else {
                        $insert_customer = "INSERT INTO customer (name, email, contact_number) VALUES ('$name', '$email', '$contact_number')";
                        if (!mysqli_query($conn, $insert_customer)) {
                            throw new Exception("Error adding customer: " . mysqli_error($conn));
                        }
                        $customer_id = mysqli_insert_id($conn);
                    }

                    // Retrieve or insert event data
                    $event_query = "SELECT event_id FROM event WHERE event_type = '$event_type'";
                    $event_result = mysqli_query($conn, $event_query);
                    if (mysqli_num_rows($event_result) > 0) {
                        $event_id = mysqli_fetch_assoc($event_result)['event_id'];
                    } else {
                        $insert_event = "INSERT INTO event (event_type) VALUES ('$event_type')";
                        if (!mysqli_query($conn, $insert_event)) {
                            throw new Exception("Error adding event: " . mysqli_error($conn));
                        }
                        $event_id = mysqli_insert_id($conn);
                    }

                    // Insert booking and people data
                    $insert_booking = "INSERT INTO booking_information (customer_id, event_id, arrival_date, leaving_date) 
                                       VALUES ($customer_id, $event_id, '$arrival_date', '$leaving_date')";
                    if (!mysqli_query($conn, $insert_booking)) {
                        throw new Exception("Error adding booking information: " . mysqli_error($conn));
                    }
                    $booking_id = mysqli_insert_id($conn);

                    $insert_people = "INSERT INTO number_of_people (booking_id, number_of_people) 
                                      VALUES ($booking_id, $number_of_people)";
                    if (!mysqli_query($conn, $insert_people)) {
                        throw new Exception("Error adding number of people: " . mysqli_error($conn));
                    }

                    // Commit and set session data
                    mysqli_commit($conn);
                    $_SESSION['booking_id'] = $booking_id;
                    $_SESSION['name'] = $name;
                    $_SESSION['email'] = $email;
                    $_SESSION['contact_number'] = $contact_number;
                    $_SESSION['event_type'] = $event_type;
                    $_SESSION['number_of_people'] = $number_of_people;
                    $_SESSION['arrival_date'] = $arrival_date;
                    $_SESSION['leaving_date'] = $leaving_date;

                    header("Location: myBooking.php");
                    exit();
                } catch (Exception $e) {
                    mysqli_rollback($conn);
                    $error_msg[] = $e->getMessage();
                }
            }
        }
    }
    mysqli_close($conn);
}

if (!empty($success_msg)) {
    foreach ($success_msg as $msg) {
        echo "<script>
                Swal.fire({
                    title: 'Success',
                    html: '$msg',
                    icon: 'success',
                    showConfirmButton: true,
                    confirmButtonText: 'OK'
                });
              </script>";
    }
}

if (!empty($error_msg)) {
    foreach ($error_msg as $msg) {
        echo "<script>
                Swal.fire({
                    title: 'Error',
                    html: '$msg',
                    icon: 'error',
                    showConfirmButton: true,
                    confirmButtonText: 'OK'
                });
              </script>";
    }
}
?>
