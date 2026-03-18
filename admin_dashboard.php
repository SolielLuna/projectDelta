
# Creating the updated admin_dashboard.php with edit functionality for users and reviewers

admin_dashboard_content = '''<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "scholarship_db");

$message = "";
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'applications';

/* -------- APPLICATION ACTIONS -------- */
if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $conn->query("UPDATE applications SET status='Approved' WHERE id=$id");
    $message = "Application Approved!";
}

if (isset($_GET['reject'])) {
    $id = intval($_GET['reject']);
    $conn->query("UPDATE applications SET status='Rejected' WHERE id=$id");
    $message = "Application Rejected!";
}

if (isset($_GET['delete_app'])) {
    $id = intval($_GET['delete_app']);
    $conn->query("DELETE FROM applications WHERE id=$id");
    $message = "Application Deleted!";
}

/* -------- USER MANAGEMENT ACTIONS -------- */
if (isset($_GET['delete_user'])) {
    $id = intval($_GET['delete_user']);
    $conn->query("DELETE FROM users WHERE id=$id");
    $conn->query("DELETE FROM applications WHERE user_id=$id");
    $message = "User and their applications deleted!";
}

// Edit User
if (isset($_POST['edit_user'])) {
    $id = intval($_POST['user_id']);
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    
    $stmt = $conn->prepare("UPDATE users SET username=?, email=? WHERE id=?");
    $stmt->bind_param("ssi", $username, $email, $id);
    if ($stmt->execute()) {
        $message = "User updated successfully!";
    } else {
        $message = "Error updating user: " . $conn->error;
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
    } else {
        $message = "Error adding user: " . $conn->error;
    }
    $stmt->close();
}

/* -------- REVIEWER MANAGEMENT ACTIONS -------- */
if (isset($_GET['delete_reviewer'])) {
    $id = intval($_GET['delete_reviewer']);
    $conn->query("DELETE FROM reviewers WHERE id=$id");
    $message = "Reviewer deleted!";
}

// Edit Reviewer
if (isset($_POST['edit_reviewer'])) {
    $id = intval($_POST['reviewer_id']);
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $email = $conn->real_escape_string($_POST['email']);
    
    $stmt = $conn->prepare("UPDATE reviewers SET fullname=?, email=? WHERE id=?");
    $stmt->bind_param("ssi", $fullname, $email, $id);
    if ($stmt->execute()) {
        $message = "Reviewer updated successfully!";
    } else {
        $message = "Error updating reviewer: " . $conn->error;
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
    } else {
        $message = "Error adding reviewer: " . $conn->error;
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
        .admin-badge {
            background: #d32f2f;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-left: 10px;
        }
        
        .tab-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .tab-nav {
            display: flex;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .tab-btn {
            padding: 15px 25px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            color: #666;
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
        }
        
        .tab-btn:hover {
            color: #1976d2;
            background: #f5f5f5;
        }
        
        .tab-btn.active {
            color: #1976d2;
            border-bottom-color: #1976d2;
        }
        
        .tab-content {
            display: none;
            padding: 25px;
        }
        
        .tab-content.active {
            display: block;
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
        
        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 13px;
            color: #666;
            text-transform: uppercase;
        }
        
        .stat-card p {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
            color: #1976d2;
        }
        
        .stat-card.users { background: linear-gradient(135deg, #4caf50, #c8e6c9); }
        .stat-card.users h3, .stat-card.users p { color: white; }
        
        .stat-card.reviewers { background: linear-gradient(135deg, #9c27b0, #e1bee7); }
        .stat-card.reviewers h3, .stat-card.reviewers p { color: white; }
        
        .add-btn {
            background: #4caf50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .add-btn:hover {
            background: #388e3c;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th {
            background: #1976d2;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        
        .data-table td {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .data-table tr:hover {
            background: #f5f5f5;
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
            margin-right: 5px;
        }
        
        .btn-view { background: #e3f2fd; color: #1976d2; }
        .btn-view:hover { background: #1976d2; color: white; }
        .btn-edit { background: #fff3e0; color: #ef6c00; }
        .btn-edit:hover { background: #ff9800; color: white; }
        .btn-delete { background: #ffebee; color: #c62828; }
        .btn-delete:hover { background: #c62828; color: white; }
        .btn-approve { background: #e8f5e9; color: #2e7d32; }
        .btn-reject { background: #ffebee; color: #c62828; }
        
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }
        
        .modal.active { display: flex; }
        
        .modal-content {
            background: white;
            border-radius: 16px;
            max-width: 900px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            padding: 20px 25px;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-body {
            padding: 25px;
        }
        
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
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
        }
        
        .detail-item p {
            margin: 0;
            font-size: 14px;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 8px;
        }
        
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
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e3f2fd;
            border-radius: 8px;
            box-sizing: border-box;
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
        
        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }
        
        .section-title {
            color: #1976d2;
            margin-bottom: 20px;
            font-size: 20px;
        }
    </style>
</head>
<body>

<div class="topnav">
    <div class="nav-left">
        <h2>🎓 ScholarFlow <span class="admin-badge">Admin</span></h2>
    </div>
    <div class="nav-right">
        <span>Welcome, <?php echo $_SESSION['username']; ?></span>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<div class="main">
    <h1>Admin Dashboard 👋</h1>
    
    <?php if($message != ""): ?>
        <p style="color:green;font-weight:bold;"><?php echo $message; ?></p>
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
        <div class="stat-card">
            <h3>Approved</h3>
            <p><?php echo $approvedCount; ?></p>
        </div>
        <div class="stat-card">
            <h3>Pending</h3>
            <p><?php echo $pendingCount; ?></p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tab-container">
        <div class="tab-nav">
            <button class="tab-btn <?php echo $active_tab == 'applications' ? 'active' : ''; ?>" onclick="window.location.href='?tab=applications'">📋 Applications</button>
            <button class="tab-btn <?php echo $active_tab == 'users' ? 'active' : ''; ?>" onclick="window.location.href='?tab=users'">👥 Users</button>
            <button class="tab-btn <?php echo $active_tab == 'reviewers' ? 'active' : ''; ?>" onclick="window.location.href='?tab=reviewers'">🔍 Reviewers</button>
        </div>

        <!-- Applications Tab -->
        <div class="tab-content <?php echo $active_tab == 'applications' ? 'active' : ''; ?>" id="applications-tab">
            <h3 class="section-title">Manage Applications</h3>
            
            <form method="GET" style="margin-bottom: 20px;">
                <input type="hidden" name="tab" value="applications">
                <input type="text" name="search" placeholder="Search by name or school" value="<?php echo $search; ?>" style="padding: 10px; width: 300px; border: 2px solid #e3f2fd; border-radius: 8px;">
                <button type="submit" class="btn" style="padding: 10px 20px;">Search</button>
            </form>

            <div class="filters" style="margin-bottom: 20px;">
                <a class="btn <?php echo $statusFilter == '' ? 'btn-primary' : ''; ?>" href="?tab=applications">All</a>
                <a class="btn <?php echo $statusFilter == 'Pending' ? 'btn-primary' : ''; ?>" href="?tab=applications&status=Pending">Pending</a>
                <a class="btn <?php echo $statusFilter == 'Approved' ? 'btn-primary' : ''; ?>" href="?tab=applications&status=Approved">Approved</a>
                <a class="btn <?php echo $statusFilter == 'Rejected' ? 'btn-primary' : ''; ?>" href="?tab=applications&status=Rejected">Rejected</a>
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
                    <?php while($row = $applications->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <strong><?php echo $row['last_name'] . ', ' . $row['first_name']; ?></strong>
                            <?php if($row['middle_name']) echo ' ' . substr($row['middle_name'], 0, 1) . '.'; ?>
                            <?php if($row['suffix']) echo ' ' . $row['suffix']; ?><br>
                            <small style="color: #666;">@<?php echo $row['username']; ?></small>
                        </td>
                        <td>
                            <small>
                            📞 <?php echo $row['contact']; ?><br>
                            🎂 <?php echo date('M d, Y', strtotime($row['date_of_birth'])); ?><br>
                            ♂️ <?php echo $row['gender']; ?> | <?php echo $row['civil_status']; ?>
                            </small>
                        </td>
                        <td><?php echo $row['school']; ?></td>
                        <td><?php echo $row['course']; ?> (<?php echo $row['year_level']; ?>)</td>
                        <td>
                            <span style="padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold;
                                <?php echo $row['status'] == 'Approved' ? 'background: #e8f5e9; color: #2e7d32;' : ($row['status'] == 'Rejected' ? 'background: #ffebee; color: #c62828;' : 'background: #fff3e0; color: #ef6c00;'); ?>">
                                <?php echo $row['status']; ?>
                            </span>
                        </td>
                        <td>
                            <button class="action-btn btn-view" onclick="viewApplication(<?php echo htmlspecialchars(json_encode($row)); ?>)" style="margin-right: 5px;">View</button>
                            <?php if($row['status'] == 'Pending'): ?>
                                <a href="?tab=applications&approve=<?php echo $row['id']; ?>" class="action-btn btn-approve" onclick="return confirm('Approve?')">✓</a>
                                <a href="?tab=applications&reject=<?php echo $row['id']; ?>" class="action-btn btn-reject" onclick="return confirm('Reject?')">✗</a>
                            <?php endif; ?>
                            <a href="?tab=applications&delete_app=<?php echo $row['id']; ?>" class="action-btn btn-delete" onclick="return confirm('Delete?')">🗑</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if($totalPages > 1): ?>
            <div style="margin-top: 20px; text-align: center;">
                <?php for($i=1; $i<=$totalPages; $i++): ?>
                    <a href="?tab=applications&page=<?php echo $i; ?>&search=<?php echo $search; ?>&status=<?php echo $statusFilter; ?>" 
                       style="padding: 8px 12px; margin: 0 3px; background: <?php echo $i==$page ? '#1976d2' : '#f5f5f5'; ?>; color: <?php echo $i==$page ? 'white' : '#666'; ?>; border-radius: 5px; text-decoration: none;">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Users Tab -->
        <div class="tab-content <?php echo $active_tab == 'users' ? 'active' : ''; ?>" id="users-tab">
            <h3 class="section-title">Manage Users</h3>
            <button class="add-btn" onclick="document.getElementById('addUserModal').classList.add('active')">+ Add User</button>
            
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
                        <td><?php echo $user['username']; ?></td>
                        <td><?php echo $user['email']; ?></td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <button class="action-btn btn-edit" onclick="openEditUserModal(<?php echo $user['id']; ?>, '<?php echo addslashes($user['username']); ?>', '<?php echo addslashes($user['email']); ?>')">Edit</button>
                            <a href="?tab=users&delete_user=<?php echo $user['id']; ?>" class="action-btn btn-delete" onclick="return confirm('Delete this user and all their applications?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Reviewers Tab -->
        <div class="tab-content <?php echo $active_tab == 'reviewers' ? 'active' : ''; ?>" id="reviewers-tab">
            <h3 class="section-title">Manage Reviewers</h3>
            <button class="add-btn" onclick="document.getElementById('addReviewerModal').classList.add('active')">+ Add Reviewer</button>
            
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
                        <td><?php echo $reviewer['fullname']; ?></td>
                        <td><?php echo $reviewer['email']; ?></td>
                        <td><?php echo date('M d, Y', strtotime($reviewer['created_at'])); ?></td>
                        <td>
                            <button class="action-btn btn-edit" onclick="openEditReviewerModal(<?php echo $reviewer['id']; ?>, '<?php echo addslashes($reviewer['fullname']); ?>', '<?php echo addslashes($reviewer['email']); ?>')">Edit</button>
                            <a href="?tab=reviewers&delete_reviewer=<?php echo $reviewer['id']; ?>" class="action-btn btn-delete" onclick="return confirm('Delete this reviewer?')">Delete</a>
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
            <button class="close-btn" onclick="document.getElementById('viewModal').classList.remove('active')">&times;</button>
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
            <button class="close-btn" onclick="document.getElementById('addUserModal').classList.remove('active')">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST">
                <input type="hidden" name="add_user" value="1">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required minlength="6">
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
<div class="modal" id="editUserModal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h2>✏️ Edit User</h2>
            <button class="close-btn" onclick="document.getElementById('editUserModal').classList.remove('active')">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST">
                <input type="hidden" name="edit_user" value="1">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" id="edit_user_username" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="edit_user_email" required>
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
            <button class="close-btn" onclick="document.getElementById('addReviewerModal').classList.remove('active')">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST">
                <input type="hidden" name="add_reviewer" value="1">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="fullname" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required minlength="6">
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
            <button class="close-btn" onclick="document.getElementById('editReviewerModal').classList.remove('active')">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST">
                <input type="hidden" name="edit_reviewer" value="1">
                <input type="hidden" name="reviewer_id" id="edit_reviewer_id">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="fullname" id="edit_reviewer_fullname" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="edit_reviewer_email" required>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="document.getElementById('editReviewerModal').classList.remove('active')">Cancel</button>
                    <button type="submit" class="btn-save">Save Changes</button>
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
                <label>Address</label>
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
                <label>Essay</label>
                <p style="white-space: pre-wrap;">${data.essay || 'No essay'}</p>
            </div>
            <div class="detail-item">
                <label>Document</label>
                <p>${data.document ? `<a href="uploads/${data.document}" target="_blank">View Document</a>` : 'No file'}</p>
            </div>
            <div class="detail-item">
                <label>Status</label>
                <p><strong>${data.status}</strong></p>
            </div>
            <div class="detail-item">
                <label>Submitted</label>
                <p>${data.created_at}</p>
            </div>
        </div>
    `;
    document.getElementById('viewModal').classList.add('active');
}

// Edit User Modal Functions
function openEditUserModal(id, username, email) {
    document.getElementById('edit_user_id').value = id;
    document.getElementById('edit_user_username').value = username;
    document.getElementById('edit_user_email').value = email;
    document.getElementById('editUserModal').classList.add('active');
}

// Edit Reviewer Modal Functions
function openEditReviewerModal(id, fullname, email) {
    document.getElementById('edit_reviewer_id').value = id;
    document.getElementById('edit_reviewer_fullname').value = fullname;
    document.getElementById('edit_reviewer_email').value = email;
    document.getElementById('editReviewerModal').classList.add('active');
}

// Close modals on outside click
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.classList.remove('active');
    }
}

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.classList.remove('active');
        });
    }
});
</script>

</body>
</html>