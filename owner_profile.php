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
    $_SESSION['owner_email'] = $owner['email'];
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
    <script src="theme.js" defer></script>
    <style>
        /* General Styles */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', Arial, sans-serif;
            background-color: #1E2A3A;
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

        .light-theme {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', Arial, sans-serif;
            background-color: #fff;
            display: flex;
            flex-direction: column;
        }

        .light-theme .container {
            width: 100%;
            max-width: 1400px;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 20px;
            padding-bottom: 100px;
        }

        .light-theme .title {
            padding-top: 20px;
            padding-left: 5px;
            font-size: 26px;
            font-weight: 600;
            color: #2575fc;
            text-transform: uppercase;
            margin-bottom: 20px;
            margin-left: 10px;
            position: fixed;
            background-color: #fff;
            width: 100%;
            padding-bottom: 10px;
        }

        /* Profile Section */
        .light-theme .stat {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
            color: #000;
            box-shadow: inset 0px 3px 5px rgba(0, 0, 0, 0.3);
            width: 300px;
            max-width: 600px;
            margin-bottom: 30px;
            margin-top: 80px;

        }

        .light-theme .profile-pic {
            height: 80px;
            width: 80px;
            border-radius: 15px;
            object-fit: cover;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.5);
        }

        .light-theme .info {
            text-align: left;
            font-size: 14px;
            line-height: 1.6;
            font-family: 'Poppins', Arial, sans-serif;
        }

        .light-theme .info p {
            margin: 5px 0;
        }

        .light-theme .info strong {
            color: #2575fc;
        }

        /* Buttons Section */
        .light-theme .button-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 30px;
            width: 90%;
            max-width: 600px;
        }

        .light-theme .button {
            padding: 15px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            text-align: center;
            background-color: #AECCE4;
            color: #000;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            width: 400px;
            height: 50px;
            font-family: 'Poppins', Arial, sans-serif;
        }

        .light-theme .button i {
            margin-right: 10px;
        }

        .light-theme .button:hover {
            background-color: #1b3554;
            transform: scale(1.05);
        }

        /* Sign Out Button */
        .light-theme .out {
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

        .light-theme a {
            text-decoration: none;
        }

        .light-theme .out:hover {
            background: linear-gradient(135deg, #FF6347, #FF7F50);
            transform: scale(1.05);
        }

        /* Navigation Bar */
        .light-theme .navbar {
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding: 15px 0;
            position: fixed;
            bottom: 0;
            width: 100%;
            background-color: #AECCE4;
            box-shadow: 0px -2px 5px rgba(0, 0, 0, 0.3);
            font-family: 'Poppins', Arial, sans-serif;
        }

        .light-theme .nav-icon {
            font-size: 28px;
            color: #000;
            cursor: pointer;
            transition: color 0.3s ease-in-out;
        }

        .light-theme .nav-icon:hover {
            color: #d0a933;
        }

        /* Modal Background */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
    }

    /* Modal Content */
    .modal-content {
        background: white;
        padding: 20px;
        border-radius: 10px;
        text-align: left;
        width: 300px;
    }

    /* Theme Options Container */
    .option-container {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    /* Label for Theme Option */
    .theme-option {
        display: flex;
        align-items: center;
        font-size: 16px;
        cursor: pointer;
    }

    /* Hide Default Radio Button */
    .theme-option input {
        display: none;
    }

    /* Custom Radio Circle */
    .checkmark {
        height: 18px;
        width: 18px;
        border: 2px solid #1E2A3A;
        border-radius: 50%;
        display: inline-block;
        position: relative;
        margin-right: 10px;
    }

    /* Selected Radio Style */
    .theme-option input:checked + .checkmark {
        background-color: #1E2A3A;
        border-color: #1E2A3A;
    }

    /* Close Button */
    .close-button {
        margin-top: 20px;
        padding: 10px;
        width: 100%;
        background-color: #1E2A3A;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .close-button:hover {
        background-color: #26374D;
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


        @media (max-width: 400px) {
            .light-theme .profile-pic {
                height: 70px;
                width: 70px;
            }

            .light-theme .stat {
                width: 275px;
            }

            .light-theme .info {
                font-size: 12px;
            }

            .light-theme .button, .out {
                font-size: 14px;
                padding: 12px;
                width: 325px;
            }
            
            .light-theme .out {
                width: 250px;
            }

            .light-theme .nav-icon {
                font-size: 24px;
            }
        }

        @media (max-width: 350px) {
            .light-theme .profile-pic {
                height: 70px;
                width: 70px;
            }

            .light-theme .stat {
                width: 250px;
            }

            .light-theme .info {
                font-size: 12px;
            }

            .light-theme .button, .out {
                font-size: 14px;
                padding: 12px;
                width: 275px;
            }
            
            .light-theme .out {
                width: 250px;
            }

            .light-theme .nav-icon {
                font-size: 24px;
            }
        }

    </style>
</head>
<body>
    <div class="title">Owner Profile</div>

    <div class="container">
        <div class="stat">
            <img src="<?php echo htmlspecialchars($owner['owner_photo']); ?>" class="profile-pic" alt="Owner Picture">
            <div class="info">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($owner['owner_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($owner['email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($owner['phone']); ?></p>
            </div>
        </div>

        <div class="button-container">
            <a href="car_manage.html"><button class="button"><i class="fas fa-car"></i>Manage Cars</button></a>
            <a href="driver_manage.php"><button class="button"><i class="fas fa-user-cog"></i>Manage Driver</button></a>
            <a href="family.php"><button class="button"><i class="fas fa-users"></i>Family</button></a>
            <button class="button" onclick="openModal()"><i class="fas fa-paint-brush"></i> Theme</button>
            <a href="account.html"><button class="button"><i class="fas fa-cogs"></i>Account</button></a>
        

        <!-- Sign Out Button -->
        <a href="Owner_login.html"><button class="out"><i class="fas fa-sign-out-alt"></i>Sign Out</button></a>
        </div>
       
        <!-- Theme Selection Modal -->
        <div id="themeModal" class="modal">
            <div class="modal-content">
                <h3>Select Theme</h3>
                <label class="theme-option">
                    <input type="radio" name="theme" value="light" onclick="setTheme('light')">
                    <span class="checkmark"></span> Light
                </label>

                <label class="theme-option">
                    <input type="radio" name="theme" value="dark" onclick="setTheme('dark')">
                    <span class="checkmark"></span> Dark
                </label>

                <button class="close-button" onclick="closeModal()">Close</button>
            </div>
        </div>
    </div>

    <!-- Navigation Bar -->
    <div class="navbar">
        <a href="dashboard.html"><i class="fas fa-tachometer-alt nav-icon"></i></a>
        <a href="drewards.html"><i class="fas fa-trophy nav-icon"></i></a>
        <a href="owner_profile.php"><i class="fas fa-user nav-icon"></i></a>
    </div>
    <script>localStorage.setItem('userType', 'owner');
            localStorage.setItem('owner_id', "<?php echo $_SESSION['owner_id']; ?>");</script>
</body>
</html>
