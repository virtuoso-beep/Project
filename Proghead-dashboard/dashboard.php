<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "roomify";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Delete schedule by ID
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM schedules WHERE id = $id");
    header("Location: " . $_SERVER['PHP_SELF']); // Redirect to the same page
    exit(); // Stop script execution after redirection
}

// Delete all schedules by room
if (isset($_GET['delete_room'])) {
    $roomToDelete = $conn->real_escape_string($_GET['delete_room']);
    $conn->query("DELETE FROM schedules WHERE room = '$roomToDelete'");
    header("Location: " . $_SERVER['PHP_SELF']); // Redirect to the same page
    exit(); // Stop script execution after redirection
}

// Add schedule
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['room'])) {
    $room = trim($_POST['room']);

    // Check if the room exists in the "rooms" table or add a new room if needed
    if ($room) {
        $roomCheck = $conn->prepare("SELECT COUNT(*) FROM rooms WHERE name = ?");
        $roomCheck->bind_param("s", $room);
        $roomCheck->execute();
        $roomCheck->bind_result($roomExists);
        $roomCheck->fetch();
        $roomCheck->close();

        if ($roomExists == 0) {
            // Add new room if it doesn't exist
            $insertRoom = $conn->prepare("INSERT INTO rooms (name) VALUES (?)"); //Use Prepare Statement
            $insertRoom->bind_param("s", $room);
            $insertRoom->execute();
            $insertRoom->close();
        }

        // Check if the room is available for the chosen time slot and day(s)
        $course = $_POST['course'];
        $professor = $_POST['professor'];
        $start = $_POST['start'];
        $end = $_POST['end'];
        $days = isset($_POST['day']) ? $_POST['day'] : [];

        foreach ($days as $day) {
            // Check for existing schedules for the same room, day, and time slot
            $conflictCheck = $conn->prepare("SELECT COUNT(*) FROM schedules WHERE room = ? AND day = ? AND ((? BETWEEN start_time AND end_time) OR (? BETWEEN start_time AND end_time))");
            $conflictCheck->bind_param("ssss", $room, $day, $start, $end);
            $conflictCheck->execute();
            $conflictCheck->bind_result($conflictExists);
            $conflictCheck->fetch();
            $conflictCheck->close();

            if ($conflictExists > 0) {
                // Conflict detected, show alert and exit
                echo "<script>alert('The room \"$room\" is already booked at this time for $day. Please select another time or day.'); window.history.back();</script>";
                exit();
            }

            // No conflict, insert the schedule
            $stmt = $conn->prepare("INSERT INTO schedules (room, course, professor, start_time, end_time, day) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $room, $course, $professor, $start, $end, $day);
            $stmt->execute();
            $stmt->close();
        }

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        // Room does not exist
        echo "<script>alert('Error: The selected or new room does not exist.'); window.history.back();</script>";
        exit();
    }
}

// Fetch all unique rooms from the 'schedules' table
$rooms = [];
$roomResult = $conn->query("SELECT DISTINCT room FROM schedules");
if ($roomResult) { // Check if the query was successful
    while ($row = $roomResult->fetch_assoc()) {
        $rooms[] = $row['room'];
    }
    $roomResult->free_result(); // free the result set
}

// Fetch all schedules
$schedules = [];
$result = $conn->query("SELECT * FROM schedules ORDER BY start_time");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $schedules[] = $row;
    }
    $result->free_result();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Roomify - Program Head</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="sidebar">
        <img src="\Project\logo.PNG" alt="Roomify Logo" />
        <button onclick="showForm()">Set Schedule</button>
        <button onclick="showSchedule()">Schedule</button>
        <button onclick="signOut()" style="margin-top: auto;">Sign out</button>
        <p class="dark-toggle" onclick="toggleTheme()">🌙</p>
    </div>

    <div class="main-content">
        <div class="card" id="formCard">
            <h2>Program Head</h2>

            <?php if (empty($rooms)): ?>
                <p class="error-message"> No rooms available. Please add a new room to start scheduling.</p>
            <?php endif; ?>

            <?php if (isset($_GET['error']) && $_GET['error'] == 'missing_room'): ?>
                <p class="error-message"> Please enter or select a room.</p>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="room">Room Name:</label>
                    <input type="text" id="room" name="room" placeholder="Enter room name" required />
                </div>
                <div class="form-group">
                    <label for="course">Input Course:</label>
                    <input type="text" id="course" name="course" required />
                </div>
                <div class="form-group">
                    <label for="professor">Professor's Name:</label>
                    <input type="text" id="professor" name="professor" required />
                </div>
                <div class="form-group">
                    <label for="start">Start Time:</label>
                    <input type="time" id="start" name="start" required />
                </div>
                <div class="form-group">
                    <label for="end">End Time:</label>
                    <input type="time" id="end" name="end" required />
                </div>
                <div class="form-group">
                    <label>Day of the Week:</label>
                    <div class="checkbox-group">
                        <label><input type="checkbox" name="day[]" value="Monday"> Monday</label>
                        <label><input type="checkbox" name="day[]" value="Tuesday"> Tuesday</label>
                        <label><input type="checkbox" name="day[]" value="Wednesday"> Wednesday</label>
                        <label><input type="checkbox" name="day[]" value="Thursday"> Thursday</label>
                        <label><input type="checkbox" name="day[]" value="Friday"> Friday</label>
                        <label><input type="checkbox" name="day[]" value="Saturday"> Saturday</label>
                    </div>
                </div>
                <button class="btn-submit" type="submit">Set Schedule</button>
            </form>
        </div>

        <div class="card" id="scheduleCard" style="display: none;">
            <h2>Schedule</h2>
            <div class="schedule-table-wrapper">
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <?php
                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                            foreach ($days as $day) : ?>
                                <th><?= $day ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $timeSlots = [];
                        foreach ($schedules as $entry) {
                            $key = $entry['start_time'] . '|' . $entry['end_time'];
                            $timeSlots[$key] = ['start' => $entry['start_time'], 'end' => $entry['end_time']];
                        }

                        uksort($timeSlots, function ($a, $b) {
                            list($startA,) = explode('|', $a);
                            list($startB,) = explode('|', $b);
                            return strtotime($startA) - strtotime($startB);
                        });

                        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

                        foreach ($timeSlots as $time) {
                            echo "<tr>";
                            echo "<td>" . date("g:i A", strtotime($time['start'])) . " - " . date("g:i A", strtotime($time['end'])) . "</td>";

                            foreach ($daysOfWeek as $day) {
                                $cell = "-";
                                foreach ($schedules as $entry) {
                                    if (
                                        $entry['start_time'] === $time['start'] &&
                                        $entry['end_time'] === $time['end'] &&
                                        $entry['day'] === $day
                                    ) {
                                        $cell = "<div class='schedule-entry'>{$entry['course']}<br>{$entry['professor']}<br><strong>{$entry['room']}</strong><br>
                                        <button class='delete-btn' onclick='confirmDelete({$entry['id']})'>Delete</button></div>";
                                        break;
                                    }
                                }
                                echo "<td>$cell</td>";
                            }

                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div id="signOutModal" class="modal-overlay" style="display:none;">
        <div class="modal-content">
            <p>Are you sure you want to sign out?</p>
            <div class="modal-buttons">
                <button onclick="proceedSignOut()">Sign Out</button>
                <button onclick="closeModal()">Cancel</button>
            </div>
        </div>
    </div>


    <script>
        function showForm() {
            document.getElementById("formCard").style.display = "block";
            document.getElementById("scheduleCard").style.display = "none";
        }

        function showSchedule() {
            document.getElementById("formCard").style.display = "none";
            document.getElementById("scheduleCard").style.display = "block";
        }

        function signOut() {
            document.getElementById("signOutModal").style.display = "flex";
        }

        function proceedSignOut() {
            window.location.href = "http://localhost/Project/Choices/";
        }

        function closeModal() {
            document.getElementById("signOutModal").style.display = "none";
        }


        function confirmDelete(id) {
            if (confirm("Are you sure you want to delete this schedule?")) {
                window.location.href = "?delete_id=" + id;
            }
        }

        function confirmRoomDelete(room) {
            if (confirm("Are you sure you want to delete all schedules for this room?")) {
                window.location.href = "?delete_room=" + encodeURIComponent(room);
            }
        }

        if (window.location.search.includes("submitted")) {
            document.querySelector("form").reset();
        }

        function toggleTheme() {
            const body = document.body;
            const sidebar = document.querySelector(".sidebar");
            const formCard = document.getElementById("formCard");
            const scheduleCard = document.getElementById("scheduleCard");

            body.classList.toggle("dark-mode");
            sidebar.classList.toggle("dark-mode");
            if(formCard){
                formCard.classList.toggle("dark-mode");
            }
            if(scheduleCard){
                scheduleCard.classList.toggle("dark-mode");
            }

            const isDark = body.classList.contains("dark-mode");
            localStorage.setItem("darkMode", isDark ? "enabled" : "disabled");
        }

        function applySavedTheme() {
            const darkModeSetting = localStorage.getItem("darkMode");
            if (darkModeSetting === "enabled") {
                document.body.classList.add("dark-mode");
                const sidebar = document.querySelector(".sidebar");
                sidebar.classList.add("dark-mode");
                const formCard = document.getElementById("formCard");
                const scheduleCard = document.getElementById("scheduleCard");
                if(formCard){
                    formCard.classList.toggle("dark-mode");
                }
                if(scheduleCard){
                    scheduleCard.classList.toggle("dark-mode");
                }

            }
        }
        applySavedTheme();

    </script>
</body>
</html>