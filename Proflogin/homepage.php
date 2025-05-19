<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "roomify";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_booking'])) {
    if (isset($_SESSION['email'])) {
        $email = $_SESSION['email'];

        $user_query = $conn->prepare("SELECT fistName, lastName FROM login.users WHERE email = ?");
        $user_query->bind_param("s", $email);
        $user_query->execute();
        $user_result = $user_query->get_result();

        if ($user_result->num_rows > 0) {
            $user = $user_result->fetch_assoc();
            $professor_name = $user['fistName'] . ' ' . $user['lastName'];

            $booking_id = $_POST['booking_id'];

            $verify_stmt = $conn->prepare("SELECT * FROM schedules WHERE id = ? AND professor = ?");
            $verify_stmt->bind_param("is", $booking_id, $professor_name);
            $verify_stmt->execute();
            $verify_result = $verify_stmt->get_result();

            if ($verify_result->num_rows > 0) {

                $delete_stmt = $conn->prepare("DELETE FROM schedules WHERE id = ?");
                $delete_stmt->bind_param("i", $booking_id);
                if ($delete_stmt->execute()) {
                    $success_message = "Booking deleted successfully!";
                } else {
                    $success_message = "Error: " . $delete_stmt->error;
                }
                $delete_stmt->close();
            } else {
                $success_message = "Error: You can only delete your own bookings.";
            }

            $verify_stmt->close();
        }

        $user_query->close();
    } else {
        $success_message = "Error: You must be logged in to delete a booking.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['professor_name'], $_POST['room'], $_POST['start_time'], $_POST['end_time'], $_POST['course'], $_POST['day'])) {
    $professor_name = $_POST['professor_name'];
    $room = $_POST['room'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $course = $_POST['course'];
    $day = $_POST['day'];

    $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM schedules WHERE room = ? AND day = ? AND NOT (end_time <= ? OR start_time >= ?)");
    if (!$check_stmt) die("Prepare failed: " . $conn->error);
    $check_stmt->bind_param("ssss", $room, $day, $start_time, $end_time);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $existing = $check_result->fetch_assoc()['count'];
    $check_stmt->close();

    if ($existing > 0) {
        $success_message = "This room is already booked for the selected time and day.";
    } else {
        $stmt = $conn->prepare("INSERT INTO schedules (room, start_time, end_time, course, professor, day) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) die("Prepare failed: " . $conn->error);
        $stmt->bind_param("ssssss", $room, $start_time, $end_time, $course, $professor_name, $day);
        $stmt->execute() ? $success_message = "Booking successful!" : $success_message = "Error: " . $stmt->error;
        $stmt->close();
    }
}

$rooms = [];
$room_result = $conn->query("SELECT DISTINCT room FROM schedules");
while ($r = $room_result->fetch_assoc()) {
    $rooms[] = $r['room'];
}

$schedules = [];
$result = $conn->query("SELECT * FROM schedules ORDER BY start_time");
while ($row = $result->fetch_assoc()) {
    $schedules[] = $row;
}

$conn->close();
?>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include("connect.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="homepage.css">
    <title>Room Booking System</title>
</head>
<body>
    <div class="sidebar">
        <div style="text-align:center; padding:15%;">
            <p style="font-size:20px; font-weight:bold;"><img src="" alt="">
                Hello, <?php
                if (isset($_SESSION['email'])) {
                    $email = $_SESSION['email'];
                    $query = mysqli_query($conn, "SELECT * FROM `users` WHERE email='$email'");
                    while ($row = mysqli_fetch_array($query)) {
                        echo $row['fistName'] . ' ' . $row['lastName'];
                    }
                }
                ?>
            </p>
        </div>
        <button onclick="showSchedule()">Room</button>
        <button onclick="showBookingForm()">Avail Room</button>
        <button onclick="confirmSignOut()" style="margin-top: auto;" class="signout-btn">Sign Out</button>
        <p class="dark-toggle" onclick="toggleTheme()">🌙</p>
    </div>

    <div class="main-content">
        <h2>Weekly Schedule</h2>

        <div class="schedule-table-container">
            <table class="schedule-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <?php
                        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                        foreach ($daysOfWeek as $day) {
                            echo "<th>$day</th>";
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $timeSlots = [];

                    foreach ($schedules as $entry) {
                        $slotKey = $entry['start_time'] . '|' . $entry['end_time'];
                        if (!isset($timeSlots[$slotKey])) {
                            $timeSlots[$slotKey] = [];
                        }
                        $timeSlots[$slotKey][] = $entry;
                    }

                    foreach ($timeSlots as $key => $entries) {
                        list($start, $end) = explode('|', $key);
                        echo "<tr>";
                        echo "<td>" . date("g:i A", strtotime($start)) . " - " . date("g:i A", strtotime($end)) . "</td>";

                        foreach ($daysOfWeek as $day) {
                            $found = false;
                            foreach ($entries as $entry) {
                                if ($entry['day'] === $day) {
                                    echo "<td>";
                                    echo "{$entry['room']}<br>{$entry['course']}<br>{$entry['professor']}";

                                    $canDelete = false;
                                    if (isset($_SESSION['email'])) {
                                        $email = $_SESSION['email'];
                                        $user_query = mysqli_query($conn, "SELECT fistName, lastName FROM login.users WHERE email='$email'");
                                        if ($userRow = mysqli_fetch_array($user_query)) {
                                            $currentUser = $userRow['fistName'] . ' ' . $userRow['lastName'];
                                            if ($entry['professor'] === $currentUser) {
                                                $canDelete = true;
                                            }
                                        }
                                    }

                                    if ($canDelete) {
                                        echo "<br>
                                        <form method='POST' style='display:inline;' id='deleteForm-{$entry['id']}'>
                                            <input type='hidden' name='booking_id' value='{$entry['id']}' />
                                            <input type='hidden' name='delete_booking' value='1' />
                                            <button type='button' onclick='confirmDelete({$entry['id']})' class='delete-btn'>Delete</button>
                                        </form>";
                                    }

                                    echo "</td>";

                                    $found = true;
                                    break;
                                }
                            }
                            if (!$found) {
                                echo "<td style='background-color:rgb(157, 179, 161); font-weight: bold;'>
                                available</td>";
                            }
                        }

                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>


        <div class="booking-form" id="bookingForm">
            <h3>Avail Room</h3>
            <form method="POST">
                <input type="text" name="professor_name"
                value="<?php
                if (isset($_SESSION['email'])) {
                    $email = $_SESSION['email'];
                    $query = mysqli_query($conn, "SELECT * FROM `users` WHERE email='$email'");
                    while ($row = mysqli_fetch_array($query)) {
                        echo $row['fistName'] . ' ' . $row['lastName'];
                    }
                }

                ?>" readonly />
                <label for="room">Room</label>
                <select name="room" required>
                    <?php foreach ($rooms as $room): ?>
                        <option value="<?= $room ?>"><?= $room ?></option>
                    <?php endforeach; ?>
                </select>


                <label for="day">Day</label>
                <select name="day" required>
                    <option value="">Select Day</option>
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                    <option value="Saturday">Saturday</option>
                </select>

                <label for="start_time">Start Time</label>
                <input type="time" name="start_time" required />

                <label for="end_time">End Time</label>
                <input type="time" name="end_time" required />

                <label for="course">Course</label>
                <input type="text" name="course" required />

                <button type="submit">Submit</button>
                <button type="button" onclick="hideBookingForm()" style="margin-top: 10px; background-color: grey;">Cancel</button>
            </form>
        </div>

        <script>
            function showBookingForm() {
                document.getElementById("bookingForm").style.display = "block";
            }

            function hideBookingForm() {
                document.getElementById("bookingForm").style.display = "none";
            }

            function confirmDelete(bookingId) {
                if (confirm("Are you sure you want to delete this booking?")) {
                    document.getElementById("deleteForm-" + bookingId).submit();
                }
            }

            function confirmSignOut() {
                document.getElementById('signoutModal').style.display = 'block';
            }

            function closeSignOutModal() {
                document.getElementById('signoutModal').style.display = 'none';
            }

            function proceedSignOut() {
                window.location.href = "logout.php";
            }


            setTimeout(function () {
                const msg = document.getElementById("successMessage");
                if (msg) msg.style.display = "none";
            }, 5000);
        </script>
        <script>
            function toggleTheme() {
                const body = document.body;
                const container = document.getElementById("main-container");

                body.classList.toggle("dark-mode");
                container.classList.toggle("dark-mode");

                const isDark = body.classList.contains("dark-mode");
                localStorage.setItem("darkMode", isDark ? "enabled" : "disabled");
            }

            function applySavedTheme() {
                const darkModeSetting = localStorage.getItem("darkMode");

                if (darkModeSetting === "enabled") {
                    document.body.classList.add("dark-mode");
                    document.getElementById("main-container").classList.add("dark-mode");
                }
            }

            applySavedTheme();
        </script>
        <script src="script.js">
        </script>
        <div id="signoutModal" class="modal">
            <div class="modal-content">
                <p>Are you sure you want to sign out?</p>
                <button style="background-color:transparent;color:white;" onclick="proceedSignOut()">Signout</button>
                <button onclick="closeSignOutModal()">Cancel</button>
            </div>
        </div>

    </body>
    </html>
