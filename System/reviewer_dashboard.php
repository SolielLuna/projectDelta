<?php
session_start();
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'reviewer' && $_SESSION['role'] !== 'admin')) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "scholarship_db");

$message = "";

/* -------- HANDLE APPLICATION ACTIONS -------- */
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

/* -------- SEARCH -------- */
$search = isset($_GET['search']) ? $_GET['search'] : "";

/* -------- FILTER -------- */
$statusFilter = isset($_GET['status']) ? $_GET['status'] : "";
$whereStatus = "";
if ($statusFilter != "") {
    $whereStatus = "AND status='$statusFilter'";
}

/* -------- PAGINATION -------- */
$limit = 5;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page - 1) * $limit;

$totalQuery = $conn->query("
    SELECT COUNT(*) as count FROM applications 
    WHERE (fullname LIKE '%$search%' OR school LIKE '%$search%')
    $whereStatus
");

$totalRows = $totalQuery->fetch_assoc()['count'];
$totalPages = ceil($totalRows / $limit);

/* -------- FETCH APPLICATION DATA -------- */
$applications = $conn->query("
    SELECT * FROM applications
    WHERE (fullname LIKE '%$search%' OR school LIKE '%$search%')
    $whereStatus
    ORDER BY created_at DESC
    LIMIT $start, $limit
");

/* -------- COUNTS -------- */
$totalUsers = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$totalApplications = $conn->query("SELECT COUNT(*) as count FROM applications")->fetch_assoc()['count'];
$approvedCount = $conn->query("SELECT COUNT(*) as count FROM applications WHERE status='Approved'")->fetch_assoc()['count'];
$pendingCount = $conn->query("SELECT COUNT(*) as count FROM applications WHERE status='Pending'")->fetch_assoc()['count'];
$rejectedCount = $conn->query("SELECT COUNT(*) as count FROM applications WHERE status='Rejected'")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reviewer Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="topnav">
    <div class="nav-left">
        <h2>🎓 Scholarship System</h2>
        <a href="admin_dashboard.php">Dashboard</a>
    </div>
    <div class="nav-right">
        <span>Welcome, <?php echo $_SESSION['fullname']; ?></span>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<div class="main">
    <h1>Welcome, <?php echo $_SESSION['fullname']; ?> 👋</h1>

    <?php if($message != ""): ?>
        <p style="color:green;font-weight:bold;"><?php echo $message; ?></p>
    <?php endif; ?>

    <!-- CARDS -->
    <div class="cards">
        <div class="card">
            <h3>Total Users</h3>
            <p><?php echo $totalUsers; ?></p>
        </div>
        <div class="card">
            <h3>Total Applications</h3>
            <p><?php echo $totalApplications; ?></p>
        </div>
        <div class="card">
            <h3>Approved</h3>
            <p><?php echo $approvedCount; ?></p>
        </div>
        <div class="card">
            <h3>Pending</h3>
            <p><?php echo $pendingCount; ?></p>
        </div>
        <div class="card">
            <h3>Rejected</h3>
            <p><?php echo $rejectedCount; ?></p>
        </div>
    </div>

    <!-- CHART -->
    <canvas id="appChart" height="100"></canvas>
    <script>
new Chart(document.getElementById("appChart"), {
    type: 'bar',
    data: {
        labels: ["Approved", "Pending", "Rejected"],
        datasets: [{
            label: "Applications",
            data: [
                <?php echo (int)$approvedCount; ?>,
                <?php echo (int)$pendingCount; ?>,
                <?php echo (int)$rejectedCount; ?>
            ],
            backgroundColor: ["green", "orange", "red"]
        }]
    },
    options: {
        animation: { duration: 1500, easing: 'easeOutBounce' },
        scales: {
            y: { 
                beginAtZero: true,
                suggestedMax: 50,
                ticks: { precision: 0 }
            }
        },
        plugins: { legend: { display: false } }
    }
});
    </script>

    <!-- SEARCH -->
    <form class="search-box" method="GET">
        <input type="text" name="search" placeholder="Search by name or school" value="<?php echo $search; ?>">
        <button type="submit">Search</button>
    </form>

    <!-- FILTER BUTTONS -->
    <div class="filters">
        <a class="btn" href="?">All</a>
        <a class="btn" href="?status=Pending">Pending</a>
        <a class="btn" href="?status=Approved">Approved</a>
        <a class="btn" href="?status=Rejected">Rejected</a>
    </div>

    <!-- APPLICATION TABLE -->
    <table>
        <tr>
            <th>Name</th>
            <th>School</th>
            <th>Course</th>
            <th>Status</th>
            <th>Document</th>
            <th>Actions</th>
        </tr>
        <?php while($row = $applications->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['fullname']; ?></td>
            <td><?php echo $row['school']; ?></td>
            <td><?php echo $row['course']; ?></td>
            <td class="status-<?php echo strtolower($row['status']); ?>"><?php echo $row['status']; ?></td>
            <td>
                <?php if($row['document']): ?>
                    <a href="uploads/<?php echo $row['document']; ?>" target="_blank">View</a>
                <?php else: ?>
                    No File
                <?php endif; ?>
            </td>
            <td class="actions">
                <a class="approve" href="?approve=<?php echo $row['id']; ?>">Approve</a>
                <a class="reject" href="?reject=<?php echo $row['id']; ?>">Reject</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <!-- PAGINATION -->
    <br>
    <?php for($i=1; $i<=$totalPages; $i++): ?>
        <a class="btn" href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>&status=<?php echo $statusFilter; ?>">
            <?php echo $i; ?>
        </a>
    <?php endfor; ?>

</div>
</body>
</html>