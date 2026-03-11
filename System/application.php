<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Scholarship Application</title>
<link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">

<h2>Scholarship Application Form</h2>
<p>Please complete all required fields.</p>

<form action="process_application.php" method="POST" enctype="multipart/form-data">

<!-- PERSONAL INFORMATION -->
<h3>1. Personal Information</h3>

<div class="form-group">
<label for="fullname">Full Name</label>
<input type="text" id="fullname" name="fullname" placeholder="Enter your full name" required>
</div>

<div class="form-group">
<label for="contact">Contact Number</label>
<input type="tel" id="contact" name="contact" placeholder="09XXXXXXXXX" pattern="[0-9]{10,13}" required>
</div>

<div class="form-group">
<label for="email">Email Address</label>
<input type="email" id="email" name="email" placeholder="your@email.com" required>
</div>

<div class="form-group">
<label for="address">Address</label>
<input type="text" id="address" name="address" placeholder="Complete address" required>
</div>


<!-- EDUCATION -->
<h3>2. Educational Background</h3>

<div class="form-group">
<label for="school">School</label>
<input type="text" id="school" name="school" placeholder="School Name" required>
</div>

<div class="form-group">
<label for="course">Course / Program</label>
<input type="text" id="course" name="course" placeholder="Course or Program" required>
</div>

<div class="form-group">
<label for="year_level">Year Level</label>
<input type="text" id="year_level" name="year_level" placeholder="Example: 2nd Year" required>
</div>

<div class="form-group">
<label for="gpa">GPA / GWA</label>
<input type="text" id="gpa" name="gpa" placeholder="Example: 1.75" required>
</div>


<!-- FAMILY STATUS -->
<h3>3. Family & Financial Status</h3>

<div class="form-group">
<label for="family_income">Estimated Monthly Family Income</label>
<input type="number" id="family_income" name="family_income" placeholder="Example: 15000" required>
</div>


<!-- ESSAY -->
<h3>4. Essay / Personal Statement</h3>

<div class="form-group">
<label for="essay">Why do you deserve this scholarship?</label>
<textarea id="essay" name="essay" placeholder="Write your personal statement here..." required></textarea>
</div>


<!-- DOCUMENT UPLOAD -->
<h3>5. Upload Supporting Documents</h3>

<div class="form-group">
<label for="document">Upload Required Document (PDF/JPG/PNG)</label>
<input type="file" id="document" name="document" accept=".pdf,.jpg,.jpeg,.png" required>
</div>


<!-- SUBMIT -->
<button type="submit">Submit Application</button>

</form>

</div>

</body>
</html>

<!-- SUBMIT -->
