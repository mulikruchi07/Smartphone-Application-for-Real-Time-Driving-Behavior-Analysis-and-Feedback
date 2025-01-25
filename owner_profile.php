<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'owner_registration');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the owner details using the session's owner_id
$owner_id = $_SESSION['owner_id'];
$sql = "SELECT owner_name, email, phone, owner_photo FROM owner_details WHERE owner_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $owner = $result->fetch_assoc();
    $_SESSION['owner_email'] = $owner['email'];  // Store the email in the session inorder to retrive in car manage
} else {
    echo "Owner not found.";
    exit();
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        /* General Styles */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', Arial, sans-serif;
            background-color: #1E2A3A;
            color: #EDEDED;
            display: flex;
            flex-direction: column;
        }

        .container {
            width: 100%;
            max-width: 1400px;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 20px;
            padding-bottom: 100px;
        }

        .title {
            padding-top: 20px;
            padding-left: 5px;
            font-size: 26px;
            font-weight: 600;
            color: #d0a933;
            text-transform: uppercase;
            margin-bottom: 20px;
            margin-left: 10px;
            position: fixed;
            background-color: #1E2A3A;
            width: 100%;
            padding-bottom: 10px;
        }

        /* Profile Section */
        .stat {
            background-color: #283347;
            padding: 20px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
            color: #F9F9F9;
            box-shadow: inset 0px 3px 5px rgba(0, 0, 0, 0.3);
            width: 300px;
            max-width: 600px;
            margin-bottom: 30px;
            margin-top: 80px;

        }

        .profile-pic {
            height: 80px;
            width: 80px;
            border-radius: 15px;
            object-fit: cover;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.5);
        }

        .info {
            text-align: left;
            font-size: 14px;
            line-height: 1.6;
            font-family: 'Poppins', Arial, sans-serif;
        }

        .info p {
            margin: 5px 0;
        }

        .info strong {
            color: #d0a933;
        }

        /* Buttons Section */
        .button-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 30px;
            width: 90%;
            max-width: 600px;
        }

        .button {
            padding: 15px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            text-align: center;
            background-color: #26374D;
            color: #fff;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            width: 400px;
            height: 50px;
            font-family: 'Poppins', Arial, sans-serif;
        }

        .button i {
            margin-right: 10px;
        }

        .button:hover {
            background-color: #1b3554;
            transform: scale(1.05);
        }

        /* Sign Out Button */
        .out {
            padding: 15px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            text-align: center;
            background: linear-gradient(135deg, #FF4500, #FF6347);
            color: #fff;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            margin-top: 50px;
            width: 350px;
            max-width: 600px;
            font-family: 'Poppins', Arial, sans-serif;
        }

        a {
            text-decoration: none;
        }

        .out:hover {
            background: linear-gradient(135deg, #FF6347, #FF7F50);
            transform: scale(1.05);
        }

        /* Navigation Bar */
        .navbar {
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding: 15px 0;
            position: fixed;
            bottom: 0;
            width: 100%;
            background-color: #26374D;
            box-shadow: 0px -2px 5px rgba(0, 0, 0, 0.3);
            font-family: 'Poppins', Arial, sans-serif;
        }

        .nav-icon {
            font-size: 28px;
            color: #EDEDED;
            cursor: pointer;
            transition: color 0.3s ease-in-out;
        }

        .nav-icon:hover {
            color: #d0a933;
        }

        /* Responsive Design */

        @media (max-width: 400px) {
            .profile-pic {
                height: 70px;
                width: 70px;
            }

            .stat {
                width: 275px;
            }

            .info {
                font-size: 12px;
            }

            .button, .out {
                font-size: 14px;
                padding: 12px;
                width: 325px;
            }
            
            .out {
                width: 250px;
            }

            .nav-icon {
                font-size: 24px;
            }
        }

        @media (max-width: 350px) {
            .profile-pic {
                height: 70px;
                width: 70px;
            }

            .stat {
                width: 250px;
            }

            .info {
                font-size: 12px;
            }

            .button, .out {
                font-size: 14px;
                padding: 12px;
                width: 275px;
            }
            
            .out {
                width: 250px;
            }

            .nav-icon {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
        <div class="title">Owner Profile</div>

        <div class="container">

        <!-- Profile Section -->
        <div class="stat">
            <img src="<?php echo htmlspecialchars($owner['owner_photo']); ?>" class="profile-pic" alt="Owner Picture">
            <div class="info">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($owner['owner_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($owner['email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($owner['phone']); ?></p>
            </div>
        </div>

        <!-- Buttons Section -->
        <div class="button-container">
            <a href="car_manage.html"><button class="button"><i class="fas fa-car"></i>Manage Cars</button></a>
            <a href="driver_manage.php"><button class="button"><i class="fas fa-user-cog"></i>Manage Driver</button></a>
            <a href="#"><button class="button"><i class="fas fa-users"></i>Family</button></a>
            <a href="#"><button class="button"><i class="fas fa-paint-brush"></i>Theme</button></a>
            <a href="#"><button class="button"><i class="fas fa-cogs"></i>Account</button></a>
            <a href="#"><button class="button"><i class="fas fa-bell"></i>Notifications</button></a>
        </div>

        <!-- Sign Out Button -->
        <a href="Owner_login.html"><button class="out"><i class="fas fa-sign-out-alt"></i>Sign Out</button></a>
    </div>

    <!-- Navigation Bar -->
    <div class="navbar">
        <a href="dashboard.html"><i class="fas fa-tachometer-alt nav-icon"></i></a>
        <a href="drewards.html"><i class="fas fa-trophy nav-icon"></i></a>
        <a href="owner_profile.php"><i class="fas fa-user nav-icon"></i></a>
    </div>
</body>
</html>
