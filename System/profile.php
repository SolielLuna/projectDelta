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
    <?php
// Check if user already applied
$check_sql = "SELECT status FROM applications WHERE user_id='$user_id'";
$check_result = $conn->query($check_sql);
?>

<div class="actions">

<?php if ($check_result->num_rows > 0): 
    $app = $check_result->fetch_assoc();
    $status = $app['status'];
?>

    <?php if ($status == 'Pending'): ?>
        <button class="btn status pending" disabled>
            ⏳ Waiting for Approval
        </button>

    <?php elseif ($status == 'Approved'): ?>
        <button class="btn status approved" disabled>
            ✅ Approved
        </button>

    <?php elseif ($status == 'Rejected'): ?>
        <button class="btn status rejected" disabled>
            ❌ Rejected
        </button>

    <?php endif; ?>

<?php else: ?>
    <a href="application.php" class="btn apply">
        Apply for Scholarship
    </a>
<?php endif; ?>

<form action="logout.php" method="POST" style="display:inline;">
    <button type="submit" class="btn logout">Logout</button>
</form>
</div>

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

        echo "<p><strong>Family Income:</strong> {$row['family_income']}</p>";

        echo "<p><strong>Essay:</strong> {$row['essay']}</p>";

        echo "<p><strong>Document:</strong> {$row['document']}</p>";
    } else {
        echo "<p>No application submitted yet.</p>";
    }

    $conn->close();
    ?>

</div>

</body>
</html>