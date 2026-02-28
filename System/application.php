<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Scholarship Application</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h2>Scholarship Application Form</h2>

    <form action="process_application.php" method="POST" enctype="multipart/form-data">

        <h3>1. Personal Information</h3>
        <input type="text" name="fullname" placeholder="Full Name" required>
        <input type="text" name="contact" placeholder="Contact Number" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="address" placeholder="Address" required>

        <h3>2. Education</h3>
        <input type="text" name="school" placeholder="School" required>
        <input type="text" name="course" placeholder="Course" required>
        <input type="text" name="year_level" placeholder="Year Level" required>
        <input type="text" name="gpa" placeholder="GPA / GWA" required>

        <h3>3. Family & Financial Status</h3>
        <input type="text" name="parent_occupation" placeholder="Parents' Occupation" required>
        <input type="text" name="family_income" placeholder="Family Income" required>

        <h3>4. Essay / Personal Statement</h3>
        <textarea name="essay" placeholder="Why you deserve the scholarship" required></textarea>
        <textarea name="goals" placeholder="Your goals" required></textarea>

        <h3>5. Upload Documents</h3>
        <input type="file" name="document" required>

        <button type="submit">Submit Application</button>
    </form>
</div>

</body>
</html>