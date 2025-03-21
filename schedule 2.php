<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
        
    <title>Schedule</title>
    <style>
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }
</style>
</head>
<body>
    <?php

    //learn from w3schools.com

    session_start();

    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='a'){
            header("location: ../login.php");
        }

    }else{
        header("location: ../login.php");
    }   

// Handle schedule submission (Admin adds schedule)
if (isset($_POST["submit"])) {
    $docid = $_POST["docid"];
    $title = $_POST["title"];
    $scheduledate = $_POST["scheduledate"];
    $scheduletime = $_POST["scheduletime"];
    $max_patients = $_POST["max_patients"];

    $sql = "INSERT INTO schedule (docid, title, scheduledate, scheduletime, max_patients) 
            VALUES ('$docid', '$title', '$scheduledate', '$scheduletime', '$max_patients')";

    if ($database->query($sql) === TRUE) {
        echo "<script>alert('Schedule added successfully!'); window.location.href='schedule.php';</script>";
    } else {
        echo "Error: " ;
    }
}

// Handle schedule deletion (Admin deletes schedule)
if (isset($_GET["delete"])) {
    $scheduleid = $_GET["delete"];
    $sql = "DELETE FROM schedule WHERE scheduleid='$scheduleid'";
    if ($database->query($sql) === TRUE) {
        echo "<script>alert('Schedule deleted successfully!'); window.location.href='schedule.php';</script>";
    }
}

// Handle patient booking
if (isset($_GET["book"])) {
    if (!isset($_SESSION["userid"])) {
        echo "<script>alert('Please login to book an appointment.'); window.location.href='login.php';</script>";
        exit;
    }

    $scheduleid = $_GET["book"];
    $patientid = $_SESSION["userid"];

    // Check available slots
    $check = "SELECT COUNT(*) as count FROM appointment WHERE scheduleid='$scheduleid'";
    $res = $database->query($check);
    $row = $res->fetch_assoc();

    // Get max patients limit
    $max_query = "SELECT max_patients FROM schedule WHERE scheduleid='$scheduleid'";
    $max_res = $database->query($max_query);
    $max_row = $max_res->fetch_assoc();
    $max_patients = $max_row["max_patients"];

    if ($row["count"] < $max_patients) {
        $sql = "INSERT INTO appointment (scheduleid, patientid) VALUES ('$scheduleid', '$patientid')";
        if ($database->query($sql) === TRUE) {
            echo "<script>alert('Appointment booked successfully!'); window.location.href='schedule.php';</script>";
        }
    } else {
        echo "<script>alert('No slots available for this session.'); window.location.href='schedule.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Scheduling</title>
</head>
<body>
    <h2>Doctor Schedule Management</h2>

    <!-- Admin Panel: Add Schedule -->
    <h3>Add Doctor Schedule</h3>
    <form action="schedule.php" method="post">
        <label>Doctor:</label>
        <select name="docid">
            <?php
            $result = $database->query("SELECT * FROM doctor");
            while ($row = $result->fetch_assoc()) {
                echo "<option value='".$row['docid']."'>".$row['docname']."</option>";
            }
            ?>
        </select>

        <label>Session Title:</label>
        <input type="text" name="title" required>

        <label>Date:</label>
        <input type="date" name="scheduledate" required>

        <label>Time:</label>
        <input type="time" name="scheduletime" required>

        <label>Max Patients:</label>
        <input type="number" name="max_patients" value="10" min="1" required>

        <button type="submit" name="submit">Add Schedule</button>
    </form>

    <!-- Display Doctor Schedules -->
    <h3>Upcoming Doctor Schedules</h3>
    <?php
    $today = date('Y-m-d');
    $sql = "SELECT schedule.*, doctor.docname 
            FROM schedule 
            INNER JOIN doctor ON schedule.docid = doctor.docid 
            WHERE schedule.scheduledate >= '$today' 
            ORDER BY schedule.scheduledate ASC";

    $result = $database->query($sql);

    if ($result->num_rows > 0) {
        echo "<table border='1'>";
        echo "<tr><th>Doctor</th><th>Title</th><th>Date</th><th>Time</th><th>Actions</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['docname']}</td>
                    <td>{$row['title']}</td>
                    <td>{$row['scheduledate']}</td>
                    <td>{$row['scheduletime']}</td>
                    <td>
                        <a href='schedule.php?book={$row['scheduleid']}'>Book</a> | 
                        <a href='schedule.php?delete={$row['scheduleid']}'>Delete</a>
                    </td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "No upcoming schedules found.";
    }
    ?>
</body>
</html>
