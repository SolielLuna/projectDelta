<?php
session_start();
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = $_SESSION['user_id'];

    // Account info (from session/readonly fields)
    $username = $_POST['username'];
    $email = $_POST['email'];
    
    // Personal Information - Name
    $last_name = $_POST['last_name'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'] ?? '';
    $suffix = $_POST['suffix'] ?? '';
    
    // Birth Information
    $date_of_birth = $_POST['date_of_birth'];
    $place_of_birth = $_POST['place_of_birth'];
    $gender = $_POST['gender'];
    $civil_status = $_POST['civil_status'];
    
    // Contact Information
    $contact = $_POST['contact'];
    $alt_contact = $_POST['alt_contact'] ?? '';
    $address = $_POST['address'];
    
    // Personal Details
    $nationality = $_POST['nationality'] ?? 'Filipino';
    $religion = $_POST['religion'];

    // Education
    $school = $_POST['school'];
    $course = $_POST['course'];
    $year_level = $_POST['year_level'];
    $gpa = $_POST['gpa'];

    // Financial
    $family_income = $_POST['family_income'];

    // Essay
    $essay = $_POST['essay'];

    // File upload
    $file_name = $_FILES['document']['name'];
    $temp_name = $_FILES['document']['tmp_name'];
    $folder = "uploads/" . $file_name;

    move_uploaded_file($temp_name, $folder);

    // Build SQL with all new fields
    $sql = "INSERT INTO applications 
    (user_id, username, email, 
     last_name, first_name, middle_name, suffix,
     date_of_birth, place_of_birth, gender, civil_status,
     contact, alt_contact, address, nationality, religion,
     school, course, year_level, gpa, family_income, essay, document)
    VALUES 
    ('$user_id', '$username', '$email',
     '$last_name', '$first_name', '$middle_name', '$suffix',
     '$date_of_birth', '$place_of_birth', '$gender', '$civil_status',
     '$contact', '$alt_contact', '$address', '$nationality', '$religion',
     '$school', '$course', '$year_level', '$gpa', '$family_income', '$essay', '$file_name')";

    if ($conn->query($sql) === TRUE) {
        echo "<h2>Application Submitted Successfully!</h2>";
        echo "<a href='profile.php'>Go Back to Profile</a>";
    } else {
        echo "Error: " . $conn->error;
    }

    $conn->close();
}
?>