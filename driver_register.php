<?php
$conn = new mysqli('localhost', 'root', '', 'owner_registration');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve POST data
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

if (!move_uploaded_file($_FILES['driver_photo']['tmp_name'], $driverPhotoPath) ||
    !move_uploaded_file($_FILES['license_photo']['tmp_name'], $licensePhotoPath)) {
    die("Error uploading files. Please check folder permissions.");
}

// Check car registration number in owner_details
$checkCarRegQuery = "SELECT * FROM owner_details WHERE car_registration_number = '$car_registration_number'";
$carRegResult = $conn->query($checkCarRegQuery);

if ($carRegResult->num_rows > 0) {
    // Hash password and insert data
    // $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $insertQuery = "INSERT INTO driver_details (driver_name, email, phone, password, car_registration_number, driver_photo, license_photo)
                    VALUES ('$driver_name', '$email', '$phone', '$password', '$car_registration_number', '$driverPhotoPath', '$licensePhotoPath')";

    if ($conn->query($insertQuery)) {
        echo "Driver registered successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "Car registration number not found in owner records.";
}

$conn->close();
?>
