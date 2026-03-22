<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$upload_dir = "uploads/";

// Create uploads directory if it doesn't exist
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Fetch application
$sql = "SELECT * FROM applications WHERE user_id='$user_id'";
$result = $conn->query($sql);
$application = $result->num_rows > 0 ? $result->fetch_assoc() : null;
$status = $application ? $application['status'] : null;

// Determine if editing is allowed (only Pending or Rejected)
$can_edit = ($status == 'Pending' || $status == 'Rejected');

// Helper function to safely escape HTML
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Handle form submission for updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile']) && $can_edit) {
    // Personal Information
    $last_name = $conn->real_escape_string($_POST['last_name'] ?? '');
    $first_name = $conn->real_escape_string($_POST['first_name'] ?? '');
    $middle_name = $conn->real_escape_string($_POST['middle_name'] ?? '');
    $suffix = $conn->real_escape_string($_POST['suffix'] ?? '');
    $date_of_birth = $conn->real_escape_string($_POST['date_of_birth'] ?? '');
    $place_of_birth = $conn->real_escape_string($_POST['place_of_birth'] ?? '');
    $gender = $conn->real_escape_string($_POST['gender'] ?? '');
    $civil_status = $conn->real_escape_string($_POST['civil_status'] ?? '');
    $contact = $conn->real_escape_string($_POST['contact'] ?? '');
    $alt_contact = $conn->real_escape_string($_POST['alt_contact'] ?? '');
    $address = $conn->real_escape_string($_POST['address'] ?? '');
    $nationality = $conn->real_escape_string($_POST['nationality'] ?? '');
    $religion = $conn->real_escape_string($_POST['religion'] ?? '');
    
    // Educational Information
    $school = $conn->real_escape_string($_POST['school'] ?? '');
    $course = $conn->real_escape_string($_POST['course'] ?? '');
    $year_level = $conn->real_escape_string($_POST['year_level'] ?? '');
    $gpa = $conn->real_escape_string($_POST['gpa'] ?? '');
    
    // Financial Information
    $family_income = $conn->real_escape_string($_POST['family_income'] ?? '');
    
    // Essay
    $essay = $conn->real_escape_string($_POST['essay'] ?? '');
    
    // Handle file upload if new file is provided
    $document_update = "";
    if (isset($_FILES['document']) && $_FILES['document']['error'] == 0) {
        $file_name = time() . "_" . basename($_FILES['document']['name']);
        $target_file = $upload_dir . $file_name;
        
        // Validate file type
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = array('pdf', 'doc', 'docx');
        
        if (in_array($file_type, $allowed_types)) {
            if (move_uploaded_file($_FILES['document']['tmp_name'], $target_file)) {
                // Delete old file if exists
                if ($application['document'] && file_exists($upload_dir . $application['document'])) {
                    unlink($upload_dir . $application['document']);
                }
                $document_update = ", document = '" . $conn->real_escape_string($file_name) . "'";
            } else {
                $error_message = "Error uploading file.";
            }
        } else {
            $error_message = "Only PDF, DOC, and DOCX files are allowed.";
        }
    }
    
    if (!isset($error_message)) {
        $update_sql = "UPDATE applications SET 
            last_name = '$last_name',
            first_name = '$first_name',
            middle_name = '$middle_name',
            suffix = '$suffix',
            date_of_birth = '$date_of_birth',
            place_of_birth = '$place_of_birth',
            gender = '$gender',
            civil_status = '$civil_status',
            contact = '$contact',
            alt_contact = '$alt_contact',
            address = '$address',
            nationality = '$nationality',
            religion = '$religion',
            school = '$school',
            course = '$course',
            year_level = '$year_level',
            gpa = '$gpa',
            family_income = '$family_income',
            essay = '$essay'
            $document_update
            WHERE user_id = '$user_id'";
        
        if ($conn->query($update_sql)) {
            $success_message = "Profile updated successfully!";
            // Refresh application data
            $result = $conn->query($sql);
            $application = $result->fetch_assoc();
        } else {
            $error_message = "Error updating profile: " . $conn->error;
        }
    }
}

