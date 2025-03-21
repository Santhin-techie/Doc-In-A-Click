<?php
session_start();
if (!isset($_SESSION["user"]) || empty($_SESSION["user"]) || $_SESSION['usertype'] != 'd') {
    header("location: ../login.php");
    exit();
}

$useremail = $_SESSION["user"];
include("../connection.php");

$stmt = $database->prepare("SELECT * FROM doctor WHERE docemail = ?");
$stmt->bind_param("s", $useremail);
$stmt->execute();
$userrow = $stmt->get_result()->fetch_assoc();
$userid = $userrow["docid"];
$username = $userrow["docname"];
$stmt->close();

$selecttype = "My";
$current = "My Patients Only";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["search"])) {
        $keyword = trim($_POST["search12"]);
        $stmt = $database->prepare("SELECT * FROM patient WHERE pemail LIKE ? OR pname LIKE ?");
        $searchTerm = "%$keyword%";
        $stmt->bind_param("ss", $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    } elseif (isset($_POST["filter"])) {
        if ($_POST["showonly"] === 'all') {
            $sqlmain = "SELECT * FROM patient";
            $current = "All Patients";
        } else {
            $stmt = $database->prepare(
                "SELECT * FROM appointment 
                 INNER JOIN patient ON patient.pid = appointment.pid 
                 INNER JOIN schedule ON schedule.scheduleid = appointment.scheduleid 
                 WHERE schedule.docid = ?"
            );
            $stmt->bind_param("i", $userid);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            $current = "My Patients Only";
        }
    }
} else {
    $stmt = $database->prepare(
        "SELECT * FROM appointment 
         INNER JOIN patient ON patient.pid = appointment.pid 
         INNER JOIN schedule ON schedule.scheduleid = appointment.scheduleid 
         WHERE schedule.docid = ?"
    );
    $stmt->bind_param("i", $userid);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
    <title>Patients</title>
</head>
<body>
<div class="container">
    <div class="menu">
        <!-- Add Menu Code -->
    </div>
    <div class="dash-body">
        <table>
            <tr>
                <td>
                    <form method="post">
                        <input type="text" name="search12" placeholder="Search Patient name or Email">
                        <button type="submit" name="search">Search</button>
                    </form>
                </td>
            </tr>
        </table>
        <div>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Telephone</th>
                        <th>Date of Birth</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (isset($result) && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['pname']); ?></td>
                            <td><?= htmlspecialchars($row['pemail']); ?></td>
                            <td><?= htmlspecialchars($row['ptel']); ?></td>
                            <td><?= htmlspecialchars($row['pdob']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No patients found.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
