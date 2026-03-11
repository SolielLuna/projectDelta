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

// Check status
$check_sql = "SELECT status FROM applications WHERE user_id='$user_id'";
$check_result = $conn->query($check_sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="profile-container">

<h2>Welcome, <?php echo $_SESSION['fullname']; ?></h2>

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

</div>

<hr>

<h3>Your Application</h3>

<?php
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
?>

<div class="profile-grid">

<div class="profile-card">
<h4>Personal Information</h4>
<p><strong>Full Name:</strong> <?php echo $row['fullname']; ?></p>
<p><strong>Contact:</strong> <?php echo $row['contact']; ?></p>
<p><strong>Email:</strong> <?php echo $row['email']; ?></p>
<p><strong>Address:</strong> <?php echo $row['address']; ?></p>
</div>

<div class="profile-card">
<h4>Education</h4>
<p><strong>School:</strong> <?php echo $row['school']; ?></p>
<p><strong>Course:</strong> <?php echo $row['course']; ?></p>
<p><strong>Year Level:</strong> <?php echo $row['year_level']; ?></p>
<p><strong>GPA:</strong> <?php echo $row['gpa']; ?></p>
</div>

<div class="profile-card">
<h4>Family Information</h4>
<p><strong>Family Income:</strong> <?php echo $row['family_income']; ?></p>
</div>

<div class="profile-card">
<h4>Essay</h4>
<p><?php echo $row['essay']; ?></p>
</div>

<div class="profile-card">
<h4>Uploaded Document</h4>
<p><?php echo $row['document']; ?></p>
</div>

</div>

<?php
} else {
    echo "<p>No application submitted yet.</p>";
}

$conn->close();
?>

</div>


<form action="logout.php" method="POST" style="display:inline;">
    <button type="submit" class="btn logout">Logout</button>
</form>

</body>
</html>
