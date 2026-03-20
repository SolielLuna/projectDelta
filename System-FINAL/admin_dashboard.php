<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "scholarship_db");

$message = "";
$messageType = "info";
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'applications';

/* -------- APPLICATION ACTIONS -------- */
if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $conn->query("UPDATE applications SET status='Approved' WHERE id=$id");
    $message = "Application Approved!";
    $messageType = "success";
}

if (isset($_GET['reject'])) {
    $id = intval($_GET['reject']);
    $conn->query("UPDATE applications SET status='Rejected' WHERE id=$id");
    $message = "Application Rejected!";
    $messageType = "warning";
}

if (isset($_GET['delete_app'])) {
    $id = intval($_GET['delete_app']);
    $conn->query("DELETE FROM applications WHERE id=$id");
    $message = "Application Deleted!";
    $messageType = "info";
}

/* -------- USER MANAGEMENT ACTIONS -------- */
if (isset($_GET['delete_user'])) {
    $id = intval($_GET['delete_user']);
    $conn->query("DELETE FROM users WHERE id=$id");
    $conn->query("DELETE FROM applications WHERE user_id=$id");
    $message = "User and their applications deleted!";
    $messageType = "info";
}

// Edit User
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'])) {
    $id = intval($_POST['user_id']);
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    
    // Check if password is being updated
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET username=?, email=?, password=? WHERE id=?");
        $stmt->bind_param("sssi", $username, $email, $password, $id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET username=?, email=? WHERE id=?");
        $stmt->bind_param("ssi", $username, $email, $id);
    }
    
    if ($stmt->execute()) {
        $message = "User updated successfully!";
        $messageType = "success";
    } else {
        $message = "Error updating user: " . $conn->error;
        $messageType = "error";
    }
    $stmt->close();
}

// Edit Application
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['application_id']) && !isset($_POST['add_user']) && !isset($_POST['add_reviewer']) && !isset($_POST['reviewer_id'])) {
    $id = intval($_POST['application_id']);
    
    // Personal Information
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $middle_name = $conn->real_escape_string($_POST['middle_name'] ?? '');
    $suffix = $conn->real_escape_string($_POST['suffix'] ?? '');
    $date_of_birth = $conn->real_escape_string($_POST['date_of_birth']);
    $place_of_birth = $conn->real_escape_string($_POST['place_of_birth']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $civil_status = $conn->real_escape_string($_POST['civil_status']);
    $contact = $conn->real_escape_string($_POST['contact']);
    $alt_contact = $conn->real_escape_string($_POST['alt_contact'] ?? '');
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    $address = $conn->real_escape_string($_POST['address']);
    $nationality = $conn->real_escape_string($_POST['nationality']);
    $religion = $conn->real_escape_string($_POST['religion']);
    
    // Education
    $school = $conn->real_escape_string($_POST['school']);
    $course = $conn->real_escape_string($_POST['course']);
    $year_level = $conn->real_escape_string($_POST['year_level']);
    $gpa = $conn->real_escape_string($_POST['gpa']);
    
    // Financial
    $family_income = $conn->real_escape_string($_POST['family_income']);
    
    // Essays and Notes
    $essay = $conn->real_escape_string($_POST['essay'] ?? '');
    $status = $conn->real_escape_string($_POST['status']);
    
    $sql = "UPDATE applications SET 
        last_name=?, first_name=?, middle_name=?, suffix=?,
        date_of_birth=?, place_of_birth=?, gender=?, civil_status=?,
        contact=?, alt_contact=?, email=?, address=?,
        nationality=?, religion=?,
        school=?, course=?, year_level=?, gpa=?,
        family_income=?, essay=?, status=?
        WHERE id=?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("sssssssssssssssssssssi",
        $last_name, $first_name, $middle_name, $suffix,
        $date_of_birth, $place_of_birth, $gender, $civil_status,
        $contact, $alt_contact, $email, $address,
        $nationality, $religion,
        $school, $course, $year_level, $gpa,
        $family_income, $essay, $status,
        $id
    );
    
    if ($stmt->execute()) {
        $message = "Application updated successfully!";
        $messageType = "success";
    } else {
        $message = "Error updating application: " . $conn->error;
        $messageType = "error";
    }
    $stmt->close();
}

if (isset($_POST['add_user'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $username, $email, $password);
    if ($stmt->execute()) {
        $message = "User added successfully!";
        $messageType = "success";
    } else {
        $message = "Error adding user: " . $conn->error;
        $messageType = "error";
    }
    $stmt->close();
}

/* -------- REVIEWER MANAGEMENT ACTIONS -------- */
if (isset($_GET['delete_reviewer'])) {
    $id = intval($_GET['delete_reviewer']);
    $conn->query("DELETE FROM reviewers WHERE id=$id");
    $message = "Reviewer deleted!";
    $messageType = "info";
}

// Edit Reviewer
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reviewer_id']) && !isset($_POST['add_reviewer'])) {
    $id = intval($_POST['reviewer_id']);
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $email = $conn->real_escape_string($_POST['email']);
    
    // Check if password is being updated
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE reviewers SET fullname=?, email=?, password=? WHERE id=?");
        $stmt->bind_param("sssi", $fullname, $email, $password, $id);
    } else {
        $stmt = $conn->prepare("UPDATE reviewers SET fullname=?, email=? WHERE id=?");
        $stmt->bind_param("ssi", $fullname, $email, $id);
    }
    
    if ($stmt->execute()) {
        $message = "Reviewer updated successfully!";
        $messageType = "success";
    } else {
        $message = "Error updating reviewer: " . $conn->error;
        $messageType = "error";
    }
    $stmt->close();
}

