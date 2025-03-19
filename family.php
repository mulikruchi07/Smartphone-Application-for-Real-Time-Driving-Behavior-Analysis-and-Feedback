<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "owner_registration";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['owner_email'])) {
    header('Location: login.php');
    exit();
}

$owner_email = $_SESSION['owner_email'];

$conn->query("ALTER TABLE driver_details ADD COLUMN IF NOT EXISTS family BOOLEAN DEFAULT FALSE");

$ownerQuery = "SELECT registration_number FROM cars WHERE owner_email = ?";
$stmt = $conn->prepare($ownerQuery);
$stmt->bind_param("s", $owner_email);
$stmt->execute();
$ownerCarReg = $stmt->get_result()->fetch_assoc()['registration_number'] ?? '';

$assignedDriversQuery = "SELECT * FROM driver_details WHERE car_registration_number = ? AND family = TRUE";
$stmt = $conn->prepare($assignedDriversQuery);
$stmt->bind_param("s", $ownerCarReg);
$stmt->execute();
$assignedDrivers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$unassignedDriversQuery = "SELECT * FROM driver_details WHERE car_registration_number = ? AND (family = FALSE OR family IS NULL)";
$stmt = $conn->prepare($unassignedDriversQuery);
$stmt->bind_param("s", $ownerCarReg);
$stmt->execute();
$unassignedDrivers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['assign_driver'])) {
        $driver_id = $_POST['driver_id'];
        $stmt = $conn->prepare("UPDATE driver_details SET family = TRUE WHERE driver_id = ?");
        $stmt->bind_param("i", $driver_id);
        $stmt->execute();
        echo json_encode(['success' => true]);
        exit();
    }

    if (isset($_POST['unassign_driver'])) {
        $driver_id = $_POST['driver_id'];
        $stmt = $conn->prepare("UPDATE driver_details SET family = FALSE WHERE driver_id = ?");
        $stmt->bind_param("i", $driver_id);
        $stmt->execute();
        echo json_encode(['success' => true]);
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['fetch_unassigned'])) {
    echo json_encode($unassignedDrivers);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Family</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="theme.js" defer></script>
    <style>
        body {
            background-color: #1e293b;
            color: white;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 20px;
        }

        .header {
            font-size: 22px;
            font-weight: bold;
            text-align: center;
            padding-top: 10px;
            margin-bottom: 20px;
        }

        .family-container {
            max-width: 400px;
            margin: auto;
            background: #2a3b4d;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .family-member {
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            background: #33475b;
            margin: 8px 0;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: relative;
            /* Ensure proper positioning of elements */
        }

        .family-member img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
        }

        .family-member div {
            flex-grow: 1;
            text-align: left;
        }

        .remove-btn {
            background: rgba(255, 0, 0, 0.5);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            display: none;
            position: absolute;
            right: 10px;
            /* Keep it on the right */
        }

        .family-member:hover .remove-btn {
            display: block;
        }

        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 20px;
            cursor: pointer;
            color: white;
            text-decoration: none;
        }

        .add-family-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #60a5fa;
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            font-size: 20px;
            cursor: pointer;
        }

        .family-list {
            display: none;
            position: fixed;
            bottom: 80px;
            right: 20px;
            background: #2a3b4d;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .family-list.show {
            display: block;
        }

        .family-item {
            padding: 10px;
            margin: 5px 0;
            background: #33475b;
            border-radius: 5px;
            cursor: pointer;
        }

        .family-item:hover {
            background: #60a5fa;
        }
    </style>
</head>

<body>
    <a href="owner_profile.php" class="back-button">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div class="header">Manage Family</div>

    <button class="add-family-btn" onclick="toggleFamilyList()"><i class="fas fa-user-plus"></i></button>

    <div class="family-container" id="familyContainer">
        <?php if (empty($assignedDrivers)) { ?>
            <p style="text-align: center; color: #bbb;">No members associated</p>
            <?php } else {
            foreach ($assignedDrivers as $driver) { ?>
                <div class="family-member" id="driver-<?php echo $driver['driver_id']; ?>">
                    <img src="<?php echo $driver['driver_photo']; ?>" alt="Driver Photo">
                    <div><strong><?php echo $driver['driver_name']; ?></strong><br>Car: <?php echo $driver['car_registration_number']; ?></div>
                    <button class="remove-btn" onclick="unassignDriver(<?php echo $driver['driver_id']; ?>)">Remove</button>
                </div>
        <?php }
        } ?>
    </div>

    <div class="family-list" id="familyList"></div>

    <script>
        function toggleFamilyList() {
            var list = document.getElementById('familyList');
            list.classList.toggle('show');

            if (list.classList.contains('show')) {
                document.addEventListener('click', closeFamilyListOutside);

                fetch(window.location.href + '?fetch_unassigned=true')
                    .then(response => response.json())
                    .then(data => {
                        list.innerHTML = '';

                        if (data.length === 0) {
                            let noMembersMessage = document.createElement('div');
                            noMembersMessage.className = 'no-members';
                            noMembersMessage.textContent = 'No members associated';
                            list.appendChild(noMembersMessage);
                        } else {
                            data.forEach(driver => {
                                var div = document.createElement('div');
                                div.className = 'family-item';
                                div.textContent = driver.driver_name;
                                div.onclick = () => assignDriver(driver.driver_id);
                                list.appendChild(div);
                            });
                        }
                    });
            } else {
                document.removeEventListener('click', closeFamilyListOutside);
            }
        }


        function closeFamilyListOutside(event) {
            var list = document.getElementById('familyList');
            var button = document.querySelector('.add-family-btn');
            if (!list.contains(event.target) && !button.contains(event.target)) {
                list.classList.remove('show');
                document.removeEventListener('click', closeFamilyListOutside);
            }
        }

        function assignDriver(driverId) {
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'assign_driver=true&driver_id=' + driverId
            }).then(() => {
                location.reload();
            });
        }

        function unassignDriver(driverId) {
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'unassign_driver=true&driver_id=' + driverId
            }).then(() => {
                let driverElement = document.getElementById('driver-' + driverId);
                if (driverElement) {
                    driverElement.remove();
                }

                // Check if all members are removed and update UI
                let familyContainer = document.getElementById('familyContainer');
                if (familyContainer.children.length === 0) {
                    familyContainer.innerHTML = '<p style="text-align: center; color: #bbb;">No members associated</p>';
                }
            });
        }
    </script>
</body>

</html>