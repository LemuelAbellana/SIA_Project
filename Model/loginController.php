<?php
session_start();

// Adjust the include paths to the correct location of the required files
require_once __DIR__ . "/../Database/database.php";  // Path to the Database class
require_once __DIR__ . "/../Database/LoginDatabase.php";  // Path to the LoginDatabase class

class LoginController {

    private $db;
    private $loginDatabase;

    // Constructor initializes the database connection and LoginDatabase class
    public function __construct() {
        $this->db = new Database(); // Create Database object
        $conn = $this->db->getConnection(); // Get the connection
        $this->loginDatabase = new LoginDatabase($conn); // Pass the connection to LoginDatabase
    }

    // Main login handler function
    public function handleLoginRequest() {
        // Default response structure
        $response = [
            "status" => "error",
            "message" => "An unexpected error occurred. Please try again.",
        ];

        try {
            if ($_SERVER["REQUEST_METHOD"] === "POST") {
                // Sanitize and validate the input
                $username = isset($_POST["username"]) ? trim($_POST["username"]) : null;
                $password = isset($_POST["password"]) ? trim($_POST["password"]) : null;

                if (empty($username) || empty($password)) {
                    throw new Exception("Username and password are required.");
                }

                // Validate the email format of the username (email)
                if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Invalid email format.");
                }

                // Fetch user data by email using LoginDatabase
                $userData = $this->loginDatabase->getUserByEmail($username);

                if ($userData) {
                    // Ensure 'password' field is present before verifying it
                    if (isset($userData["password"])) {
                        // Verify the password (hashed password in the database)
                        if (password_verify($password, $userData["password"])) {
                            // Correct credentials: set session variables
                            $_SESSION["user_id"] = $userData["admin_id"];  // Store admin_id in session
                            $_SESSION["username"] = $userData["email"];    // Store email as username

                            // Optionally regenerate session ID to prevent session fixation attacks
                            session_regenerate_id(true);

                            // Success response with redirect URL
                            $response = [
                                "status" => "success",
                                "redirect" => "../View/admin/booking_information.html",  // Redirect URL after successful login
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
            }
        } catch (Exception $e) {
            // Catch any exception, log it, and show a user-friendly message
            $response["message"] = $e->getMessage();
        }

        // Return JSON response
        echo json_encode($response);
    }
}

// Instantiate LoginController and handle the request
$loginController = new LoginController();
$loginController->handleLoginRequest();
?>
