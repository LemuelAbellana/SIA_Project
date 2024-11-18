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

        $stmt->bind_param("s", $username);  // Bind email parameter
        $stmt->execute();
        $result = $stmt->get_result();

        // If the user is found
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Ensure 'password' field is present before verifying it
            if (isset($user["password"])) {
                // Verify the password (hashed password in the database)
                if (password_verify($password, $user["password"])) {
                    // Set session variables
                    $_SESSION["user_id"] = $user["admin_id"];  // Store admin_id in session
                    $_SESSION["username"] = $user["email"];  // Store email as username

                    // Success response with redirect URL
                    $response = [
                        "status" => "success",
                        "redirect" => "../View/admin/booking_information.html",  // Adjust this URL as needed
                    ];
                } else {
                    // Incorrect password
                    $response["message"] = "Incorrect password. Please try again.";
                }
            } else {
                // Missing password field in the user record
                $response["message"] = "Password data is missing. Please contact support.";
            }
        } else {
            // User not found
            $response["message"] = "User not found. Please check your credentials.";
        }

        // Clean up
        $stmt->close();
        $conn->close();
    }
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

// Output JSON response
echo json_encode($response);
?>
