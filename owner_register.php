<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'owner_registration');

    if ($conn->connect_error) {
        echo "<script>alert('Connection failed.');</script>";
        exit();
    }

    // Sanitize input values and check if they are empty
    $owner_name = isset($_POST['owner-name']) ? $conn->real_escape_string($_POST['owner-name']) : '';
    $email = isset($_POST['email']) ? $conn->real_escape_string($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? $conn->real_escape_string($_POST['phone']) : '';
    $password = isset($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : '';
    $car_model = isset($_POST['car-model']) ? $conn->real_escape_string($_POST['car-model']) : '';
    $car_registration_number = isset($_POST['car_registration_number']) ? $conn->real_escape_string($_POST['car_registration_number']) : '';

    // // Check if any required fields are empty
    // if (empty($owner_name) || empty($email) || empty($phone) || empty($password) || empty($car_model) || empty($car_registration_number) || empty($_FILES['car-photo']['name']) || empty($_FILES['owner-photo']['name']) || empty($_FILES['license-photo']['name'])) {
    //     echo "<script>alert('All fields are required.');</script>";
    //     exit();
    // }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format.');</script>";
        exit();
    }

    // Check if the owner already exists
    $owner_check = $conn->prepare("SELECT owner_id FROM owner_details WHERE email = ?");
    $owner_check->bind_param("s", $email);
    $owner_check->execute();
    $owner_check->store_result();
    if ($owner_check->num_rows > 0) {
        echo "<script>alert('Owner already registered.');window.location.href = 'owner_registration.html';</script>";
        exit();
    }
    $owner_check->close();

    // Check if the car registration number already exists
    $car_check = $conn->prepare("SELECT id FROM cars WHERE registration_number = ?");
    $car_check->bind_param("s", $car_registration_number);
    $car_check->execute();
    $car_check->store_result();
    if ($car_check->num_rows > 0) {
        echo "<script>alert('Car registration number already exists.');window.location.href = 'owner_registration.html';</script>";
        exit();
    }
    $car_check->close();

    // Handle file uploads
    $car_photo = uploadFile($_FILES['car-photo'], 'car_photos');
    $owner_photo = uploadFile($_FILES['owner-photo'], 'owner_photos');
    $owner_license_photos = uploadFile($_FILES['license-photo'], 'owner_license_photos');

    if (!$car_photo || !$owner_photo || !$owner_license_photos) {
        echo "<script>alert('Error uploading files.');window.location.href = 'owner_registration.html';</script>";
        exit();
    }

    // Insert data into the database for owner details
    $sql = "INSERT INTO owner_details (owner_name, email, phone, password, car_model, car_photo, car_registration_number, owner_photo, owner_license_photos) 
            VALUES ('$owner_name', '$email', '$phone', '$password', '$car_model', '$car_photo', '$car_registration_number', '$owner_photo', '$owner_license_photos')";

    if ($conn->query($sql) === TRUE) {
        session_start();
        $owner_id = $conn->insert_id;
        $_SESSION['owner_id'] = $owner_id;
        $_SESSION['owner_name'] = $owner_name;
        $_SESSION['email'] = $email;

        // Insert the car details into the cars table using the form input values
        $car_photo_path = $car_photo ? $car_photo : "uploads/car_photos/default_car.jpg"; // Use uploaded photo or default if not uploaded
        $default_car_sql = "INSERT INTO cars (owner_email, registration_number, car_model, car_image_path) 
                            VALUES ('$email', '$car_registration_number', '$car_model', '$car_photo_path')";

        if ($conn->query($default_car_sql) === TRUE) {
            echo "<script>alert('Owner and car added successfully'); window.location.href='owner_profile.php';</script>";
            exit();
        } else {
            echo "<script>alert('Error adding car: " . $conn->error . "');</script>";
            exit();
        }
    } else {
        echo "<script>alert('Error registering owner: " . $conn->error . "');</script>";
        exit();
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
        return false;
    }
}
?> 