if (isset($_POST['add_reviewer'])) {
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO reviewers (fullname, email, password, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $fullname, $email, $password);
    if ($stmt->execute()) {
        $message = "Reviewer added successfully!";
        $messageType = "success";
    } else {
        $message = "Error adding reviewer: " . $conn->error;
        $messageType = "error";
    }
    $stmt->close();
}

/* -------- SEARCH & FILTER -------- */
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : "";
$statusFilter = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : "";

/* -------- APPLICATIONS DATA -------- */
$whereStatus = "";
if ($statusFilter != "") {
    $whereStatus = "AND status='$statusFilter'";
}

$limit = 5;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page - 1) * $limit;

$totalQuery = $conn->query("
    SELECT COUNT(*) as count FROM applications 
    WHERE (last_name LIKE '%$search%' OR first_name LIKE '%$search%' OR username LIKE '%$search%' OR school LIKE '%$search%')
    $whereStatus
");
$totalRows = $totalQuery->fetch_assoc()['count'];
$totalPages = ceil($totalRows / $limit);

$applications = $conn->query("
    SELECT * FROM applications
    WHERE (last_name LIKE '%$search%' OR first_name LIKE '%$search%' OR username LIKE '%$search%' OR school LIKE '%$search%')
    $whereStatus
    ORDER BY created_at DESC
    LIMIT $start, $limit
");

/* -------- USERS DATA -------- */
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
$totalUsers = $users->num_rows;

/* -------- REVIEWERS DATA -------- */
$reviewers = $conn->query("SELECT * FROM reviewers ORDER BY created_at DESC");
$totalReviewers = $reviewers->num_rows;

/* -------- STATISTICS -------- */
$totalApplications = $conn->query("SELECT COUNT(*) as count FROM applications")->fetch_assoc()['count'];
$approvedCount = $conn->query("SELECT COUNT(*) as count FROM applications WHERE status='Approved'")->fetch_assoc()['count'];
$pendingCount = $conn->query("SELECT COUNT(*) as count FROM applications WHERE status='Pending'")->fetch_assoc()['count'];
$rejectedCount = $conn->query("SELECT COUNT(*) as count FROM applications WHERE status='Rejected'")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ScholarFlow</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            margin: 0;
            padding: 0;
        }
        
        .admin-badge {
            background: linear-gradient(135deg, #d32f2f, #b71c1c);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-left: 10px;
            box-shadow: 0 2px 4px rgba(211, 47, 47, 0.3);
        }
        
        .topnav {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .nav-left {
            display: flex;
            align-items: center;
        }
        
        .nav-left h2 {
            margin: 0;
            color: #1976d2;
            font-size: 24px;
        }
        
        .nav-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .nav-right span {
            color: #666;
            font-weight: 500;
        }
        
        .logout-btn {
            background: #ffebee;
            color: #c62828;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: #c62828;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(198, 40, 40, 0.3);
        }
        
        .main {
            padding: 30px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        h1 {
            color: #333;
            margin-bottom: 25px;
            font-size: 28px;
        }
        
        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease;
            font-weight: 500;
        }
        
        .alert-success { 
            background: #e8f5e9; 
            color: #2e7d32; 
            border-left: 4px solid #4caf50; 
        }
        .alert-warning { 
            background: #fff3e0; 
            color: #ef6c00; 
            border-left: 4px solid #ff9800; 
        }
        .alert-error { 
            background: #ffebee; 
            color: #c62828; 
            border-left: 4px solid #f44336; 
        }
        .alert-info { 
            background: #e3f2fd; 
            color: #1565c0; 
            border-left: 4px solid #2196f3; 
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        /* Tab Container */
        .tab-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .tab-nav {
            display: flex;
            border-bottom: 2px solid #f0f0f0;
            background: #fafafa;
        }
        
        .tab-btn {
            padding: 15px 25px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            color: #666;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .tab-btn:hover {
            color: #1976d2;
            background: #f5f5f5;
        }
        
        .tab-btn.active {
            color: #1976d2;
            border-bottom-color: #1976d2;
            background: white;
        }
        
        .tab-content {
            display: none;
            padding: 25px;
        }
        
        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        /* Statistics Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        
        @media (max-width: 1400px) {
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }
        }
        
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .stat-card {
            background: white;
            padding: 25px 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid #f0f0f0;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .stat-card.users { 
            background: linear-gradient(135deg, #4caf50, #66bb6a); 
            color: white;
            border: none;
        }
        
        .stat-card.reviewers { 
            background: linear-gradient(135deg, #9c27b0, #ab47bc); 
            color: white;
            border: none;
        }
        
        .stat-card.approved {
            background: linear-gradient(135deg, #4caf50, #81c784);
            color: white;
            border: none;
        }
        
        .stat-card.pending {
            background: linear-gradient(135deg, #ff9800, #ffb74d);
            color: white;
            border: none;
        }
        
        .stat-card.rejected {
            background: linear-gradient(135deg, #f44336, #ef5350);
            color: white;
            border: none;
        }
        
        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 13px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-card.users h3, 
        .stat-card.reviewers h3,
        .stat-card.approved h3,
        .stat-card.pending h3,
        .stat-card.rejected h3 { 
            color: rgba(255,255,255,0.9); 
        }
        
        .stat-card p {
            margin: 0;
            font-size: 32px;
            font-weight: bold;
            color: #1976d2;
        }
        
        .stat-card.users p, 
        .stat-card.reviewers p,
        .stat-card.approved p,
        .stat-card.pending p,
        .stat-card.rejected p { 
            color: white; 
        }
        
        /* Section Title */
        .section-title {
            color: #1976d2;
            margin-bottom: 20px;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
        }
        
        /* Search and Filter */
        .search-filter-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .search-box {
            flex: 1;
            min-width: 250px;
        }
        
        .search-box input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e3f2fd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .search-box input:focus {
            outline: none;
            border-color: #1976d2;
            box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
        }
        
        .filter-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 8px 16px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .filter-btn.active {
            background: #1976d2;
            color: white;
            box-shadow: 0 2px 8px rgba(25, 118, 210, 0.3);
        }
        
        .filter-btn:not(.active) {
            background: #f5f5f5;
            color: #666;
            border-color: #e0e0e0;
        }
        
        .filter-btn:not(.active):hover {
            background: #e3f2fd;
            color: #1976d2;
            border-color: #1976d2;
            transform: translateY(-2px);
        }
        
        /* Add Button */
        .add-btn {
            background: linear-gradient(135deg, #4caf50, #45a049);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(76, 175, 80, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .add-btn:hover {
            background: linear-gradient(135deg, #45a049, #388e3c);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.4);
        }
        
        /* Data Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .data-table th {
            background: #1976d2;
            color: white;
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .data-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }
        
        .data-table tr:hover {
            background: #f8f9fa;
        }
        
        .data-table tr:last-child td {
            border-bottom: none;
        }
        
        /* Action Buttons Container */
        .action-buttons {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
        
        /* Password Field Styles */
        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .password-wrapper input {
            width: 100%;
            padding-right: 45px !important;
        }
        
        .toggle-password {
            position: absolute;
            right: 12px;
            cursor: pointer;
            color: #666;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            transition: all 0.3s ease;
            border-radius: 50%;
            padding: 4px;
        }
        
        .toggle-password:hover {
            color: #1976d2;
            background: rgba(25, 118, 210, 0.1);
            transform: scale(1.1);
        }
        
        .toggle-password:active {
            transform: scale(0.95);
        }
        
        .toggle-password svg {
            width: 20px;
            height: 20px;
            transition: all 0.3s ease;
        }
        
        .eye-icon, .eye-off-icon {
            display: block;
        }
        
        .password-match-error {
            display: none;
            margin-top: 5px;
            animation: shake 0.5s ease;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        /* Action Buttons - Following Reviewer Dashboard Design */
        .action-btn {
            padding: 8px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .btn-view { 
            background: #e3f2fd; 
            color: #1976d2; 
        }
        .btn-view:hover { 
            background: #1976d2; 
            color: white; 
            box-shadow: 0 4px 12px rgba(25, 118, 210, 0.3);
        }
        
        .btn-edit { 
            background: #fff3e0; 
            color: #ef6c00; 
        }
        .btn-edit:hover { 
            background: #ff9800; 
            color: white; 
            box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3);
        }
        
        .btn-delete { 
            background: #fafafa; 
            color: #666; 
        }
        .btn-delete:hover { 
            background: #d32f2f; 
            color: white; 
            box-shadow: 0 4px 12px rgba(211, 47, 47, 0.3);
        }
        
        .btn-approve { 
            background: #e8f5e9; 
            color: #2e7d32; 
        }
        .btn-approve:hover { 
            background: #4caf50; 
            color: white; 
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
        }
        
        .btn-reject { 
            background: #ffebee; 
            color: #c62828; 
        }
        .btn-reject:hover { 
            background: #f44336; 
            color: white; 
            box-shadow: 0 4px 12px rgba(244, 67, 54, 0.3);
        }
        
        /* Status Badge */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            display: inline-block;
            letter-spacing: 0.5px;
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
        
        /* Pagination */
        .pagination {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-top: 30px;
        }
        
        .page-btn {
            padding: 10px 16px;
            border-radius: 8px;
            text-decoration: none;
            background: #f5f5f5;
            color: #666;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .page-btn:hover,
        .page-btn.active {
            background: #1976d2;
            color: white;
            box-shadow: 0 2px 8px rgba(25, 118, 210, 0.3);
        }
        
        .page-btn:hover:not(.active) {
            background: #e3f2fd;
            color: #1976d2;
            border-color: #1976d2;
            transform: translateY(-2px);
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 2000;
            justify-content: center;
            align-items: center;
            padding: 20px;
            backdrop-filter: blur(4px);
        }
        
        .modal.active { 
            display: flex;
            animation: modalFadeIn 0.3s ease;
        }
        
        @keyframes modalFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .modal-content {
            background: white;
            border-radius: 16px;
            max-width: 900px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: modalSlideIn 0.3s ease;
        }
        
        @keyframes modalSlideIn {
            from { opacity: 0; transform: translateY(-50px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .modal-header {
            padding: 20px 25px;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #f8f9fa, #ffffff);
        }
        
        .modal-header h2 {
            margin: 0;
            color: #1976d2;
            font-size: 22px;
        }
        
        .modal-body {
            padding: 25px;
        }
        
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        
        @media (max-width: 768px) {
            .detail-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 480px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .detail-item {
            margin-bottom: 15px;
        }
        
        .detail-item.full-width {
            grid-column: 1 / -1;
        }
        
        .detail-item label {
            display: block;
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .detail-item p {
            margin: 0;
            font-size: 14px;
            padding: 12px;
            background: #f5f5f5;
            border-radius: 8px;
            color: #333;
            border: 1px solid #e0e0e0;
        }
        
        .detail-item .essay-content {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
            line-height: 1.6;
            max-height: fit-content;
            width: 100%;
            max-width: 100%;
            overflow-y: auto;
            border: 1px solid #e0e0e0;
            white-space: pre-wrap;
        }
                
        /* Form Styles */
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #424242;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .form-group input, 
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e3f2fd;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus, 
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #1976d2;
            box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
        }
        
        .btn-save {
            background: linear-gradient(135deg, #4caf50, #45a049);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(76, 175, 80, 0.3);
        }
        
        .btn-save:hover {
            background: linear-gradient(135deg, #45a049, #388e3c);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.4);
        }
        
        .btn-cancel {
            background: #f5f5f5;
            color: #666;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-cancel:hover {
            background: #e0e0e0;
            color: #333;
            transform: translateY(-2px);
        }
        
        .close-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: none;
            background: #f5f5f5;
            color: #666;
            font-size: 20px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .close-btn:hover {
            background: #ff5252;
            color: white;
            transform: scale(1.1) rotate(90deg);
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        
        .empty-state p {
            margin: 0;
            color: #999;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .main {
                padding: 15px;
            }
            
            .topnav {
                padding: 10px 15px;
                flex-direction: column;
                gap: 10px;
            }
            
            .tab-btn {
                padding: 12px 15px;
                font-size: 13px;
            }
            
            .data-table {
                font-size: 12px;
            }
            
            .data-table th,
            .data-table td {
                padding: 10px 8px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 4px;
            }
            
            .action-btn {
                padding: 6px 10px;
                font-size: 11px;
            }
        }
    </style>
</head>
<body>

<div class="topnav">
    <div class="nav-left">
        <h2>🎓 ScholarFlow <span class="admin-badge">Admin</span></h2>
    </div>
    <div class="nav-right">
        <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<div class="main">
    <h1>Admin Dashboard 👋</h1>
    
    <?php if($message != ""): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <span style="font-size: 18px;">
                <?php 
                echo $messageType === 'success' ? '✓' : 
                     ($messageType === 'warning' ? '⚠' : 
                     ($messageType === 'error' ? '✗' : 'ℹ')); 
                ?>
            </span>
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card users">
            <h3>Total Users</h3>
            <p><?php echo $totalUsers; ?></p>
        </div>
        <div class="stat-card reviewers">
            <h3>Total Reviewers</h3>
            <p><?php echo $totalReviewers; ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Applications</h3>
            <p><?php echo $totalApplications; ?></p>
        </div>
        <div class="stat-card approved">
            <h3>Approved</h3>
            <p><?php echo $approvedCount; ?></p>
        </div>
        <div class="stat-card pending">
            <h3>Pending</h3>
            <p><?php echo $pendingCount; ?></p>
        </div>
        <div class="stat-card rejected">
            <h3>Rejected</h3>
            <p><?php echo $rejectedCount; ?></p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tab-container">
        <div class="tab-nav">
            <button class="tab-btn <?php echo $active_tab == 'applications' ? 'active' : ''; ?>" onclick="window.location.href='?tab=applications'">
                📋 Applications
            </button>
            <button class="tab-btn <?php echo $active_tab == 'users' ? 'active' : ''; ?>" onclick="window.location.href='?tab=users'">
                👥 Users
            </button>
            <button class="tab-btn <?php echo $active_tab == 'reviewers' ? 'active' : ''; ?>" onclick="window.location.href='?tab=reviewers'">
                🔍 Reviewers
            </button>
        </div>

        <!-- Applications Tab -->
        <div class="tab-content <?php echo $active_tab == 'applications' ? 'active' : ''; ?>" id="applications-tab">
            <h3 class="section-title">📋 Manage Applications</h3>
            
            <div class="search-filter-bar">
                <form class="search-box" method="GET">
                    <input type="hidden" name="tab" value="applications">
                    <input type="text" name="search" placeholder="🔍 Search by name, username, or school..." value="<?php echo htmlspecialchars($search); ?>">
                </form>
                <div class="filter-buttons">
                    <a class="filter-btn <?php echo $statusFilter == '' ? 'active' : ''; ?>" href="?tab=applications">All</a>
                    <a class="filter-btn <?php echo $statusFilter == 'Pending' ? 'active' : ''; ?>" href="?tab=applications&status=Pending">Pending</a>
                    <a class="filter-btn <?php echo $statusFilter == 'Approved' ? 'active' : ''; ?>" href="?tab=applications&status=Approved">Approved</a>
                    <a class="filter-btn <?php echo $statusFilter == 'Rejected' ? 'active' : ''; ?>" href="?tab=applications&status=Rejected">Rejected</a>
                </div>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Applicant Name</th>
                        <th>Personal Info</th>
                        <th>School</th>
                        <th>Course</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($applications->num_rows > 0): ?>
                        <?php while($row = $applications->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($row['last_name'] . ', ' . $row['first_name']); ?></strong>
                                <?php if($row['middle_name']) echo ' ' . htmlspecialchars(substr($row['middle_name'], 0, 1)) . '.'; ?>
                                <?php if($row['suffix']) echo ' ' . htmlspecialchars($row['suffix']); ?><br>
                                <small style="color: #666;">@<?php echo htmlspecialchars($row['username']); ?></small>
                            </td>
                            <td>
                                <small>
                                📞 <?php echo htmlspecialchars($row['contact']); ?><br>
                                🎂 <?php echo date('M d, Y', strtotime($row['date_of_birth'])); ?><br>
                                ♂️ <?php echo htmlspecialchars($row['gender']); ?> | <?php echo htmlspecialchars($row['civil_status']); ?>
                                </small>
                            </td>
                            <td><?php echo htmlspecialchars($row['school']); ?></td>
                            <td><?php echo htmlspecialchars($row['course']); ?> (<?php echo htmlspecialchars($row['year_level']); ?>)</td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn btn-view" onclick="viewApplication(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                        👁 View
                                    </button>
                                    <?php if($row['status'] == 'Pending'): ?>
                                        <a href="?tab=applications&approve=<?php echo $row['id']; ?>" class="action-btn btn-approve" onclick="return confirm('Approve this application?')">
                                            ✓ Approve
                                        </a>
                                        <a href="?tab=applications&reject=<?php echo $row['id']; ?>" class="action-btn btn-reject" onclick="return confirm('Reject this application?')">
                                            ✗ Reject
                                        </a>
                                    <?php endif; ?>
                                    <button class="action-btn btn-edit" onclick='openEditApplicationModal(<?php echo json_encode($row); ?>)'>
                                        ✏️ Edit
                                    </button>
                                    <a href="?tab=applications&delete_app=<?php echo $row['id']; ?>" class="action-btn btn-delete" onclick="return confirm('Delete this application?')">
                                        🗑 Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-state-icon">📭</div>
                                    <h3>No applications found</h3>
                                    <p>Try adjusting your search or filter criteria</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if($totalPages > 1): ?>
            <div class="pagination">
                <?php for($i=1; $i<=$totalPages; $i++): ?>
                    <a href="?tab=applications&page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($statusFilter); ?>" 
                       class="page-btn <?php echo $i==$page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Users Tab -->
        <div class="tab-content <?php echo $active_tab == 'users' ? 'active' : ''; ?>" id="users-tab">
            <h3 class="section-title">👥 Manage Users</h3>
            <button class="add-btn" onclick="document.getElementById('addUserModal').classList.add('active')">
                + Add User
            </button>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($user = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <div class="action-buttons">
                                <button class="action-btn btn-edit" onclick="openEditUserModal(<?php echo $user['id']; ?>, '<?php echo addslashes($user['username']); ?>', '<?php echo addslashes($user['email']); ?>')">
                                    ✏️ Edit
                                </button>
                                <a href="?tab=users&delete_user=<?php echo $user['id']; ?>" class="action-btn btn-delete" onclick="return confirm('Delete this user and all their applications?')">
                                    🗑 Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Reviewers Tab -->
        <div class="tab-content <?php echo $active_tab == 'reviewers' ? 'active' : ''; ?>" id="reviewers-tab">
            <h3 class="section-title">🔍 Manage Reviewers</h3>
            <button class="add-btn" onclick="document.getElementById('addReviewerModal').classList.add('active')">
                + Add Reviewer
            </button>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($reviewer = $reviewers->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $reviewer['id']; ?></td>
                        <td><?php echo htmlspecialchars($reviewer['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($reviewer['email']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($reviewer['created_at'])); ?></td>
                        <td>
                            <div class="action-buttons">
                                <button class="action-btn btn-edit" onclick="openEditReviewerModal(<?php echo $reviewer['id']; ?>, '<?php echo addslashes($reviewer['fullname']); ?>', '<?php echo addslashes($reviewer['email']); ?>')">
                                    ✏️ Edit
                                </button>
                                <a href="?tab=reviewers&delete_reviewer=<?php echo $reviewer['id']; ?>" class="action-btn btn-delete" onclick="return confirm('Delete this reviewer?')">
                                    🗑 Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- View Application Modal -->
<div class="modal" id="viewModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>📋 Application Details</h2>
            <button class="close-btn" onclick="document.getElementById('viewModal').classList.remove('active')">×</button>
        </div>
        <div class="modal-body">
            <div id="applicationDetails"></div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal" id="addUserModal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h2>👥 Add New User</h2>
            <button class="close-btn" onclick="document.getElementById('addUserModal').classList.remove('active')">×</button>
        </div>
        <div class="modal-body">
            <form method="POST" onsubmit="return validateUserPasswords()">
                <input type="hidden" name="add_user" value="1">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required placeholder="Enter username">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="Enter email address">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="add_user_password" required minlength="6" placeholder="Minimum 6 characters">
                        <span class="toggle-password" onclick="togglePassword('add_user_password', this)">
                            <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="eye-off-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </span>
                    </div>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="confirm_password" id="add_user_confirm_password" required minlength="6" placeholder="Re-enter password">
                        <span class="toggle-password" onclick="togglePassword('add_user_confirm_password', this)">
                            <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="eye-off-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </span>
                    </div>
                    <span class="password-match-error" id="user_password_error" style="color: #c62828; font-size: 12px; display: none;">❌ Passwords do not match</span>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="document.getElementById('addUserModal').classList.remove('active')">Cancel</button>
                    <button type="submit" class="btn-save">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<!-- Edit User Modal -->
<div class="modal" id="editUserModal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h2>✏️ Edit User</h2>
            <button class="close-btn" onclick="document.getElementById('editUserModal').classList.remove('active')">×</button>
        </div>
        <div class="modal-body">
            <form method="POST" onsubmit="return validateEditUserPasswords()">
                <input type="hidden" name="user_id" id="edit_user_id">
                
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" id="edit_user_username" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="edit_user_email" required>
                </div>

                <div class="form-group">
                    <label>New Password <small style="color: #999; font-weight: normal;">(Leave blank to keep current)</small></label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="edit_user_password" minlength="6" placeholder="Enter new password (optional)">
                        <span class="toggle-password" onclick="togglePassword('edit_user_password', this)">
                            <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="eye-off-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </span>
                    </div>
                </div>

                <div class="form-group">
                    <label>Confirm New Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="confirm_password" id="edit_user_confirm_password" minlength="6" placeholder="Re-enter new password">
                        <span class="toggle-password" onclick="togglePassword('edit_user_confirm_password', this)">
                            <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="eye-off-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </span>
                    </div>
                    <span class="password-match-error" id="edit_user_password_error" style="color: #c62828; font-size: 12px; display: none;">❌ Passwords do not match</span>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="document.getElementById('editUserModal').classList.remove('active')">Cancel</button>
                    <button type="submit" class="btn-save">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Reviewer Modal -->
<div class="modal" id="addReviewerModal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h2>🔍 Add New Reviewer</h2>
            <button class="close-btn" onclick="document.getElementById('addReviewerModal').classList.remove('active')">×</button>
        </div>
        <div class="modal-body">
            <form method="POST" onsubmit="return validateReviewerPasswords()">
                <input type="hidden" name="add_reviewer" value="1">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="fullname" required placeholder="Enter full name">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="Enter email address">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="add_reviewer_password" required minlength="6" placeholder="Minimum 6 characters">
                        <span class="toggle-password" onclick="togglePassword('add_reviewer_password', this)">
                            <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="eye-off-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </span>
                    </div>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="confirm_password" id="add_reviewer_confirm_password" required minlength="6" placeholder="Re-enter password">
                        <span class="toggle-password" onclick="togglePassword('add_reviewer_confirm_password', this)">
                            <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="eye-off-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </span>
                    </div>
                    <span class="password-match-error" id="reviewer_password_error" style="color: #c62828; font-size: 12px; display: none;">❌ Passwords do not match</span>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="document.getElementById('addReviewerModal').classList.remove('active')">Cancel</button>
                    <button type="submit" class="btn-save">Add Reviewer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Reviewer Modal -->
<div class="modal" id="editReviewerModal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h2>✏️ Edit Reviewer</h2>
            <button class="close-btn" onclick="document.getElementById('editReviewerModal').classList.remove('active')">×</button>
        </div>
        <div class="modal-body">
            <form method="POST" onsubmit="return validateEditReviewerPasswords()">
                <input type="hidden" name="reviewer_id" id="edit_reviewer_id">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="fullname" id="edit_reviewer_fullname" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="edit_reviewer_email" required>
                </div>
                <div class="form-group">
                    <label>New Password <small style="color: #999; font-weight: normal;">(Leave blank to keep current)</small></label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="edit_reviewer_password" minlength="6" placeholder="Enter new password (optional)">
                        <span class="toggle-password" onclick="togglePassword('edit_reviewer_password', this)">
                            <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="eye-off-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </span>
                    </div>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="confirm_password" id="edit_reviewer_confirm_password" minlength="6" placeholder="Re-enter new password">
                        <span class="toggle-password" onclick="togglePassword('edit_reviewer_confirm_password', this)">
                            <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="eye-off-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </span>
                    </div>
                    <span class="password-match-error" id="edit_reviewer_password_error" style="color: #c62828; font-size: 12px; display: none;">❌ Passwords do not match</span>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="document.getElementById('editReviewerModal').classList.remove('active')">Cancel</button>
                    <button type="submit" class="btn-save">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Application Modal -->
<div class="modal" id="editApplicationModal">
    <div class="modal-content" style="max-width: 1000px; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header">
            <h2>✏️ Edit Full Application</h2>
            <button class="close-btn" onclick="document.getElementById('editApplicationModal').classList.remove('active')">×</button>
        </div>
        <div class="modal-body">
            <form method="POST">
                <input type="hidden" name="application_id" id="edit_app_id">
                
                <!-- Personal Information Section -->
                <h4 style="color: #1976d2; margin: 20px 0 15px 0; padding-bottom: 8px; border-bottom: 2px solid #e3f2fd; display: flex; align-items: center; gap: 8px;">
                    👤 Personal Information
                </h4>
                <div class="detail-grid">
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" id="edit_app_last_name" required>
                    </div>
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" id="edit_app_first_name" required>
                    </div>
                    <div class="form-group">
                        <label>Middle Name</label>
                        <input type="text" name="middle_name" id="edit_app_middle_name">
                    </div>
                    <div class="form-group">
                        <label>Suffix (Jr., Sr., III, etc.)</label>
                        <input type="text" name="suffix" id="edit_app_suffix">
                    </div>
                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="date" name="date_of_birth" id="edit_app_date_of_birth" required>
                    </div>
                    <div class="form-group">
                        <label>Place of Birth</label>
                        <input type="text" name="place_of_birth" id="edit_app_place_of_birth" required>
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <select name="gender" id="edit_app_gender" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Civil Status</label>
                        <select name="civil_status" id="edit_app_civil_status" required>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Widowed">Widowed</option>
                            <option value="Separated">Separated</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Contact Number</label>
                        <input type="tel" name="contact" id="edit_app_contact" pattern="[0-9]{11}" required>
                    </div>
                    <div class="form-group">
                        <label>Alternative Contact</label>
                        <input type="tel" name="alt_contact" id="edit_app_alt_contact" pattern="[0-9]{11}">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" id="edit_app_email">
                    </div>
                    <div class="form-group">
                        <label>Nationality</label>
                        <input type="text" name="nationality" id="edit_app_nationality" required>
                    </div>
                    <div class="form-group full-width">
                        <label>Complete Address</label>
                        <textarea name="address" id="edit_app_address" rows="2" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Religion</label>
                        <input type="text" name="religion" id="edit_app_religion" required>
                    </div>
                </div>

                <!-- Education Section -->
                <h4 style="color: #1976d2; margin: 30px 0 15px 0; padding-bottom: 8px; border-bottom: 2px solid #e3f2fd; display: flex; align-items: center; gap: 8px;">
                    🎓 Educational Background
                </h4>
                <div class="detail-grid">
                    <div class="form-group full-width">
                        <label>School/University</label>
                        <input type="text" name="school" id="edit_app_school" required>
                    </div>
                    <div class="form-group">
                        <label>Course/Program</label>
                        <input type="text" name="course" id="edit_app_course" required>
                    </div>
                    <div class="form-group">
                        <label>Year Level</label>
                        <select name="year_level" id="edit_app_year_level" required>
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                            <option value="4th Year">4th Year</option>
                            <option value="5th Year">5th Year</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>GPA / GWA</label>
                        <input type="number" step="0.01" min="1.0" max="5.0" name="gpa" id="edit_app_gpa" required>
                    </div>
                </div>

                <!-- Financial Section -->
                <h4 style="color: #1976d2; margin: 30px 0 15px 0; padding-bottom: 8px; border-bottom: 2px solid #e3f2fd; display: flex; align-items: center; gap: 8px;">
                    💰 Financial Information
                </h4>
                <div class="detail-grid">
                    <div class="form-group">
                        <label>Annual Family Income</label>
                        <select name="family_income" id="edit_app_family_income" required>
                            <option value="Below 100,000">Below ₱100,000</option>
                            <option value="100,000 - 200,000">₱100,000 - ₱200,000</option>
                            <option value="200,000 - 300,000">₱200,000 - ₱300,000</option>
                            <option value="300,000 - 500,000">₱300,000 - ₱500,000</option>
                            <option value="Above 500,000">Above ₱500,000</option>
                        </select>
                    </div>
                </div>

                <!-- Essay Section -->
                <h4 style="color: #1976d2; margin: 30px 0 15px 0; padding-bottom: 8px; border-bottom: 2px solid #e3f2fd; display: flex; align-items: center; gap: 8px;">
                    ✍️ Essays
                </h4>
                <div class="detail-grid">
                    <div class="form-group full-width">
                        <label>Personal Essay</label>
                        <textarea name="essay" id="edit_app_essay" rows="4" placeholder="Why do you deserve this scholarship?"></textarea>
                    </div>
                </div>

                <!-- Reviewer Notes Section (Admin Only) -->
                <h4 style="color: #1976d2; margin: 30px 0 15px 0; padding-bottom: 8px; border-bottom: 2px solid #e3f2fd; display: flex; align-items: center; gap: 8px;">
                    🔍 Administrative Notes
                </h4>
                <div class="detail-grid">
                    <div class="form-group">
                        <label>Application Status</label>
                        <select name="status" id="edit_app_status" required>
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="document.getElementById('editApplicationModal').classList.remove('active')">Cancel</button>
                    <button type="submit" class="btn-save">💾 Save All Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewApplication(data) {
    const details = document.getElementById('applicationDetails');
    details.innerHTML = `
        <div class="detail-grid">
            <div class="detail-item">
                <label>Last Name</label>
                <p>${data.last_name || '-'}</p>
            </div>
            <div class="detail-item">
                <label>First Name</label>
                <p>${data.first_name || '-'}</p>
            </div>
            <div class="detail-item">
                <label>Middle Name</label>
                <p>${data.middle_name || '-'}</p>
            </div>
            <div class="detail-item">
                <label>Suffix</label>
                <p>${data.suffix || '-'}</p>
            </div>
            <div class="detail-item">
                <label>Date of Birth</label>
                <p>${data.date_of_birth || '-'}</p>
            </div>
            <div class="detail-item">
                <label>Place of Birth</label>
                <p>${data.place_of_birth || '-'}</p>
            </div>
            <div class="detail-item">
                <label>Gender</label>
                <p>${data.gender || '-'}</p>
            </div>
            <div class="detail-item">
                <label>Civil Status</label>
                <p>${data.civil_status || '-'}</p>
            </div>
            <div class="detail-item full-width">
                <label>Complete Address</label>
                <p>${data.address || '-'}</p>
            </div>
            <div class="detail-item">
                <label>Contact</label>
                <p>${data.contact || '-'}</p>
            </div>
            <div class="detail-item">
                <label>Alt Contact</label>
                <p>${data.alt_contact || '-'}</p>
            </div>
            <div class="detail-item">
                <label>Nationality</label>
                <p>${data.nationality || '-'}</p>
            </div>
            <div class="detail-item">
                <label>Religion</label>
                <p>${data.religion || '-'}</p>
            </div>
            <div class="detail-item full-width">
                <label>School</label>
                <p>${data.school || '-'}</p>
            </div>
            <div class="detail-item">
                <label>Course</label>
                <p>${data.course || '-'}</p>
            </div>
            <div class="detail-item">
                <label>Year Level</label>
                <p>${data.year_level || '-'}</p>
            </div>
            <div class="detail-item">
                <label>GPA</label>
                <p>${data.gpa || '-'}</p>
            </div>
            <div class="detail-item">
                <label>Family Income</label>
                <p>${data.family_income || '-'}</p>
            </div>
            <div class="detail-item full-width">
                <label>Personal Statement / Essay</label>
                <div class="essay-content">${data.essay || 'No essay provided'}</div>
            </div>
            <div class="detail-item">
                <label>Document</label>
                <p>${data.document ? `<a href="uploads/${data.document}" target="_blank" style="color: #1976d2; font-weight: 600;">📄 View Document</a>` : 'No file uploaded'}</p>
            </div>
            <div class="detail-item">
                <label>Status</label>
                <p><span class="status-badge status-${data.status.toLowerCase()}">${data.status}</span></p>
            </div>
            <div class="detail-item">
                <label>Submitted</label>
                <p>${new Date(data.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</p>
            </div>
        </div>
    `;
    document.getElementById('viewModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

// Edit Application Modal Functions
function openEditApplicationModal(data) {
    // Hidden field
    document.getElementById('edit_app_id').value = data.id;
    
    // Personal Info
    document.getElementById('edit_app_last_name').value = data.last_name || '';
    document.getElementById('edit_app_first_name').value = data.first_name || '';
    document.getElementById('edit_app_middle_name').value = data.middle_name || '';
    document.getElementById('edit_app_suffix').value = data.suffix || '';
    document.getElementById('edit_app_date_of_birth').value = data.date_of_birth || '';
    document.getElementById('edit_app_place_of_birth').value = data.place_of_birth || '';
    document.getElementById('edit_app_gender').value = data.gender || 'Male';
    document.getElementById('edit_app_civil_status').value = data.civil_status || 'Single';
    document.getElementById('edit_app_contact').value = data.contact || '';
    document.getElementById('edit_app_alt_contact').value = data.alt_contact || '';
    document.getElementById('edit_app_email').value = data.email || '';
    document.getElementById('edit_app_address').value = data.address || '';
    document.getElementById('edit_app_nationality').value = data.nationality || 'Filipino';
    document.getElementById('edit_app_religion').value = data.religion || '';
    
    // Education
    document.getElementById('edit_app_school').value = data.school || '';
    document.getElementById('edit_app_course').value = data.course || '';
    document.getElementById('edit_app_year_level').value = data.year_level || '1st Year';
    document.getElementById('edit_app_gpa').value = data.gpa || '';
    
    // Financial
    document.getElementById('edit_app_family_income').value = data.family_income || 'Below 100,000';
    
    // Essays
    document.getElementById('edit_app_essay').value = data.essay || '';
    
    // Admin fields
    document.getElementById('edit_app_status').value = data.status || 'Pending';
    
    document.getElementById('editApplicationModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

// Edit User Modal Functions
function openEditUserModal(id, username, email) {
    document.getElementById('edit_user_id').value = id;
    document.getElementById('edit_user_username').value = username;
    document.getElementById('edit_user_email').value = email;
    document.getElementById('edit_user_password').value = ''; // Clear password field
    document.getElementById('editUserModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

// Edit Reviewer Modal Functions
function openEditReviewerModal(id, fullname, email) {
    document.getElementById('edit_reviewer_id').value = id;
    document.getElementById('edit_reviewer_fullname').value = fullname;
    document.getElementById('edit_reviewer_email').value = email;
    document.getElementById('edit_reviewer_password').value = ''; // Clear password field
    document.getElementById('editReviewerModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}
// Close modals on outside click
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
}

function togglePassword(inputId, toggleBtn) {
    const input = document.getElementById(inputId);
    const eyeIcon = toggleBtn.querySelector('.eye-icon');
    const eyeOffIcon = toggleBtn.querySelector('.eye-off-icon');
    
    if (input.type === 'password') {
        input.type = 'text';
        eyeIcon.style.display = 'none';
        eyeOffIcon.style.display = 'block';
    } else {
        input.type = 'password';
        eyeIcon.style.display = 'block';
        eyeOffIcon.style.display = 'none';
    }
}

function validateUserPasswords() {
    const password = document.getElementById('add_user_password').value;
    const confirmPassword = document.getElementById('add_user_confirm_password').value;
    const errorElement = document.getElementById('user_password_error');
    
    if (password !== confirmPassword) {
        errorElement.style.display = 'block';
        return false;
    }
    errorElement.style.display = 'none';
    return true;
}

function validateEditUserPasswords() {
    const password = document.getElementById('edit_user_password').value;
    const confirmPassword = document.getElementById('edit_user_confirm_password').value;
    const errorElement = document.getElementById('edit_user_password_error');
    
    if (password === '' && confirmPassword === '') {
        errorElement.style.display = 'none';
        return true;
    }
    
    if (password !== confirmPassword) {
        errorElement.style.display = 'block';
        return false;
    }
    errorElement.style.display = 'none';
    return true;
}

function validateReviewerPasswords() {
    const password = document.getElementById('add_reviewer_password').value;
    const confirmPassword = document.getElementById('add_reviewer_confirm_password').value;
    const errorElement = document.getElementById('reviewer_password_error');
    
    if (password !== confirmPassword) {
        errorElement.style.display = 'block';
        return false;
    }
    errorElement.style.display = 'none';
    return true;
}

function validateEditReviewerPasswords() {
    const password = document.getElementById('edit_reviewer_password').value;
    const confirmPassword = document.getElementById('edit_reviewer_confirm_password').value;
    const errorElement = document.getElementById('edit_reviewer_password_error');
    
    if (password === '' && confirmPassword === '') {
        errorElement.style.display = 'none';
        return true;
    }
    
    if (password !== confirmPassword) {
        errorElement.style.display = 'block';
        return false;
    }
    errorElement.style.display = 'none';
    return true;
}

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.classList.remove('active');
        });
        document.body.style.overflow = 'auto';
    }
});
</script>

</body>
</html>