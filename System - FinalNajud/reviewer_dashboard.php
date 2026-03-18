<?php
session_start();

// Check if user is logged in as reviewer
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'reviewer') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "scholarship_db");

$message = "";
$messageType = "";

/* -------- HANDLE ACTIONS -------- */
// Approve application
if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $reviewer_id = $_SESSION['reviewer_id'];
    $reviewer_name = $_SESSION['username'];
    $reviewed_at = date('Y-m-d H:i:s');
    
    $stmt = $conn->prepare("UPDATE applications SET status='Approved', reviewer_id=?, reviewer_name=?, reviewed_at=? WHERE id=?");
    $stmt->bind_param("issi", $reviewer_id, $reviewer_name, $reviewed_at, $id);
    
    if ($stmt->execute()) {
        $message = "Application Approved successfully!";
        $messageType = "success";
    } else {
        $message = "Error approving application.";
        $messageType = "error";
    }
    $stmt->close();
}

// Reject application
if (isset($_GET['reject'])) {
    $id = intval($_GET['reject']);
    $reviewer_id = $_SESSION['reviewer_id'];
    $reviewer_name = $_SESSION['username'];
    $reviewed_at = date('Y-m-d H:i:s');
    
    $stmt = $conn->prepare("UPDATE applications SET status='Rejected', reviewer_id=?, reviewer_name=?, reviewed_at=? WHERE id=?");
    $stmt->bind_param("issi", $reviewer_id, $reviewer_name, $reviewed_at, $id);
    
    if ($stmt->execute()) {
        $message = "Application Rejected.";
        $messageType = "warning";
    } else {
        $message = "Error rejecting application.";
        $messageType = "error";
    }
    $stmt->close();
}

// Delete application
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM applications WHERE id=?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $message = "Application Deleted!";
        $messageType = "info";
    } else {
        $message = "Error deleting application.";
        $messageType = "error";
    }
    $stmt->close();
}

// Handle Edit Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['application_id'])) {
    $id = intval($_POST['application_id']);
    $status = $conn->real_escape_string($_POST['status']);
    $review_notes = isset($_POST['review_notes']) ? $conn->real_escape_string($_POST['review_notes']) : '';
    
    $reviewer_id = $_SESSION['reviewer_id'];
    $reviewer_name = $_SESSION['username'];
    $reviewed_at = date('Y-m-d H:i:s');
    
    $stmt = $conn->prepare("UPDATE applications SET status=?, review_notes=?, reviewer_id=?, reviewer_name=?, reviewed_at=? WHERE id=?");
    $stmt->bind_param("ssissi", $status, $review_notes, $reviewer_id, $reviewer_name, $reviewed_at, $id);
    
    if ($stmt->execute()) {
        $message = "Application updated successfully!";
        $messageType = "success";
    } else {
        $message = "Error updating application: " . $stmt->error;
        $messageType = "error";
    }
    $stmt->close();
}

/* -------- SEARCH & FILTER -------- */
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : "";
$statusFilter = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : "";

$whereClause = "WHERE (a.username LIKE '%$search%' OR a.school LIKE '%$search%' OR a.email LIKE '%$search%')";
if ($statusFilter != "") {
    $whereClause .= " AND a.status='$statusFilter'";
}

/* -------- PAGINATION -------- */
$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page - 1) * $limit;

$totalQuery = $conn->query("SELECT COUNT(*) as count FROM applications a $whereClause");
$totalRows = $totalQuery->fetch_assoc()['count'];
$totalPages = ceil($totalRows / $limit);

