<?php
require_once __DIR__ . "/../Database/registerAction.php"; // Correct path to the file

class RegisterController {

    private $registerAction;

    public function __construct() {
        // Initialize registerAction
        $this->registerAction = new RegisterAction();
    }

    // Handles the registration logic
    public function handleRequest() {
        header("Content-Type: application/json");

        try {
            if ($_SERVER["REQUEST_METHOD"] === "POST") {
                $data = $this->getRequestData();

                // Validate input data
                $validationErrors = $this->validateData($data);

                if (!empty($validationErrors)) {
                    echo json_encode(["success" => false, "errors" => $validationErrors]);
                    exit;
                }

                // Hash the password and attempt registration
                $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
                $isRegistered = $this->registerAction->registerAdmin($data['username'], $data['email'], $passwordHash);

                // Respond with success or failure
                echo json_encode(["success" => $isRegistered]);
            } else {
                throw new Exception("Invalid request method.");
            }
        } catch (Exception $e) {
            echo json_encode(["success" => false, "errors" => [$e->getMessage()]]);
        }
    }

    // Retrieve JSON data from the request
    private function getRequestData() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data) {
            throw new Exception("Invalid JSON data.");
        }
        return $data;
    }

    // Validate registration data
    private function validateData($data) {
        $errors = [];

        if (empty($data['username']) || empty($data['email']) || empty($data['password']) || empty($data['repeat_password'])) {
            $errors[] = "All fields are required.";
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email address.";
        }

        if (strlen($data['password']) < 8) {
            $errors[] = "Password must be at least 8 characters long.";
        }

        if ($data['password'] !== $data['repeat_password']) {
            $errors[] = "Passwords do not match.";
        }

        // Check if email exists in the database
        if ($this->registerAction->isEmailExists($data['email'])) {
            $errors[] = "Email already exists.";
        }

        return $errors;
    }
}

// Instantiate and handle the registration request
$controller = new RegisterController();
$controller->handleRequest();
?>
