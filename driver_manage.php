<?php
session_start();

// Ensure the owner is logged in
if (!isset($_SESSION['owner_email'])) {
    // If the owner is not logged in, redirect to the login page
    header("Location: owner_login.php");
    exit();
}

$owner_email = $_SESSION['owner_email']; // Get the logged-in owner's email from the session

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'owner_registration');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all cars owned by the logged-in owner using the owner_email
$stmt = $conn->prepare("SELECT * FROM cars WHERE owner_email = ?");
$stmt->bind_param("s", $owner_email);
$stmt->execute();
$cars_result = $stmt->get_result();

if ($cars_result->num_rows > 0) {
    // For each car, fetch related drivers
    while ($car = $cars_result->fetch_assoc()) {
        $car_registration_number = $car['registration_number'];

        // Fetch drivers for this car
        $driver_stmt = $conn->prepare("SELECT * FROM driver_details WHERE car_registration_number = ?");
        $driver_stmt->bind_param("s", $car_registration_number);
        $driver_stmt->execute();
        $drivers_result = $driver_stmt->get_result();
        
        // Display car details
        echo "<h2>Car: " . htmlspecialchars($car['car_model']) . " (" . htmlspecialchars($car['registration_number']) . ")</h2>";

        // If drivers are found for this car
        if ($drivers_result->num_rows > 0) {
            echo "<table border='1'>
                    <thead>
                        <tr>
                            <th>Driver Name</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>";

            // Display each driver for this car
            while ($driver = $drivers_result->fetch_assoc()) {
                echo "<tr>
                        <td>" . htmlspecialchars($driver['driver_name']) . "</td>
                        <td>" . htmlspecialchars($driver['phone']) . "</td>
                        <td>
                            <a href='edit_driver.php?driver_id=" . $driver['id'] . "'>Edit</a> | 
                            <a href='delete_driver.php?driver_id=" . $driver['id'] . "'>Delete</a>
                        </td>
                    </tr>";
            }

            echo "</tbody></table>";
        } else {
            echo "<p>No drivers assigned to this car.</p>";
        }

        echo "<hr>";
    }
} else {
    echo "<p>No cars found for your account.</p>";
}

$conn->close();
?>

