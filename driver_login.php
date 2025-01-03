<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "owner_registration");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form inputs
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch the driver details from the database
    $stmt = $conn->prepare("SELECT * FROM driver_details WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Verify the password
        //if ($password === $row['password']) { // Plain text comparison

        if (password_verify($password, $row['password'])) {
            session_start();

            // Store driver details in session
            $_SESSION['driver_id'] = $row['id'];
            $_SESSION['driver_name'] = $row['driver_name'];
            $_SESSION['driver_email'] = $row['email'];
            $_SESSION['driver_phone'] = $row['phone'];
            $_SESSION['car_registration_number'] = $row['car_registration_number'];
            $_SESSION['driver_photo'] = $row['driver_photo'];
            $_SESSION['license_photo'] = $row['license_photo'];
            header("Location: driver_profile.php");
            exit();
        } else {
            echo "<script>alert('Incorrect password.'); window.location.href='driver_login.html';</script>";
        }
    } else {
        echo "<script>alert('No user found with this email.'); window.location.href='driver_login.html';</script>";
    }
}
$conn->close();
?>
