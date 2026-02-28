<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch application
$sql = "SELECT * FROM applications WHERE user_id='$user_id'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h2>Welcome, <?php echo $_SESSION['fullname']; ?></h2>

    <!-- APPLY BUTTON -->
    <a href="application.php" class="btn">Apply for Scholarship</a>
    <a href="logout.php" class="btn">Logout</a>

    <hr>

    <h3>Your Application</h3>

    <?php
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        echo "<p><strong>Full Name:</strong> {$row['fullname']}</p>";
        echo "<p><strong>Contact:</strong> {$row['contact']}</p>";
        echo "<p><strong>Email:</strong> {$row['email']}</p>";
        echo "<p><strong>Address:</strong> {$row['address']}</p>";

        echo "<p><strong>School:</strong> {$row['school']}</p>";
        echo "<p><strong>Course:</strong> {$row['course']}</p>";
        echo "<p><strong>Year Level:</strong> {$row['year_level']}</p>";
        echo "<p><strong>GPA:</strong> {$row['gpa']}</p>";

        echo "<p><strong>Parent Occupation:</strong> {$row['parent_occupation']}</p>";
        echo "<p><strong>Family Income:</strong> {$row['family_income']}</p>";

        echo "<p><strong>Essay:</strong> {$row['essay']}</p>";
        echo "<p><strong>Goals:</strong> {$row['goals']}</p>";

        echo "<p><strong>Document:</strong> {$row['document']}</p>";
    } else {
        echo "<p>No application submitted yet.</p>";
    }

    $conn->close();
    ?>

</div>

</body>
</html>