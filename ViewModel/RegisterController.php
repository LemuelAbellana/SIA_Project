<?php
require_once __DIR__ . "/../Database/adminModel.php"; // Corrected the path

header("Content-Type: application/json");

try {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data) {
            throw new Exception("Invalid JSON data.");
        }

        $adminModel = new AdminModel();

        // Validation logic
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
        if ($adminModel->isEmailExists($data['email'])) {
            $errors[] = "Email already exists.";
        }

        // If validation fails, return errors
        if (!empty($errors)) {
            echo json_encode(["success" => false, "errors" => $errors]);
            exit;
        }

        // Hash the password and register the user
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        $isRegistered = $adminModel->registerAdmin($data['username'], $data['email'], $passwordHash);

        // Return success or failure
        echo json_encode(["success" => $isRegistered]);
    } else {
        throw new Exception("Invalid request method.");
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "errors" => [$e->getMessage()]]);
}
?>
