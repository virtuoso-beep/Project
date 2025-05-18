<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "roomify";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$schedules = [];
$result = $conn->query("SELECT * FROM schedules ORDER BY start_time");
while ($row = $result->fetch_assoc()) {
    $schedules[] = $row;
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard - Roomify</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div style="text-align: left; margin: 20px;">
        <button onclick="window.location.href='http://localhost/Project/choices/'"
            style="position: absolute; top: 20px; left: 20px; padding: 10px 20px; border: none; background: #9d4edd; color: white; border-radius: 8px; cursor: pointer;">
            ← Back
        </button>
    </div>
    <h2>Weekly Room Schedule - Student View</h2>

    <div class="schedule-table-container">
        <table>
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
                    $key = $entry['start_time'] . '|' . $entry['end_time'];
                    if (!isset($timeSlots[$key])) {
                        $timeSlots[$key] = [];
                    }
                    $timeSlots[$key][] = $entry;
                }

                foreach ($timeSlots as $time => $entries) {
                    list($start, $end) = explode('|', $time);
                    echo "<tr>";
                    echo "<td>" . date("g:i A", strtotime($start)) . " - " . date("g:i A", strtotime($end)) . "</td>";

                    foreach ($daysOfWeek as $day) {
                        $found = false;
                        foreach ($entries as $entry) {
                            if ($entry['day'] === $day) {
                                echo "<td>{$entry['room']}<br>{$entry['course']}<br>{$entry['professor']}</td>";
                                $found = true;
                                break;
                            }
                        }
                        if (!$found) {
                            echo "<td class='available'>Available</td>";
                        }
                    }
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <button class="dark-toggle" onclick="toggleTheme()">🌙</button>


    <footer>
        &copy; <?php echo date("Y"); ?> Roomify - Student View
    </footer>
<script>
    function toggleTheme() {
        document.body.classList.toggle("dark-mode");
        const isDark = document.body.classList.contains("dark-mode");
        localStorage.setItem("darkMode", isDark ? "enabled" : "disabled");
    }

    function applySavedTheme() {
        const darkModeSetting = localStorage.getItem("darkMode");
        if (darkModeSetting === "enabled") {
            document.body.classList.add("dark-mode");
        }
    }

    applySavedTheme();
</script>

</body>
</html>