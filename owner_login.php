<?php
session_start();

$conn = new mysqli('localhost', 'root', '', 'owner_registration');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($password)) {
        // Prepared statement to fetch user
        $stmt = $conn->prepare("SELECT * FROM owner_details WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify the provided password without the hashed password stored in the database
            //if ($password,$user['password']) {

            if (password_verify($password, $user['password'])) {
                session_regenerate_id();

                $_SESSION['owner_id'] = $user['owner_id'];
                $_SESSION['owner_name'] = $user['owner_name'];
                $_SESSION['owner_email'] = $user['email'];
                header("Location: owner_profile.php"); // Redirect to a PHP profile page
                exit();
            } else {
                $error_message = "Incorrect password!";
            }
        } else {
            $error_message = "No account found with that email!";
        }

        $stmt->close();
    } else {
        $error_message = "Invalid email format or empty password!";
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Login</title>
    <style>
        /* General body styling */
        body {
            font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
            background-color: #1e1e2e;
            color: #fff;
            margin: 0;
            padding: 0;
        }

        /* Container */
        .container {
            max-width: 500px;
            margin: 150px auto;
            padding: 20px;
            background-color: #222936;
            border: 1px solid #1e1e2e;
            border-radius: 15px;
            box-shadow: 0 0 50px rgba(0, 0, 0, 0.5);
        }

        /* Heading */
        h1 {
            text-align: center;
            color: #4CAF50;
            margin-bottom: 20px;
        } 

        /* Labels */
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        /* Text inputs */
        input[type="email"],
        input[type="password"] {
            width: 95%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #1e1e2e;
            border-radius: 8px;
            background-color: hsl(120, 1%, 70%);
            outline: none;
            transition: border-color 0.3s ease;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #4CAF50;
        }

        /* Submit button */
        button[type="submit"] {
            margin-top: 10%;
            margin-left: 25%;
            width: 50%;
            padding: 10px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: #3e8e41;
        }

        /* Already have account */
        .already-account {
            text-align: center;
            margin-top: 10px;
        }

        a {
            text-decoration: none;
            color: #4CAF50;
        }

        a:hover {
            color: #3e8e41;
        }

        /* Error message styling */
        .error-message {
            color: #ff4c4c;
            text-align: center;
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Owner Login</h1>
        <form action="owner_login.php" method="post">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required><br>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required><br>

            <button type="submit">Login</button><br>

            <div class="already-account">
                <p>Don't have an account? <a href="owner_registration.html">Click here</a></p>
            </div>

            <?php if (isset($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
        </form>
    </div>
</body>

</html>
