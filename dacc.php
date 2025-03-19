<?php

// Ensure session is started properly
ini_set('session.gc_maxlifetime', 86400); // Keep session alive for 24 hours
session_start();
header("Content-Type: application/json");

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$conn = new mysqli("localhost", "root", "", "owner_registration");
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed."]);
    exit;
}

// Read JSON input from request
$data = json_decode(file_get_contents("php://input"), true);

// ✅ Allow `driver_id` from JSON if session is missing
if (!isset($_SESSION['driver_id']) && isset($data['driver_id'])) {
    $_SESSION['driver_id'] = $data['driver_id'];
}

// ✅ Check if user is logged in using `driver_id`
if (!isset($_SESSION['driver_id'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in. Redirecting to login page."]);
    exit;
}

$driver_id = $_SESSION['driver_id'];  // ✅ Always use session driver_id

// Function to sanitize input
function cleanInput($input) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($input));
}

// ✅ Fetch user details using `driver_id`
if (isset($data['fetchUserDetails'])) {
    $query = "SELECT driver_id, driver_name, email, phone, car_registration_number, driver_photo, license_photo 
              FROM driver_details WHERE driver_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $driver_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result) {
        echo json_encode(["status" => "success", "user" => $result]);
    } else {
        echo json_encode(["status" => "error", "message" => "User not found."]);
    }
    exit;
}

// ✅ Update Profile Photo
if (isset($_POST['updatePhoto'])) {
    if (!isset($_SESSION['driver_id'])) {
        echo json_encode(["status" => "error", "message" => "Session expired. Please log in again."]);
        exit;
    }

    if (!isset($_FILES['newPhoto'])) {
        echo json_encode(["status" => "error", "message" => "No file uploaded."]);
        exit;
    }

    $photo = $_FILES['newPhoto'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];

    if (!in_array($photo['type'], $allowedTypes)) {
        echo json_encode(["status" => "error", "message" => "Invalid file type. Only JPG and PNG allowed."]);
        exit;
    }

    $photoPath = "uploads/" . basename($photo["name"]);
    move_uploaded_file($photo["tmp_name"], $photoPath);

    $query = "UPDATE driver_details SET driver_photo = ? WHERE driver_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $photoPath, $driver_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Profile photo updated successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update profile photo."]);
    }
    exit;
}

// ✅ Update Phone Number
if (isset($data['updatePhone'])) {
    $newPhone = cleanInput($data['phone']);
    $query = "UPDATE driver_details SET phone = ? WHERE driver_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $newPhone, $driver_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Phone number updated."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update phone number."]);
    }
    exit;
}

// ✅ Update Password
if (isset($data['updatePassword'])) {
    $currentPassword = cleanInput($data['current_password']);
    $newPassword = cleanInput($data['new_password']);
    $confirmPassword = cleanInput($data['confirm_password']);

    if ($newPassword !== $confirmPassword) {
        echo json_encode(["status" => "error", "message" => "New passwords do not match."]);
        exit;
    }

    $query = "SELECT password FROM driver_details WHERE driver_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $driver_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if (!$result || !password_verify($currentPassword, $result['password'])) {
        echo json_encode(["status" => "error", "message" => "Current password is incorrect."]);
        exit;
    }

    $newPasswordHash = password_hash($newPassword, PASSWORD_BCRYPT);
    $query = "UPDATE driver_details SET password = ? WHERE driver_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $newPasswordHash, $driver_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Password updated successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update password."]);
    }
    exit;
}

// ✅ Delete Account (Fixed)
if (isset($data['deleteAccount'])) {
    $query = "DELETE FROM driver_details WHERE driver_id = ?"; 
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $driver_id); 

    if ($stmt->execute()) {
        session_destroy();
        echo json_encode(["status" => "success", "message" => "Account deleted successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete account."]);
    }
    exit;
}
// ✅ Ensure every request exits early when handled
error_log("Handling request: " . json_encode($data));

// ✅ If no valid action was requested, send a single error response
if (empty($data)) {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}

$conn->close();
?>
