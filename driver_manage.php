<?php
session_start();

// Ensure the owner is logged in
if (!isset($_SESSION['owner_email'])) {
    header("Location: owner_login.php");
    exit();
}

$owner_email = $_SESSION['owner_email'];

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'owner_registration');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Delete Action
if (isset($_GET['delete_driver_id'])) {
    $driver_id = $_GET['delete_driver_id'];

    // Modify the query to delete the driver using the driver_id
    $delete_query = $conn->prepare("DELETE FROM driver_details WHERE driver_id = ?");
    $delete_query->bind_param("i", $driver_id);
    $delete_query->execute();

    // Redirect to refresh the page and display the updated list
    header("Location: driver_manage.php");
    exit();
}


// Fetch all cars owned by the logged-in owner using the owner_email
$stmt = $conn->prepare("SELECT * FROM cars WHERE owner_email = ?");
$stmt->bind_param("s", $owner_email);
$stmt->execute();
$cars_result = $stmt->get_result();

// Store car and driver data for rendering
$car_driver_data = [];

if ($cars_result->num_rows > 0) {
    while ($car = $cars_result->fetch_assoc()) {
        $car_registration_number = $car['registration_number'];

        // Fetch drivers for this car
        $driver_stmt = $conn->prepare("SELECT * FROM driver_details WHERE car_registration_number = ?");
        $driver_stmt->bind_param("s", $car_registration_number);
        $driver_stmt->execute();
        $drivers_result = $driver_stmt->get_result();
        $drivers = [];

        while ($driver = $drivers_result->fetch_assoc()) {
            $drivers[] = $driver;
        }

        $car_driver_data[] = [
            'car' => $car,
            'drivers' => $drivers,
        ];
    }
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Drivers</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="theme.js" defer></script>
    <style>
        /* Global styling */
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background-color: #1E2A3A;
            color: #FFFFFF;
        }

        /* Header styling */
        .header {
            position: fixed;
            width: 100%;
            top: 0;
            left: 0;
            color: #F4F4F8;
            padding: 15px 20px;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .back-button {
            color: #F4F4F8;
            font-size: 18px;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 6px;
            background-color: transparent;
            transition: background 0.3s ease;
        }

        .back-button:hover {
            background-color: #3C4A5C;
        }

        .title {
            font-size: 26px;
            font-weight: bold;
            text-align: center;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            flex-grow: 1;
        }

        /* Main container styling */
        .container {
            margin-top: 80px;
            padding: 20px;
        }

        .car-box {
            background-color: #26374D;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.15);
            margin-bottom: 20px;
        }

        .car-details {
            margin-bottom: 10px;
            font-size: 14px;
            color: rgb(255, 255, 255);
        }

        .driver-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .driver-item {
            display: block;
            padding: 12px;
            margin-bottom: 10px;
            background-color: #3C4A5C;
            border-radius: 8px;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
            color: #FFFFFF;
            font-size: 12px;
            position: relative;
            transition: transform 0.3s ease, background-color 0.3s ease;
        }

        .driver-item:hover {
            background-color: #4B5C72;
            transform: translateY(-5px);
        }

        .driver-item span {
            font-weight: 600;
        }

        .driver-item .phone {
            font-size: 14px;
            color: rgb(255, 255, 255);
        }

        .actions {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
        }

        .actions a {
            text-decoration: none;
            color: #FFA726;
            margin: 0 10px;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        .actions a:hover {
            color: #FF7043;
        }

        .empty-message {
            text-align: center;
            font-size: 18px;
            color: #BBBBBB;
        }


        .light-theme {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background-color: #fff;
        }

        /* Header styling */
        .light-theme .header {
            position: fixed;
            width: 100%;
            top: 0;
            left: 0;
            color: #2575fc;
            padding: 15px 20px;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .light-theme .back-button {
            color: #000;
            font-size: 18px;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 6px;
            background-color: transparent;
            transition: background 0.3s ease;
        }

        .light-theme .back-button:hover {
            background-color: #3C4A5C;
        }

        .light-theme .title {
            font-size: 26px;
            font-weight: bold;
            text-align: center;
            text-shadow: 2px 2px 4px rgba(255, 255, 255, 0.5);
            flex-grow: 1;
        }

        /* Main container styling */
        .light-theme .container {
            margin-top: 80px;
            padding: 20px;
        }

        .light-theme .car-box {
            background-color: #AECCE4;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.15);
            margin-bottom: 20px;
        }

        .light-theme .car-details {
            margin-bottom: 10px;
            font-size: 18px;
            color: rgb(4, 4, 4);
        }

        .light-theme .driver-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .light-theme .driver-item {
            display: block;
            padding: 12px;
            margin-bottom: 10px;
            background-color:rgb(83, 103, 132);
            border-radius: 8px;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
            color: #fff;
            font-size: 16px;
            position: relative;
            transition: transform 0.3s ease, background-color 0.3s ease;
        }

        .light-theme .driver-item:hover {
            background-color: #4B5C72;
            transform: translateY(-5px);
        }

        .light-theme .driver-item span {
            font-weight: 600;
        }

        .light-theme .driver-item .phone {
            font-size: 14px;
            color: rgb(13, 12, 12);
        }

        .light-theme .actions {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
        }

        .light-theme .actions a {
            text-decoration: none;
            color:#FF7043;
            margin: 0 10px;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        .light-theme .actions a:hover {
            color: #FF7043;
        }

        .light-theme .empty-message {
            text-align: center;
            font-size: 18px;
            color:rgb(255, 255, 255);
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .back-button {
                position: absolute;
                top: 15px;
                left: 20px;
            }

            .title {
                font-size: 22px;
            }
        }

        @media (max-width: 768px) {
            .light-theme .header {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .light-theme .back-button {
                position: absolute;
                top: 15px;
                left: 20px;
            }

            .light-theme .title {
                font-size: 22px;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <a href="owner_profile.php" class="back-button">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="title">Manage Drivers</h1>
    </div>

    <div class="container">
        <?php if (!empty($car_driver_data)): ?>
            <?php foreach ($car_driver_data as $data): ?>
                <div class="car-box">
                    <div class="car-details">
                        <strong>Car Model:</strong> <?= htmlspecialchars($data['car']['car_model']) ?> <br>
                        <strong>Registration Number:</strong> <?= htmlspecialchars($data['car']['registration_number']) ?>
                    </div>

                    <ul class="driver-list">
                        <?php if (!empty($data['drivers'])): ?>
                            <?php foreach ($data['drivers'] as $driver): ?>
                                <li class="driver-item">
                                    <span>Driver Name:</span> <?= htmlspecialchars($driver['driver_name']) ?><br>
                                    <span>Phone:</span> <?= htmlspecialchars($driver['phone']) ?><br>
                                    <div class="actions">
                                        <a href="driver_manage.php?delete_driver_id=<?= $driver['driver_id'] ?>" onclick="return confirm('Are you sure you want to delete this driver?')">Delete</a>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="empty-message">No drivers assigned to this car.</p>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="empty-message">No cars found for your account.</p>
        <?php endif; ?>
    </div>
</body>

</html>
