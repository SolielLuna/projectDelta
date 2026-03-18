<?php
session_start();
include "db.php";

// Initialize variables for success/error states
$success = false;
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Account info (from session/readonly fields)
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    
    // Personal Information - Name
    $last_name = $_POST['last_name'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $middle_name = $_POST['middle_name'] ?? '';
    $suffix = $_POST['suffix'] ?? '';
    
    // Birth Information
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $place_of_birth = $_POST['place_of_birth'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $civil_status = $_POST['civil_status'] ?? '';
    
    // Contact Information
    $contact = $_POST['contact'] ?? '';
    $alt_contact = $_POST['alt_contact'] ?? '';
    $address = $_POST['address'] ?? '';
    
    // Personal Details
    $nationality = $_POST['nationality'] ?? 'Filipino';
    $religion = $_POST['religion'] ?? '';
    
    // Education
    $school = $_POST['school'] ?? '';
    $course = $_POST['course'] ?? '';
    $year_level = $_POST['year_level'] ?? '';
    $gpa = $_POST['gpa'] ?? '';
    
    // Financial
    $family_income = $_POST['family_income'] ?? '';
    
    // Essay
    $essay = $_POST['essay'] ?? '';
    
    // File upload handling
    $file_name = '';
    if (isset($_FILES['document']) && $_FILES['document']['error'] == 0) {
        $file_name = time() . "_" . basename($_FILES['document']['name']);
        $target_dir = "uploads/";
        $target_file = $target_dir . $file_name;
        
        // Create uploads directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        // Move uploaded file
        if (!move_uploaded_file($_FILES['document']['tmp_name'], $target_file)) {
            $error_message = "Failed to upload document. Please try again.";
        }
    } else {
        $error_message = "Please upload a document.";
    }
    
    // If no error so far, proceed with database insertion
    if (empty($error_message)) {
        // Sanitize inputs for database
        $username = $conn->real_escape_string($username);
        $email = $conn->real_escape_string($email);
        $last_name = $conn->real_escape_string($last_name);
        $first_name = $conn->real_escape_string($first_name);
        $middle_name = $conn->real_escape_string($middle_name);
        $suffix = $conn->real_escape_string($suffix);
        $date_of_birth = $conn->real_escape_string($date_of_birth);
        $place_of_birth = $conn->real_escape_string($place_of_birth);
        $gender = $conn->real_escape_string($gender);
        $civil_status = $conn->real_escape_string($civil_status);
        $contact = $conn->real_escape_string($contact);
        $alt_contact = $conn->real_escape_string($alt_contact);
        $address = $conn->real_escape_string($address);
        $nationality = $conn->real_escape_string($nationality);
        $religion = $conn->real_escape_string($religion);
        $school = $conn->real_escape_string($school);
        $course = $conn->real_escape_string($course);
        $year_level = $conn->real_escape_string($year_level);
        $gpa = $conn->real_escape_string($gpa);
        $family_income = $conn->real_escape_string($family_income);
        $essay = $conn->real_escape_string($essay);
        $file_name = $conn->real_escape_string($file_name);
        
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
            $success = true;
        } else {
            $error_message = "Database error: " . $conn->error;
            // Delete uploaded file if database insert failed
            if ($file_name && file_exists("uploads/" . $file_name)) {
                unlink("uploads/" . $file_name);
            }
        }
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Status - Scholarship System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e3f2fd 0%, #f5f7fa 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .status-container {
            background: white;
            border-radius: 20px;
            padding: 50px;
            max-width: 600px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        }
        
        .icon-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 50px;
        }
        
        .success .icon-circle {
            background: #e8f5e9;
            color: #4caf50;
        }
        
        .error .icon-circle {
            background: #ffebee;
            color: #f44336;
        }
        
        h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 28px;
        }
        
        .message {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #1976d2 0%, #0d47a1 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(25, 118, 210, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(25, 118, 210, 0.4);
        }
        
        .btn-secondary {
            background: #f5f5f5;
            color: #666;
            margin-left: 10px;
        }
        
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        
        .error-code {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-family: monospace;
            color: #666;
            font-size: 14px;
        }
        
        @media (max-width: 480px) {
            .status-container {
                padding: 30px 20px;
            }
            
            h2 {
                font-size: 24px;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
                margin: 5px 0;
            }
            
            .btn-secondary {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <?php if ($success): ?>
    <div class="status-container success">
        <div class="icon-circle">✓</div>
        <h2>Application Submitted!</h2>
        <p class="message">
            Your scholarship application has been successfully submitted.<br>
            You can track the status of your application in your profile.
        </p>
        <a href="profile.php" class="btn btn-primary">
            <span>👤</span> Go to Profile
        </a>
    </div>
    
    <?php elseif (!empty($error_message)): ?>
    <div class="status-container error">
        <div class="icon-circle">✕</div>
        <h2>Submission Failed</h2>
        <p class="message">
            We encountered an error while processing your application.<br>
            Please try again or contact support if the problem persists.
        </p>
        <div class="error-code"><?php echo htmlspecialchars($error_message); ?></div>
        <a href="application.php" class="btn btn-primary">
            <span>←</span> Back to Application
        </a>
        <a href="profile.php" class="btn btn-secondary">
            Go to Profile
        </a>
    </div>
    
    <?php else: ?>
    <div class="status-container error">
        <div class="icon-circle">⚠</div>
        <h2>Invalid Access</h2>
        <p class="message">
            This page should only be accessed after submitting an application form.<br>
            Please fill out the application form first.
        </p>
        <a href="application.php" class="btn btn-primary">
            <span>📝</span> Start Application
        </a>
    </div>
    <?php endif; ?>
</body>
</html>