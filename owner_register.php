<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'owner_registration');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Sanitize input values
    $owner_name = isset($_POST['owner-name']) ? $conn->real_escape_string($_POST['owner-name']) : '';
    $email = isset($_POST['email']) ? $conn->real_escape_string($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? $conn->real_escape_string($_POST['phone']) : '';
    $password = isset($_POST['password']) ? $conn->real_escape_string($_POST['password']) : ''; /*? password_hash($_POST['password'], PASSWORD_DEFAULT) : '';*/
    $car_model = isset($_POST['car-model']) ? $conn->real_escape_string($_POST['car-model']) : '';
    $car_registration_number = isset($_POST['car_registration_number']) ? $conn->real_escape_string($_POST['car_registration_number']) : '';

    // Handle file uploads
    $car_photo = isset($_FILES['car-photo']) ? uploadFile($_FILES['car-photo'], 'car_photos') : '';
    $owner_photo = isset($_FILES['owner-photo']) ? uploadFile($_FILES['owner-photo'], 'owner_photos') : '';
    $owner_license_photos = isset($_FILES['license-photo']) ? uploadFile($_FILES['license-photo'], 'owner_license_photos') : '';

    // Insert data into the database
    $sql = "INSERT INTO owner_details (owner_name, email, phone, password, car_model, car_photo, car_registration_number, owner_photo, owner_license_photos) 
            VALUES ('$owner_name', '$email', '$phone', '$password', '$car_model', '$car_photo', '$car_registration_number', '$owner_photo', '$owner_license_photos')";

    if ($conn->query($sql) === TRUE) {
        echo "Registration successful!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}

// Function to handle file upload
function uploadFile($file, $folder) {
    // Create directory if it doesn't exist
    $target_dir = "uploads/" . $folder . "/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true); // Set proper permissions
    }

    $target_file = $target_dir . basename($file["name"]);
    
    // Check if the file is a valid image (optional)
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $target_file;
    } else {
        return '';  // Return empty string if upload fails
    }
}
?>