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
    $query = "SELECT * FROM driver_details WHERE email = '$email'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Verify the password
        if ($password === $row['password']) { // Plain text comparison
            echo "Login successful. Welcome, " . htmlspecialchars($row['driver_name']) . "!";
        } else {
            echo "Incorrect password.";
        }
    } else {
        echo "No user found with this email.";
    }
}

$conn->close();
?>
