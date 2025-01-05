<?php
session_start();

// Ensure JSON responses are always sent with the correct header
header('Content-Type: application/json');

if (!isset($_SESSION['owner_email'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in.']);
    exit();
}

$ownerEmail = $_SESSION['owner_email'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "owner_registration";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Connection failed: ' . $conn->connect_error]);
    exit();
}

$action = $_POST['action'] ?? null;

// Handle Add Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
    $registration = $_POST['registration'];
    $model = $_POST['model'];
    $image = $_FILES['image'];

    // Check for duplicate registration
    $stmt = $conn->prepare("SELECT id FROM cars WHERE registration_number = ?");
    $stmt->bind_param("s", $registration);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'Registration number already exists']);
        $stmt->close();
        exit();
    }
    $stmt->close();

    // Validate and upload image
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $fileExtension = pathinfo($image["name"], PATHINFO_EXTENSION);
    if (!in_array($fileExtension, $allowedExtensions)) {
        echo json_encode(['success' => false, 'error' => 'Invalid file type']);
        exit();
    }

    $targetDir = "uploads/car_photos/";
    $targetFile = $targetDir . basename($image["name"]);
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    if (move_uploaded_file($image["tmp_name"], $targetFile)) {
        $stmt = $conn->prepare("INSERT INTO cars (owner_email, registration_number, car_model, car_image_path) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $ownerEmail, $registration, $model, $targetFile);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to upload image']);
    }
}

// Handle Edit Action
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'edit') {
    $id = $_POST['id'];
    $registration = $_POST['registration'];
    $model = $_POST['model'];
    $image = $_FILES['image'];

    // Check for duplicate registration number excluding the current car
    $stmt = $conn->prepare("SELECT id FROM cars WHERE registration_number = ? AND id != ?");
    $stmt->bind_param("si", $registration, $id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'Registration number already exists']);
        $stmt->close();
        exit();
    }
    $stmt->close();

    // Update car details
    $updateImageQuery = "";
    if (!empty($image['name'])) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $fileExtension = pathinfo($image["name"], PATHINFO_EXTENSION);
        if (!in_array($fileExtension, $allowedExtensions)) {
            echo json_encode(['success' => false, 'error' => 'Invalid file type']);
            exit();
        }

        $targetDir = "uploads/car_photos/";
        $targetFile = $targetDir . basename($image["name"]);
        if (move_uploaded_file($image["tmp_name"], $targetFile)) {
            $updateImageQuery = ", car_image_path = '$targetFile'";
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to upload image']);
            exit();
        }
    }

    $stmt = $conn->prepare("UPDATE cars SET registration_number = ?, car_model = ? $updateImageQuery WHERE id = ?");
    $stmt->bind_param("ssi", $registration, $model, $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
    $stmt->close();
}

// Handle Get Cars
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $conn->prepare("SELECT * FROM cars WHERE owner_email = ?");
    $stmt->bind_param("s", $ownerEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    $cars = [];
    while ($row = $result->fetch_assoc()) {
        $cars[] = $row;
    }

    echo json_encode($cars);
    $stmt->close();
}

// Handle Delete Action
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete') {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM cars WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
    $stmt->close();
}

$conn->close();
?>