// Check if in edit mode
$edit_mode = isset($_GET['edit']) && $_GET['edit'] == '1' && $can_edit;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profile</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            box-sizing: border-box;
            background: url('images/8.png') center/cover no-repeat;
        }
        .container {
            width: 570px;
            height: 570px;
            overflow-y: auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .profile-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
        }
        .profile-section h4 {
            color: #771111;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e3f2fd;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .info-row {
            display: flex;
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: bold;
            width: 160px;
            color: #555;
        }
        .info-value {
            flex: 1;
            color: #333;
        }
        
        /* Edit Button Styles */
        .btn-edit {
            background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 152, 0, 0.3);
        }
        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 152, 0, 0.4);
            background: linear-gradient(135deg, #ffa726 0%, #f57c00 100%);
        }
        .btn-edit:active {
            transform: translateY(0);
        }
        .btn-edit.disabled {
            background: #ccc;
            cursor: not-allowed;
            box-shadow: none;
        }
        .btn-edit.disabled:hover {
            transform: none;
        }
        
        .btn-cancel {
            background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 152, 0, 0.3);
        }

        .btn-cancel:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 152, 0, 0.4);
            background: linear-gradient(135deg, #ffa375 0%, #f57c00 100%);
        }
        .btn-save {
            background: linear-gradient(135deg, #4caf50 0%, #388e3c 100%);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
        }
        
        .form-group {
            margin-bottom: 12px;
        }
        .form-group label {
            display: block;
            margin-bottom: 4px;
            font-weight: bold;
            color: #555;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 2px solid #e3f2fd;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #771111;
        }
        .form-group input:read-only {
            background: #f5f5f5;
            cursor: not-allowed;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .form-grid .full-width {
            grid-column: 1 / -1;
        }
        
        .alert {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }
        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }
        .alert-info {
            background: #e3f2fd;
            color: #771111;
            border: 1px solid #8fffa5;
        }
        
        /* File Upload Styles */
        .file-upload {
            position: relative;
            border: 2px dashed #bbdefb;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            background: #fafafa;
        }
        .file-upload:hover {
            border-color: #771111;
            background: #e3f2fd;
        }
        .file-upload input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        .file-upload-icon {
            font-size: 32px;
            color: #771111;
            margin-bottom: 8px;
        }
        .file-name {
            margin-top: 8px;
            color: #666;
            font-size: 14px;
        }
        .current-file {
            background: #e3f2fd;
            padding: 8px 12px;
            border-radius: 5px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .current-file a {
            color: #771111;
            text-decoration: none;
            font-weight: 600;
        }
        .current-file a:hover {
            text-decoration: underline;
        }
        
        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: flex-start;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
        }
        .status-pending {
            background: #fff3e0;
            color: #e65100;
        }
        .status-approved {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .status-rejected {
            background: #ffebee;
            color: #c62828;
        }
        
        /* Tooltip for disabled edit */
        .edit-disabled-reason {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
            font-style: italic;
        }
        
        .required {
            color: #f44336;
        }
        
        /* Empty State Styles */
        .empty-state {
            text-align: center;
            padding: 40px 30px;
            background: linear-gradient(135deg, #fffd88 0%, #fffd88 100%);
            border-radius: 15px;
            border: 2px dashed #771111;
            margin: 15px 0;
        }
        .empty-state-icon {
            font-size: 60px;
            margin-bottom: 15px;
            opacity: 0.7;
        }
        .empty-state h3 {
            color: #771111;
            margin-bottom: 10px;
            font-size: 22px;
        }
        .empty-state p {
            color: #666;
            margin-bottom: 20px;
            font-size: 15px;
            line-height: 1.5;
        }
        .btn-apply-large {
            background: linear-gradient(135deg, #771111 0%, #a10d0d 100%);
            color: white;
            padding: 12px 35px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px #771111;
        }
        .btn-apply-large:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px #771111;
        }
        .btn-apply-large svg {
            width: 18px;
            height: 18px;
        }

        /* Simple Logout Button Hover */
        .logout-btn {
            background: #771111;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(211, 47, 47, 0.3);
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(211, 47, 47, 0.4);
            background: #d32f2f;
        }
        
        /* Welcome Header */
        .welcome-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .welcome-header h2 {
            margin: 0;
            color: #771111;
            font-size: 24px;
        }
        .user-badge {
            background: linear-gradient(135deg, #fffd88 0%, #fffd88 100%);
            padding: 6px 16px;
            border-radius: 20px;
            color: #771111;
            font-weight: 600;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        h3 {
            font-size: 18px;
            margin: 12px 0;
            color: #333;
        }
        
        hr {
            margin: 12px 0;
            border: none;
            border-top: 1px solid #e0e0e0;
        }
        
        .actions {
            margin-bottom: 12px;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }

        .actions .status-badge {
            margin: 0 !important;
        }

        .actions form {
            margin: 0;
            margin-left: 100%; /* Pushes logout to the right */
        }
        
        @media (max-width: 768px) {
            .container {
                width: 90vw;
                height: 90vw;
                max-width: 500px;
                max-height: 500px;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
            .info-row {
                flex-direction: column;
            }
            .info-label {
                width: 100%;
                margin-bottom: 4px;
            }
            .welcome-header {
                flex-direction: column;
                text-align: center;
            }
            .welcome-header h2 {
                font-size: 20px;
            }
        }
        .profile-section{
            text-align: left;
            display: block;
        }
    </style>
</head>
<body>

<div class="container">
    
    <!-- Improved Welcome Header -->
    <div class="welcome-header">
        <h2>Welcome back! 👋</h2>
        <div class="user-badge">
            <span>👤</span>
            <?php echo e($_SESSION['username']); ?>
        </div>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success">✓ <?php echo $success_message; ?></div>
    <?php endif; ?>
    <?php if (isset($error_message)): ?>
        <div class="alert alert-error">✗ <?php echo $error_message; ?></div>
    <?php endif; ?>

   <div class="actions" style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
        <?php if ($application): ?>
            <!-- Status Badge -->
            <span class="status-badge status-<?php echo strtolower($status); ?>" style="margin: 0;">
                <?php 
                $icons = ['Pending' => '⏳', 'Approved' => '✅', 'Rejected' => '❌'];
                echo $icons[$status] . ' ' . $status;
                ?>
            </span>
            
            <?php if ($can_edit): ?>
                <?php if (!$edit_mode): ?>
                    <a href="profile.php?edit=1" class="btn-edit">
                        <span>✏️</span> Edit Profile
                    </a>
                <?php else: ?>
                    <a href="profile.php" class="btn btn-cancel" style="display: inline-flex; align-items: center; height: 29px; padding: 0 20px; margin: 0; text-decoration: none; line-height: 1;">❌ Cancel Editing</a>
                <?php endif; ?>
            <?php else: ?>
                <button class="btn-edit disabled" disabled title="Cannot edit approved applications" style="display: inline-flex; align-items: center; height: 29px; padding: 0 20px; margin: 0; text-decoration: none; line-height: 1;">
                    <span>🔒</span> Edit Locked
                </button>
            <?php endif; ?>
        <?php endif; ?>
        
        <form action="logout.php" method="POST" style="display:inline; margin: 0;">
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>

    <hr>

    <?php
    if ($application) {
        if ($edit_mode) {
            // Edit Mode - Show Form with ALL fields
            ?>
            <h3>✏️ Edit Your Application</h3>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="alert alert-info">
                    💡 You can edit your application because it is <strong><?php echo $status; ?></strong>.
                </div>
                
                <!-- PERSONAL INFORMATION -->
                <div class="profile-section">
                    <h4>👤 Personal Information</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" value="<?php echo e($application['username']); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" value="<?php echo e($application['email']); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label>Last Name <span class="required">*</span></label>
                            <input type="text" name="last_name" value="<?php echo e($application['last_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>First Name <span class="required">*</span></label>
                            <input type="text" name="first_name" value="<?php echo e($application['first_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Middle Name</label>
                            <input type="text" name="middle_name" value="<?php echo e($application['middle_name']); ?>">
                        </div>
                        <div class="form-group">
                            <label>Suffix (Jr., Sr., III)</label>
                            <input type="text" name="suffix" value="<?php echo e($application['suffix']); ?>">
                        </div>
                        <div class="form-group">
                            <label>Date of Birth <span class="required">*</span></label>
                            <input type="date" name="date_of_birth" value="<?php echo e($application['date_of_birth']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Place of Birth <span class="required">*</span></label>
                            <input type="text" name="place_of_birth" value="<?php echo e($application['place_of_birth']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Gender <span class="required">*</span></label>
                            <select name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male" <?php echo ($application['gender'] ?? '') == 'Male' ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ($application['gender'] ?? '') == 'Female' ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo ($application['gender'] ?? '') == 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Civil Status <span class="required">*</span></label>
                            <select name="civil_status" required>
                                <option value="">Select Status</option>
                                <option value="Single" <?php echo ($application['civil_status'] ?? '') == 'Single' ? 'selected' : ''; ?>>Single</option>
                                <option value="Married" <?php echo ($application['civil_status'] ?? '') == 'Married' ? 'selected' : ''; ?>>Married</option>
                                <option value="Widowed" <?php echo ($application['civil_status'] ?? '') == 'Widowed' ? 'selected' : ''; ?>>Widowed</option>
                                <option value="Separated" <?php echo ($application['civil_status'] ?? '') == 'Separated' ? 'selected' : ''; ?>>Separated</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Contact Number <span class="required">*</span></label>
                            <input type="tel" name="contact" value="<?php echo e($application['contact']); ?>" pattern="[0-9]{11}" required>
                        </div>
                        <div class="form-group">
                            <label>Alternative Contact</label>
                            <input type="tel" name="alt_contact" value="<?php echo e($application['alt_contact']); ?>" pattern="[0-9]{11}">
                        </div>
                        <div class="form-group full-width">
                            <label>Complete Address <span class="required">*</span></label>
                            <input type="text" name="address" value="<?php echo e($application['address']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Nationality <span class="required">*</span></label>
                            <input type="text" name="nationality" value="<?php echo e($application['nationality']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Religion <span class="required">*</span></label>
                            <input type="text" name="religion" value="<?php echo e($application['religion']); ?>" required>
                        </div>
                    </div>
                </div>

                <!-- EDUCATIONAL BACKGROUND -->
                <div class="profile-section">
                    <h4>🎓 Educational Background</h4>
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label>School/University <span class="required">*</span></label>
                            <input type="text" name="school" value="<?php echo e($application['school']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Course/Program <span class="required">*</span></label>
                            <input type="text" name="course" value="<?php echo e($application['course']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Year Level <span class="required">*</span></label>
                            <select name="year_level" required>
                                <option value="">Select Year</option>
                                <option value="1st Year" <?php echo ($application['year_level'] ?? '') == '1st Year' ? 'selected' : ''; ?>>1st Year</option>
                                <option value="2nd Year" <?php echo ($application['year_level'] ?? '') == '2nd Year' ? 'selected' : ''; ?>>2nd Year</option>
                                <option value="3rd Year" <?php echo ($application['year_level'] ?? '') == '3rd Year' ? 'selected' : ''; ?>>3rd Year</option>
                                <option value="4th Year" <?php echo ($application['year_level'] ?? '') == '4th Year' ? 'selected' : ''; ?>>4th Year</option>
                                <option value="5th Year" <?php echo ($application['year_level'] ?? '') == '5th Year' ? 'selected' : ''; ?>>5th Year</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>GPA / GWA <span class="required">*</span></label>
                            <input type="number" name="gpa" step="0.01" min="1.0" max="5.0" value="<?php echo e($application['gpa']); ?>" required>
                        </div>
                    </div>
                </div>

                <!-- FAMILY & FINANCIAL -->
                <div class="profile-section">
                    <h4>💰 Family & Financial Status</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Annual Family Income <span class="required">*</span></label>
                            <select name="family_income" required>
                                <option value="">Select Range</option>
                                <option value="Below 100,000" <?php echo ($application['family_income'] ?? '') == 'Below 100,000' ? 'selected' : ''; ?>>Below ₱100,000</option>
                                <option value="100,000 - 200,000" <?php echo ($application['family_income'] ?? '') == '100,000 - 200,000' ? 'selected' : ''; ?>>₱100,000 - ₱200,000</option>
                                <option value="200,000 - 300,000" <?php echo ($application['family_income'] ?? '') == '200,000 - 300,000' ? 'selected' : ''; ?>>₱200,000 - ₱300,000</option>
                                <option value="300,000 - 500,000" <?php echo ($application['family_income'] ?? '') == '300,000 - 500,000' ? 'selected' : ''; ?>>₱300,000 - ₱500,000</option>
                                <option value="Above 500,000" <?php echo ($application['family_income'] ?? '') == 'Above 500,000' ? 'selected' : ''; ?>>Above ₱500,000</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- PERSONAL STATEMENT -->
                <div class="profile-section">
                    <h4>✍️ Personal Statement</h4>
                    <div class="form-group">
                        <label>Why do you deserve this scholarship? <span class="required">*</span></label>
                        <textarea name="essay" rows="4" maxlength="1000" required><?php echo e($application['essay']); ?></textarea>
                    </div>
                </div>

                <!-- DOCUMENTS -->
                <div class="profile-section">
                    <h4>📎 Required Documents</h4>
                    <?php if ($application['document']): ?>
                        <div class="current-file">
                            <span>📄</span>
                            <span>Current file: <a href="<?php echo $upload_dir . e($application['document']); ?>" target="_blank"><?php echo e($application['document']); ?></a></span>
                        </div>
                    <?php endif; ?>
                    
                    <label style="display: block; margin-bottom: 10px; font-weight: bold;">Upload New Document (Optional)</label>
                    <div class="file-upload" onclick="document.getElementById('document').click()">
                        <input type="file" id="document" name="document" accept=".pdf,.doc,.docx" onchange="updateFileName(this)">
                        <div class="file-upload-icon">📄</div>
                        <p><strong>Click to upload new file</strong> or drag and drop</p>
                        <p style="font-size: 12px; color: #999;">PDF, DOC, DOCX up to 5MB</p>
                        <div class="file-name" id="fileName">No new file selected</div>
                    </div>
                    <p style="font-size: 12px; color: #666; margin-top: 10px;">
                        💡 Leave empty to keep your current document
                    </p>
                </div>

                <div class="actions" style="margin-top: 20px;">
                    <button type="submit" name="update_profile" class="btn-save">💾 Save Changes</button>
                    <a href="profile.php" class="btn btn-cancel">Cancel</a>
                </div>
            </form>
            
            <script>
                function updateFileName(input) {
                    const fileName = document.getElementById('fileName');
                    if (input.files && input.files[0]) {
                        fileName.textContent = 'New file: ' + input.files[0].name;
                        fileName.style.color = '#1976d2';
                        fileName.style.fontWeight = '600';
                    }
                }
            </script>
            <?php
        } else {
            // View Mode - Show ALL Info
            ?>
            <h3>📋 Your Application Details</h3>
            
            <?php if ($status == 'Approved'): ?>
                <div class="alert alert-success">
                    🎉 Congratulations! Your application has been <strong>approved</strong>. Editing is now disabled.
                </div>
            <?php elseif ($status == 'Rejected'): ?>
                <div class="alert alert-error">
                    ❌ Your application was <strong>rejected</strong>. You can edit and resubmit your information.
                </div>
            <?php elseif ($status == 'Pending'): ?>
                <div class="alert alert-info">
                    ⏳ Your application is <strong>pending</strong> review. You can still make changes if needed.
                </div>
            <?php endif; ?>
            
            <!-- PERSONAL INFORMATION -->
            <div class="profile-section">
                <h4>👤 Personal Information</h4>
                <div class="info-row">
                    <span class="info-label">Username:</span>
                    <span class="info-value"><?php echo e($application['username']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Full Name:</span>
                    <span class="info-value">
                        <?php 
                        $full_name = e($application['last_name']) . ", " . e($application['first_name']);
                        if ($application['middle_name']) $full_name .= " " . e($application['middle_name']);
                        if ($application['suffix']) $full_name .= " " . e($application['suffix']);
                        echo $full_name;
                        ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date of Birth:</span>
                    <span class="info-value"><?php echo e($application['date_of_birth']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Place of Birth:</span>
                    <span class="info-value"><?php echo e($application['place_of_birth']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Gender:</span>
                    <span class="info-value"><?php echo e($application['gender']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Civil Status:</span>
                    <span class="info-value"><?php echo e($application['civil_status']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Contact:</span>
                    <span class="info-value"><?php echo e($application['contact']); ?></span>
                </div>
                <?php if ($application['alt_contact']): ?>
                <div class="info-row">
                    <span class="info-label">Alt Contact:</span>
                    <span class="info-value"><?php echo e($application['alt_contact']); ?></span>
                </div>
                <?php endif; ?>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><?php echo e($application['email']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Address:</span>
                    <span class="info-value"><?php echo e($application['address']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Nationality:</span>
                    <span class="info-value"><?php echo e($application['nationality']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Religion:</span>
                    <span class="info-value"><?php echo e($application['religion']); ?></span>
                </div>
            </div>

            <!-- EDUCATIONAL BACKGROUND -->
            <div class="profile-section">
                <h4>🎓 Educational Background</h4>
                <div class="info-row">
                    <span class="info-label">School:</span>
                    <span class="info-value"><?php echo e($application['school']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Course:</span>
                    <span class="info-value"><?php echo e($application['course']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Year Level:</span>
                    <span class="info-value"><?php echo e($application['year_level']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">GPA:</span>
                    <span class="info-value"><?php echo e($application['gpa']); ?></span>
                </div>
            </div>

            <!-- FAMILY & FINANCIAL -->
            <div class="profile-section">
                <h4>💰 Family & Financial Status</h4>
                <div class="info-row">
                    <span class="info-label">Family Income:</span>
                    <span class="info-value"><?php echo e($application['family_income']); ?></span>
                </div>
            </div>

            <!-- PERSONAL STATEMENT -->
            <div class="profile-section">
                <h4>✍️ Personal Statement</h4>
                <p style="line-height: 1.6; color: #555;"><?php echo nl2br(e($application['essay'])); ?></p>
            </div>

            <!-- DOCUMENTS -->
            <div class="profile-section">
                <h4>📎 Documents</h4>
                <?php if ($application['document']): ?>
                    <div class="current-file">
                        <span>📄</span>
                        <span>Uploaded file: <a href="<?php echo $upload_dir . e($application['document']); ?>" target="_blank"><?php echo e($application['document']); ?></a></span>
                    </div>
                <?php else: ?>
                    <p style="color: #999;">No document uploaded</p>
                <?php endif; ?>
            </div>
            <?php
        }
    } else {
        // IMPROVED EMPTY STATE
        ?>
        <div class="empty-state">
            <div class="empty-state-icon">📝</div>
            <h3>Start Your Scholarship Journey</h3>
            <p>You haven't submitted a scholarship application yet.<br>Click the button below to apply and take the first step toward your educational goals!</p>
            <a href="application.php" class="btn-apply-large">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Apply for Scholarship
            </a>
        </div>
        <?php
    }

    $conn->close();
    ?>

</div>

</body>
</html>