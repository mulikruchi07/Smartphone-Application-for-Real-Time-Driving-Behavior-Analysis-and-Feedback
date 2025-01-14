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
    <style>

        body {
            margin: 0;
            font-family: 'Poppins', Arial, sans-serif;
            background-color: #101820;
            color: #EDEDED;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            width: 95%;
            max-width: 480px;
            padding: 20px;
            background: linear-gradient(145deg, #1E1E1E, #2A2A2A);
            border-radius: 20px;
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.5);
            text-align: center;
        }

        .title {
            font-size: 26px;
            font-weight: bold;
            color: #FFA500; 
            text-transform: uppercase;
            margin-bottom: 25px;
        }

        .stat {
            background: linear-gradient(135deg, #232323, #333333);
            padding: 15px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
            color: #F9F9F9;
            box-shadow: inset 0px 3px 5px rgba(0, 0, 0, 0.3);
            margin-bottom: 20px;
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
        }

        .info p {
            margin: 5px 0;
        }

        .info strong {
            color: #00C1D4; 
        }

        .button-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .button {
            padding: 15px;
            font-size: 14px;
            font-weight: bold;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            text-align: center;
            background: linear-gradient(135deg, #1F2833, #2C343A);
            color: #fff;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            height: 80px; /* Ensure uniform height for all buttons */
            width: 50%;
        }

        .button i {
            display: block;
            font-size: 20px;
            margin-bottom: 5px;
        }

        .button:hover {
            background: linear-gradient(135deg, #2D3A45, #374953);
            transform: scale(1.05);
        }

        /* Sign Out Button */
        .out {
            padding: 15px;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            text-align: center;
            background: linear-gradient(135deg, #FF4500, #FF6347); /* Bright red-orange */
            color: #fff;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            margin: 20px auto;
            display: block;
            width: 60%; /* Centered button with a fixed width */
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
            margin-top: 30px;
            border-top: 1px solid #333;
        }

        .nav-icon {
            font-size: 28px;
            color: #EDEDED;
            cursor: pointer;
            transition: color 0.3s ease-in-out;
        }

        .nav-icon:hover {
            color: #00C1D4;
        }

        /* Responsive Design */
        @media (max-width: 600px) {
            .container {
                max-width: 90%;
                padding: 15px;
            }

            .button-container {
                grid-template-columns: 1fr;
            }

            .profile-pic {
                height: 70px;
                width: 70px;
            }

            .info {
                text-align: center;
                font-size: 12px;
            }

            .button, .out {
                font-size: 13px;
                padding: 12px;
            }

            .button {
                height: 70px; /* Adjust for smaller screens */
            }

            .out {
                width: 80%; /* Slightly wider on smaller screens */
            }

            .nav-icon {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="title">Owner Profile</div>

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

        <!-- Navigation Bar -->
        <div class="navbar">
            <a href="dashboard.html"><i class="fas fa-tachometer-alt nav-icon"></i></a>
            <a href="drewards.html"><i class="fas fa-trophy nav-icon"></i></a>
            <a href="owner_profile.php"><i class="fas fa-user nav-icon"></i></a>
        </div>
    </div>
</body>
</html>
