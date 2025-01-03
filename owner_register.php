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
    // $password = isset($_POST['password']) ? $conn->real_escape_string($_POST['password']) : '';  Encrypt into hash password()
    $password = isset($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : '';
    $car_model = isset($_POST['car-model']) ? $conn->real_escape_string($_POST['car-model']) : '';
    $car_registration_number = isset($_POST['car_registration_number']) ? $conn->real_escape_string($_POST['car_registration_number']) : '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format.";
        exit();
    }

    // Handle file uploads
    $car_photo = isset($_FILES['car-photo']) ? uploadFile($_FILES['car-photo'], 'car_photos') : '';
    $owner_photo = isset($_FILES['owner-photo']) ? uploadFile($_FILES['owner-photo'], 'owner_photos') : '';
    $owner_license_photos = isset($_FILES['license-photo']) ? uploadFile($_FILES['license-photo'], 'owner_license_photos') : '';

    // Insert data into the database
    $sql = "INSERT INTO owner_details (owner_name, email, phone, password, car_model, car_photo, car_registration_number, owner_photo, owner_license_photos) 
            VALUES ('$owner_name', '$email', '$phone', '$password', '$car_model', '$car_photo', '$car_registration_number', '$owner_photo', '$owner_license_photos')";

    if ($conn->query($sql) === TRUE) {
        session_start();
        $owner_id = $conn->insert_id;
        $_SESSION['owner_id'] = $owner_id;
        $_SESSION['owner_name'] = $owner_name;
        $_SESSION['email'] = $email;

        header("Location: owner_profile.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}

// Function to handle file upload
function uploadFile($file, $folder)
{
    // Create directory if it doesn't exist
    $target_dir = "uploads/" . $folder . "/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true); // Set proper permissions
    }

    $unique_name = uniqid() . "_" . basename($file["name"]);
    $target_file = $target_dir . $unique_name;

    // Attempt file upload
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $target_file;
    } else {
        echo "Error uploading " . $file["name"];
        return '';
    }
}
?>