/* -------- FETCH DATA -------- */
$applications = $conn->query("
    SELECT a.*, u.username as applicant_name, u.email as user_email 
    FROM applications a 
    LEFT JOIN users u ON a.user_id = u.id 
    $whereClause 
    ORDER BY a.created_at DESC 
    LIMIT $start, $limit
");

/* -------- STATISTICS -------- */
$totalApplications = $conn->query("SELECT COUNT(*) as count FROM applications")->fetch_assoc()['count'];
$approvedCount = $conn->query("SELECT COUNT(*) as count FROM applications WHERE status='Approved'")->fetch_assoc()['count'];
$pendingCount = $conn->query("SELECT COUNT(*) as count FROM applications WHERE status='Pending'")->fetch_assoc()['count'];
$rejectedCount = $conn->query("SELECT COUNT(*) as count FROM applications WHERE status='Rejected'")->fetch_assoc()['count'];
$myReviews = $conn->query("SELECT COUNT(*) as count FROM applications WHERE reviewer_id='" . $_SESSION['reviewer_id'] . "'")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviewer Dashboard - ScholarFlow</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .reviewer-badge {
            background: #9c27b0;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        
        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card.my-reviews {
            background: linear-gradient(135deg, #9c27b0, #e1bee7);
            color: white;
        }
        
        .stat-card.my-reviews h3,
        .stat-card.my-reviews p {
            color: white;
        }
        
        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #666;
            text-transform: uppercase;
        }
        
        .stat-card p {
            margin: 0;
            font-size: 32px;
            font-weight: bold;
            color: #1976d2;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
        }
        
        .alert-success { background: #e8f5e9; color: #2e7d32; border-left: 4px solid #4caf50; }
        .alert-warning { background: #fff3e0; color: #ef6c00; border-left: 4px solid #ff9800; }
        .alert-error { background: #ffebee; color: #c62828; border-left: 4px solid #f44336; }
        .alert-info { background: #e3f2fd; color: #1565c0; border-left: 4px solid #2196f3; }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        .search-filter-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .search-box-reviewer {
            flex: 1;
            min-width: 250px;
        }
        
        .search-box-reviewer input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e3f2fd;
            border-radius: 8px;
            font-size: 14px;
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
            transition: all 0.3s;
        }
        
        .filter-btn.active {
            background: #1976d2;
            color: white;
        }
        
        .filter-btn:not(.active) {
            background: #f5f5f5;
            color: #666;
        }
        
        .filter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .applications-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .applications-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .applications-table th {
            background: #1976d2;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
        }
        
        .applications-table td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }
        
        .applications-table tr:hover {
            background: #f5f5f5;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pending { background: #fff3e0; color: #e65100; }
        .status-approved { background: #e8f5e9; color: #2e7d32; }
        .status-rejected { background: #ffebee; color: #c62828; }
        
        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        
        .action-btn {
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .btn-view { background: #e3f2fd; color: #1976d2; }
        .btn-view:hover { background: #1976d2; color: white; }
        
        .btn-approve { background: #e8f5e9; color: #2e7d32; }
        .btn-approve:hover { background: #4caf50; color: white; }
        
        .btn-reject { background: #ffebee; color: #c62828; }
        .btn-reject:hover { background: #f44336; color: white; }
        
        .btn-edit { background: #fff3e0; color: #ef6c00; }
        .btn-edit:hover { background: #ff9800; color: white; }
        
        .btn-delete { background: #fafafa; color: #666; }
        .btn-delete:hover { background: #d32f2f; color: white; }
        
        .reviewer-tag {
            font-size: 11px;
            color: #9c27b0;
            font-weight: 600;
        }
        
        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 2000;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .modal-overlay.active {
            display: flex;
        }
        
        .modal-container {
            background: white;
            border-radius: 16px;
            max-width: 800px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
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
        }
        
        .modal-header h2 {
            margin: 0;
            color: #1976d2;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .modal-close:hover {
            background: #f5f5f5;
            color: #d32f2f;
        }
        
        .modal-body {
            padding: 25px;
        }
        
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .detail-item {
            margin-bottom: 15px;
        }
        
        .detail-item.full-width {
            grid-column: 1 / -1;
        }
        
        .detail-item label {
            display: block;
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .detail-item p {
            margin: 0;
            font-size: 15px;
            color: #333;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 8px;
        }
        
        .detail-item .essay-content {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
            line-height: 1.6;
            max-height: 200px;
            overflow-y: auto;
        }
        
        /* Edit Form Styles */
        .edit-form .form-group {
            margin-bottom: 15px;
        }
        
        .edit-form label {
            display: block;
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .edit-form input,
        .edit-form select,
        .edit-form textarea {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #e3f2fd;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
        }
        
        .edit-form input:focus,
        .edit-form select:focus,
        .edit-form textarea:focus {
            outline: none;
            border-color: #1976d2;
        }
        
        .edit-form textarea {
            min-height: 100px;
            resize: vertical;
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
            background: #4caf50;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }
        
        .btn-cancel {
            background: #f5f5f5;
            color: #666;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }
        
        .pagination {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-top: 30px;
        }
        
        .page-btn {
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            background: #f5f5f5;
            color: #666;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .page-btn:hover,
        .page-btn.active {
            background: #1976d2;
            color: white;
        }
        
        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .section-title {
            color: #1976d2;
            margin-bottom: 20px;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="topnav">
    <div class="nav-left">
        <h2>🎓 ScholarFlow</h2>
        <span class="reviewer-badge">Reviewer Panel</span>
        <a href="reviewer_dashboard.php">Dashboard</a>
    </div>
    <div class="nav-right">
        <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<div class="main">
    <h1>Reviewer Dashboard 👋</h1>
    
    <?php if($message != ""): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <span><?php echo $messageType === 'success' ? '✓' : ($messageType === 'warning' ? '⚠' : ($messageType === 'error' ? '✗' : 'ℹ')); ?></span>
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Applications</h3>
            <p><?php echo $totalApplications; ?></p>
        </div>
        <div class="stat-card">
            <h3>Pending Review</h3>
            <p><?php echo $pendingCount; ?></p>
        </div>
        <div class="stat-card">
            <h3>Approved</h3>
            <p><?php echo $approvedCount; ?></p>
        </div>
        <div class="stat-card">
            <h3>Rejected</h3>
            <p><?php echo $rejectedCount; ?></p>
        </div>
        <div class="stat-card my-reviews">
            <h3>My Reviews</h3>
            <p><?php echo $myReviews; ?></p>
        </div>
    </div>

    <!-- Chart -->
    <div class="chart-container">
        <h3 class="section-title">📊 Application Overview</h3>
        <canvas id="reviewerChart" height="80"></canvas>
    </div>

    <!-- Search and Filter -->
    <h3 class="section-title">📋 Manage Applications</h3>
    <div class="search-filter-bar">
        <form class="search-box-reviewer" method="GET">
            <input type="text" name="search" placeholder="Search by name, school, or email..." value="<?php echo htmlspecialchars($search); ?>">
        </form>
        <div class="filter-buttons">
            <a class="filter-btn <?php echo $statusFilter == '' ? 'active' : ''; ?>" href="?">All</a>
            <a class="filter-btn <?php echo $statusFilter == 'Pending' ? 'active' : ''; ?>" href="?status=Pending<?php echo $search ? '&search='.urlencode($search) : ''; ?>">Pending</a>
            <a class="filter-btn <?php echo $statusFilter == 'Approved' ? 'active' : ''; ?>" href="?status=Approved<?php echo $search ? '&search='.urlencode($search) : ''; ?>">Approved</a>
            <a class="filter-btn <?php echo $statusFilter == 'Rejected' ? 'active' : ''; ?>" href="?status=Rejected<?php echo $search ? '&search='.urlencode($search) : ''; ?>">Rejected</a>
        </div>
    </div>

    <!-- Applications Table -->
    <div class="applications-table">
        <table>
            <thead>
                <tr>
                    <th>Applicant</th>
                    <th>School / Course</th>
                    <th>GPA</th>
                    <th>Income</th>
                    <th>Status</th>
                    <th>Reviewed By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if($applications->num_rows > 0): ?>
                    <?php while($row = $applications->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($row['last_name'] . ', ' . $row['first_name']); ?></strong>
                            <?php if($row['middle_name']) echo ' ' . substr($row['middle_name'], 0, 1) . '.'; ?>
                            <?php if($row['suffix']) echo ' ' . $row['suffix']; ?><br>
                            <small style="color: #666;">@<?php echo htmlspecialchars($row['username']); ?></small>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($row['school']); ?><br>
                            <small style="color: #666;"><?php echo htmlspecialchars($row['course']); ?> - <?php echo htmlspecialchars($row['year_level']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($row['gpa']); ?></td>
                        <td><?php echo htmlspecialchars($row['family_income']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                                <?php echo $row['status']; ?>
                            </span>
                        </td>
                        <td>
                            <?php if($row['reviewer_name']): ?>
                                <span class="reviewer-tag"><?php echo htmlspecialchars($row['reviewer_name']); ?></span><br>
                                <small style="color: #999;"><?php echo date('M d, Y', strtotime($row['reviewed_at'])); ?></small>
                            <?php else: ?>
                                <span style="color: #999; font-size: 12px;">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="action-btn btn-view" onclick='openViewModal(<?php echo json_encode($row); ?>)'>View</button>
                                
                                <?php
                                    // Build base URL without conflicting parameters
                                    $baseURL = "reviewer_dashboard.php?page=$page";
                                    if($search) $baseURL .= "&search=".urlencode($search);
                                    if($statusFilter) $baseURL .= "&status=".urlencode($statusFilter);
                                    ?>

                                    <?php if($row['status'] == 'Pending'): ?>
                                        <a href="<?php echo $baseURL; ?>&approve=<?php echo $row['id']; ?>" class="action-btn btn-approve" onclick="return confirm('Approve this application?')">Approve</a>
                                        <a href="<?php echo $baseURL; ?>&reject=<?php echo $row['id']; ?>" class="action-btn btn-reject" onclick="return confirm('Reject this application?')">Reject</a>
                                    <?php endif; ?>
                                
                                <button class="action-btn btn-edit" 
                                    onclick='openEditModal(<?php echo json_encode($row); ?>)'>
                                    Edit
                                </button>
                                <a href="?delete=<?php echo $row['id']; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $statusFilter ? '&status='.urlencode($statusFilter) : ''; ?><?php echo $page > 1 ? '&page='.$page : ''; ?>" class="action-btn btn-delete" onclick="return confirm('Are you sure you want to delete this application? This action cannot be undone.')">Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">
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
    </div>

    <!-- Pagination -->
    <?php if($totalPages > 1): ?>
    <div class="pagination">
        <?php for($i=1; $i<=$totalPages; $i++): ?>
            <a class="page-btn <?php echo $i == $page ? 'active' : ''; ?>" href="?page=<?php echo $i; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $statusFilter ? '&status='.urlencode($statusFilter) : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<!-- View Modal -->
<div class="modal-overlay" id="viewModal">
    <div class="modal-container">
        <div class="modal-header">
            <h2>👤 Application Details</h2>
            <button class="modal-close" onclick="closeViewModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="detail-grid">
                <!-- Name Information -->
                <div class="detail-item">
                    <label>Last Name</label>
                    <p id="view-last-name"></p>
                </div>
                <div class="detail-item">
                    <label>First Name</label>
                    <p id="view-first-name"></p>
                </div>
                <div class="detail-item">
                    <label>Middle Name</label>
                    <p id="view-middle-name"></p>
                </div>
                <div class="detail-item">
                    <label>Suffix</label>
                    <p id="view-suffix"></p>
                </div>
                
                <!-- Birth Information -->
                <div class="detail-item">
                    <label>Date of Birth</label>
                    <p id="view-dob"></p>
                </div>
                <div class="detail-item">
                    <label>Place of Birth</label>
                    <p id="view-pob"></p>
                </div>
                <div class="detail-item">
                    <label>Gender</label>
                    <p id="view-gender"></p>
                </div>
                <div class="detail-item">
                    <label>Civil Status</label>
                    <p id="view-civil"></p>
                </div>
                
                <!-- Contact -->
                <div class="detail-item">
                    <label>Contact Number</label>
                    <p id="view-contact"></p>
                </div>
                <div class="detail-item">
                    <label>Alt. Contact</label>
                    <p id="view-alt-contact"></p>
                </div>
                <div class="detail-item">
                    <label>Nationality</label>
                    <p id="view-nationality"></p>
                </div>
                <div class="detail-item">
                    <label>Religion</label>
                    <p id="view-religion"></p>
                </div>
                
                <!-- Address -->
                <div class="detail-item full-width">
                    <label>Complete Address</label>
                    <p id="view-address"></p>
                </div>
                
                <!-- Education -->
                <div class="detail-item">
                    <label>School</label>
                    <p id="view-school"></p>
                </div>
                <div class="detail-item">
                    <label>Course</label>
                    <p id="view-course"></p>
                </div>
                <div class="detail-item">
                    <label>Year Level</label>
                    <p id="view-year"></p>
                </div>
                <div class="detail-item">
                    <label>GPA</label>
                    <p id="view-gpa"></p>
                </div>
                
                <!-- Financial -->
                <div class="detail-item">
                    <label>Family Income</label>
                    <p id="view-income"></p>
                </div>
                <div class="detail-item">
                    <label>Document</label>
                    <p id="view-document"></p>
                </div>
                <div class="detail-item">
                    <label>Status</label>
                    <p id="view-status"></p>
                </div>
                <div class="detail-item">
                    <label>Submitted</label>
                    <p id="view-submitted"></p>
                </div>
                
                <!-- Essay -->
                <div class="detail-item full-width">
                    <label>Personal Statement</label>
                    <div class="essay-content" id="view-essay"></div>
                </div>
                
                <!-- Review Info -->
                <div class="detail-item">
                    <label>Reviewed By</label>
                    <p id="view-reviewer"></p>
                </div>
                <div class="detail-item">
                    <label>Reviewed At</label>
                    <p id="view-reviewed-at"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal for Reviewer -->
<div class="modal-overlay" id="editModal">
    <div class="modal-container">
        <div class="modal-header">
            <h2>📝 Review Application</h2>
            <button class="modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form class="edit-form" method="POST" action="">
                <input type="hidden" name="application_id" id="edit-id">
                <div class="detail-grid">

                    <!-- Non-editable applicant info -->
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" id="edit-fullname" disabled>
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <input type="text" id="edit-gender" disabled>
                    </div>
                    <div class="form-group">
                        <label>Age</label>
                        <input type="text" id="edit-age" disabled>
                    </div>
                    <div class="form-group">
                        <label>School</label>
                        <input type="text" id="edit-school" disabled>
                    </div>
                    <div class="form-group">
                        <label>Course</label>
                        <input type="text" id="edit-course" disabled>
                    </div>
                    <div class="form-group">
                        <label>Year Level</label>
                        <input type="text" id="edit-year" disabled>
                    </div>
                    <div class="form-group full-width">
                        <label>Personal Statement / Essay</label>
                        <textarea id="edit-essay" rows="4" disabled></textarea>
                    </div>

                    <!-- Reviewer editable fields -->
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" id="edit-status" required>
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label>Reviewer Notes</label>
                        <textarea name="review_notes" id="edit-review-notes" rows="4" placeholder="Add your review notes here"></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn-save">Save Review</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Chart
    new Chart(document.getElementById("reviewerChart"), {
        type: 'bar',
        data: {
            labels: ["Pending", "Approved", "Rejected"],
            datasets: [{
                label: "Applications",
                data: [
                    <?php echo (int)$pendingCount; ?>,
                    <?php echo (int)$approvedCount; ?>,
                    <?php echo (int)$rejectedCount; ?>
                ],
                backgroundColor: ["#ff9800", "#4caf50", "#f44336"],
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            animation: {
                duration: 1500,
                easing: 'easeOutBounce'
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });

    // View Modal Functions
    function openViewModal(data) {
        document.getElementById('view-last-name').textContent = data.last_name || '-';
        document.getElementById('view-first-name').textContent = data.first_name || '-';
        document.getElementById('view-middle-name').textContent = data.middle_name || '-';
        document.getElementById('view-suffix').textContent = data.suffix || '-';
        document.getElementById('view-dob').textContent = data.date_of_birth || '-';
        document.getElementById('view-pob').textContent = data.place_of_birth || '-';
        document.getElementById('view-gender').textContent = data.gender || '-';
        document.getElementById('view-civil').textContent = data.civil_status || '-';
        document.getElementById('view-contact').textContent = data.contact || '-';
        document.getElementById('view-alt-contact').textContent = data.alt_contact || '-';
        document.getElementById('view-nationality').textContent = data.nationality || '-';
        document.getElementById('view-religion').textContent = data.religion || '-';
        document.getElementById('view-address').textContent = data.address || '-';
        document.getElementById('view-school').textContent = data.school || '-';
        document.getElementById('view-course').textContent = data.course || '-';
        document.getElementById('view-year').textContent = data.year_level || '-';
        document.getElementById('view-gpa').textContent = data.gpa || '-';
        document.getElementById('view-income').textContent = data.family_income || '-';
        document.getElementById('view-essay').textContent = data.essay || 'No essay provided';
        document.getElementById('view-status').innerHTML = '<span class="status-badge status-' + data.status.toLowerCase() + '">' + data.status + '</span>';
        document.getElementById('view-reviewer').textContent = data.reviewer_name || 'Not reviewed yet';
        document.getElementById('view-reviewed-at').textContent = data.reviewed_at ? new Date(data.reviewed_at).toLocaleDateString() : '-';
        document.getElementById('view-submitted').textContent = new Date(data.created_at).toLocaleDateString();
        
        if (data.document) {
            document.getElementById('view-document').innerHTML =
                '<a href="uploads/' + data.document + '" target="_blank" style="color: #1976d2;">📄 View Document</a>';
        } else {
            document.getElementById('view-document').textContent = 'No document';
        }
        
        document.getElementById('viewModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeViewModal() {
        document.getElementById('viewModal').classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    // Edit Modal Functions - Updated with all application fields
    function openEditModal(data) {
    document.getElementById('edit-id').value = data.id;

    // Non-editable info
    document.getElementById('edit-fullname').value =
        `${data.last_name}, ${data.first_name} ${data.middle_name || ''} ${data.suffix || ''}`.trim();
    document.getElementById('edit-gender').value = data.gender || '-';
    
    // Calculate age from date of birth
    if(data.date_of_birth) {
        const dob = new Date(data.date_of_birth);
        const ageDifMs = Date.now() - dob.getTime();
        const ageDate = new Date(ageDifMs);
        document.getElementById('edit-age').value = Math.abs(ageDate.getUTCFullYear() - 1970);
    } else {
        document.getElementById('edit-age').value = '-';
    }

    document.getElementById('edit-school').value = data.school || '-';
    document.getElementById('edit-course').value = data.course || '-';
    document.getElementById('edit-year').value = data.year_level || '-';
    document.getElementById('edit-essay').value = data.essay || 'No essay provided';

    // Editable reviewer fields
    document.getElementById('edit-status').value = data.status || 'Pending';
    document.getElementById('edit-review-notes').value = data.review_notes || '';

    document.getElementById('editModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('active');
    document.body.style.overflow = 'auto';
}

// Close modal on overlay click
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeEditModal();
});

</script>

</body>
</html>
