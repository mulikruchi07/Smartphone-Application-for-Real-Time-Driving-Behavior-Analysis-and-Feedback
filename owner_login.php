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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css">
    <script src="theme.js" defer></script>
    <style>
        /* General body styling */
        body {
    font-family: 'Poppins', Arial, sans-serif;
    background-color: #1e2a3a;
    color: #fff;
    margin: 0;
    padding: 0;
}

/* Container */
.container {
    max-width: 300px;
    margin: 150px auto;
    padding: 20px;
    background-color: #26374D;
    border: 1px solid #1e2a3a;
    border-radius: 15px;
    box-shadow: 0 0 50px rgba(0, 0, 0, 0.5);
}

/* Heading */
h1 {
    text-align: center;
    color: #d0a933;
    margin-bottom: 20px;
}

/* Labels */
label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
}

/* Text inputs */
input[type="email"], input[type="password"] {
    width: 95%;
    padding: 10px;
    margin-bottom: 20px;
    border: 1px solid #1e1e2e;
    border-radius: 8px;
    background-color: hsl(120, 1%, 70%);
    outline: none;
    transition: border-color 0.3s ease;
}

input[type="email"]:focus, input[type="password"]:focus {
    border-color: #e3b93c;
}

/* Submit button */
button[type="submit"] {
    margin-top: 10%;
    margin-left: 25%;
    width: 50%;
    padding: 10px;
    background-color: #d0a933;
    color: #fff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 15px;
    transition: background-color 0.3s ease;
}

button[type="submit"]:hover {
    background-color: #af8d28;
}

/* Already have account */
.already-account {
    text-align: center;
    margin-top: 10px;
}

a {
    text-decoration: none;
    color: #e3b93c;
}

a:hover {
    color: #d0a933;
}

/* Forgot Password link */
.forgot-password {
            text-align: right;
            margin-bottom: 15px;
        }

        .forgot-password a {
            color: #e3b93c;
            font-size: 14px;
        }

        .forgot-password a:hover {
            color: #d0a933;
        }


/* Media query for larger screens (laptops/desktops) */
@media (min-width: 768px) {
    .container {
        max-width: 300px; /* Slightly larger width for bigger screens */
        margin: 150px auto;
    }
    
    button[type="submit"] {
        margin-left: 25%;
        width: 50%; /* Center button for larger screens */
    }

    input[type="email"], input[type="password"] {
        width: 95%; /* Stretch inputs for wider layout */
    }
}


/* General body styling */
.light-theme {
    font-family: 'Poppins', Arial, sans-serif;
    background-color: #fff;
    color: black;
    margin: 0;
    padding: 0;
}

/* Container */
.light-theme .container {
    max-width: 300px;
    margin: 150px auto;
    padding: 20px;
    background-color: #AECCE4;
    border-radius: 15px;
    box-shadow: 0 0 50px rgb(255, 255, 255);
}

/* Heading */
.light-theme h1 {
    text-align: center;
    color: #2575fc;
    margin-bottom: 20px;
}

/* Labels */
.light-theme label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
}

/* Text inputs */
.light-theme input[type="email"], input[type="password"] {
    width: 95%;
    padding: 10px;
    margin-bottom: 20px;
    border: 1px solid #1e1e2e;
    border-radius: 8px;
    background-color: hsl(120, 8%, 97%);
    outline: none;
    transition: border-color 0.3s ease;
}

.light-theme input[type="email"]:focus, input[type="password"]:focus {
    border-color: #2575fc;
}

/* Submit button */
.light-theme button[type="submit"] {
    margin-top: 10%;
    margin-left: 25%;
    width: 50%;
    padding: 10px;
    background-color:#2575fc;
    color: #000;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 15px;
    transition: background-color 0.3s ease;
}

.light-theme button[type="submit"]:hover {
    background-color: #1c5cc9;
}

/* Already have account */
.light-theme .already-account {
    text-align: center;
    margin-top: 10px;
}

.light-theme a {
    text-decoration: none;
    color: #2575fc;
}

.light-theme a:hover {
    color: #1d5ecf;
}

/* Media query for larger screens (laptops/desktops) */
@media (min-width: 768px) {
    .container {
        max-width: 300px; /* Slightly larger width for bigger screens */
        margin: 150px auto;
    }
    
    button[type="submit"] {
        margin-left: 25%;
        width: 50%; /* Center button for larger screens */
    }

    input[type="email"], input[type="password"] {
        width: 95%; /* Stretch inputs for wider layout */
    }
}

/* Media query for ultra-wide screens */
@media (min-width: 1200px) {
    .container {
        max-width: 500px; /* Larger width for very wide screens */
    }
}

        /* Error message styling */
        .error-message {
            color: #ff4c4c;
            text-align: center;
            font-size: 14px;
            margin-top: 10px;
        }



        @media (min-width: 768px) {
            .light-theme .container {
        max-width: 300px; /* Slightly larger width for bigger screens */
        margin: 150px auto;
    }
    
    .light-theme button[type="submit"] {
        margin-left: 25%;
        width: 50%; /* Center button for larger screens */
    }

    .light-theme input[type="email"], input[type="password"] {
        width: 95%; /* Stretch inputs for wider layout */
    }
}

/* Media query for ultra-wide screens */
@media (min-width: 1200px) {
    .light-theme .container {
        max-width: 500px; /* Larger width for very wide screens */
    }
}

        /* Error message styling */
        .light-theme .error-message {
            color: #ff4c4c;
            text-align: center;
            font-size: 14px;
            margin-top: 10px;
        }

        #togglePasswordLogin {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 18px;
            color: #004d40;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Login</h1>
        <form action="owner_login.php" method="post">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required><br>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required><br>
            <i class="far fa-eye" id="togglePasswordLogin"></i>

            <div class="forgot-password">
                <a href="forgot_password.html">Forgot Password?</a>
            </div>

            <button type="submit">Login</button><br>

            <div class="already-account">
                <p>Don't have an account? <a href="owner_registration.html">Click here</a></p>
            </div>

            <?php if (isset($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
        </form>
    </div>

    <script>
        // Password visibility toggle
        const togglePasswordLogin = document.getElementById('togglePasswordLogin');
        const loginPassword = document.getElementById('password');

        togglePasswordLogin.addEventListener('click', function () {
            const type = loginPassword.getAttribute('type') === 'password' ? 'text' : 'password';
            loginPassword.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>

</html>