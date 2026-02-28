<?php
session_start();
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = $_SESSION['user_id'];

    $fullname = $_POST['fullname'];
    $contact = $_POST['contact'];
    $email = $_POST['email'];
    $address = $_POST['address'];

    $school = $_POST['school'];
    $course = $_POST['course'];
    $year_level = $_POST['year_level'];
    $gpa = $_POST['gpa'];

    $parent_occupation = $_POST['parent_occupation'];
    $family_income = $_POST['family_income'];

    $essay = $_POST['essay'];
    $goals = $_POST['goals'];

    // File upload
    $file_name = $_FILES['document']['name'];
    $temp_name = $_FILES['document']['tmp_name'];
    $folder = "uploads/" . $file_name;

    move_uploaded_file($temp_name, $folder);

    $sql = "INSERT INTO applications 
    (user_id, fullname, contact, email, address, school, course, year_level, gpa, parent_occupation, family_income, essay, goals, document)
    VALUES 
    ('$user_id','$fullname','$contact','$email','$address','$school','$course','$year_level','$gpa','$parent_occupation','$family_income','$essay','$goals','$file_name')";

    if ($conn->query($sql) === TRUE) {
        echo "<h2>Application Submitted Successfully!</h2>";
        echo "<a href='profile.php'>Go Back to Profile</a>";
    } else {
        echo "Error: " . $conn->error;
    }

    $conn->close();
}
?>