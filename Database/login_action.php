<?php
session_start();
require_once __DIR__ . "/database.php";

// Ensure JSON response
header('Content-Type: application/json');

// Initialize default response
$response = [
    "status" => "error",
    "message" => "An unexpected error occurred.",
];

try {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Ensure that both fields are set and not empty
        $username = isset($_POST["username"]) ? trim($_POST["username"]) : null;
        $password = isset($_POST["password"]) ? trim($_POST["password"]) : null;

        // Validate input
        if (empty($username) || empty($password)) {
            throw new Exception("Username and password are required.");
        }

        // Initialize database connection
        $db = new Database();
        $conn = $db->getConnection();

        // Query to find the user (ensure 'admin_id' is included in the SELECT statement)
        $sql = "SELECT admin_id, email, password FROM admins WHERE email = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Check if 'admin_id' exists in the fetched user array
            if (isset($user["admin_id"]) && isset($user["password"])) {
                // Verify password
                if (password_verify($password, $user["password"])) {
                    $_SESSION["user_id"] = $user["admin_id"];  // Set the session with 'admin_id'
                    $_SESSION["username"] = $user["email"];  // Store the email as username (or adjust if necessary)

                    // Success response
                    $response = [
                        "status" => "success",
                        "redirect" => "../View/admin/booking_information.html",
                    ];
                } else {
                    $response["message"] = "Incorrect password. Please try again.";
                }
            } else {
                $response["message"] = "User data is missing. Please contact support.";
            }
        } else {
            $response["message"] = "User not found. Please check your credentials.";
        }

        $stmt->close();
        $conn->close();
    }
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

// Output JSON response
echo json_encode($response);
?>
