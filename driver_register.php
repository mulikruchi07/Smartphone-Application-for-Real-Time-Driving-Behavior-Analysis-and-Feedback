<?php
$conn = new mysqli('localhost', 'root', '', 'owner_registration');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve POST data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
$driver_name = $_POST['driver_name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$password = $_POST['password'] ?? '';
$car_registration_number = $_POST['car_registration_number'] ?? '';

// Directories
$baseDir = "uploads";
$driverPhotoDir = "$baseDir/driver_photos";
$licensePhotoDir = "$baseDir/license_photos";

// Check and create directories
if (!is_dir($baseDir)) mkdir($baseDir, 0777, true);
if (!is_dir($driverPhotoDir)) mkdir($driverPhotoDir, 0777, true);
if (!is_dir($licensePhotoDir)) mkdir($licensePhotoDir, 0777, true);

// File upload handling
$driverPhotoPath = $driverPhotoDir . '/' . basename($_FILES['driver_photo']['name']);
$licensePhotoPath = $licensePhotoDir . '/' . basename($_FILES['license_photo']['name']);

if (
    !move_uploaded_file($_FILES['driver_photo']['tmp_name'], $driverPhotoPath) ||
    !move_uploaded_file($_FILES['license_photo']['tmp_name'], $licensePhotoPath)
) {
    die("Error uploading files. Please check folder permissions.");
}

// Check if email already exists
$checkEmailQuery = "SELECT email FROM driver_details WHERE email = ?";
$stmt = $conn->prepare($checkEmailQuery);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo "<script>alert('Email already registered. Please use a different email.'); window.location.href='Driver_regis.html';</script>";
    exit();
}
$stmt->close();

// Check car registration number in owner_details
$checkCarRegQuery = "SELECT * FROM owner_details WHERE car_registration_number = ?";
$stmt = $conn->prepare($checkCarRegQuery);
$stmt->bind_param('s', $car_registration_number);
$stmt->execute();
$carRegResult = $stmt->get_result();

if ($carRegResult->num_rows > 0) {
    //$password = isset($_POST['password']) ? $conn->real_escape_string($_POST['password']) : '';  Normal Text password
    // Hash password and insert data
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    $insertQuery = "INSERT INTO driver_details (driver_name, email, phone, password, car_registration_number, driver_photo, license_photo)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param('sssssss', $driver_name, $email, $phone, $hashedPassword, $car_registration_number, $driverPhotoPath, $licensePhotoPath);

    if ($stmt->execute()) {
        session_start();
        $driver_id = $conn->insert_id; // Get the last inserted ID
    $_SESSION['driver_id'] = $driver_id;

        $_SESSION['driver_name'] = $driver_name;
        $_SESSION['driver_email'] = $email;
        $_SESSION['driver_phone'] = $phone;
        $_SESSION['car_registration_number'] = $car_registration_number;
        $_SESSION['driver_photo'] = $driverPhotoPath;
        $_SESSION['license_photo'] = $licensePhotoPath;

        header("Location: driver_profile.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
} else {
    echo "<script>alert('Car registration number not found in owner records.'); window.location.href='Driver_regis.html';</script>";
}
}
$stmt->close();
$conn->close();
?>
