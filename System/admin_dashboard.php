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

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM applications WHERE id=$id");
    $message = "Application Deleted!";
}

/* -------- HANDLE REVIEWER ACTIONS -------- */
if (isset($_POST['add_reviewer'])) {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $fullname = $_POST['fullname'];

    // Check if email already exists
    $check = $conn->prepare("SELECT * FROM admins WHERE email=?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();
    if($result->num_rows > 0){
        $reviewer_message = "Email already exists!";
    } else {
        $stmt = $conn->prepare("INSERT INTO admins (email, password, fullname, role) VALUES (?, ?, ?, 'reviewer')");
        $stmt->bind_param("sss", $email, $password, $fullname);
        if($stmt->execute()){
            $reviewer_message = "Reviewer added successfully!";
        } else {
            $reviewer_message = "Error adding reviewer!";
        }
    }
}

if(isset($_GET['delete_reviewer'])){
    $id = intval($_GET['delete_reviewer']);
    $conn->query("DELETE FROM admins WHERE id=$id AND role='reviewer'");
    $reviewer_message = "Reviewer deleted!";
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
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body>

<div class="topnav">
    <div class="nav-left">
    <h2>🎓 Scholarship System</h2>
    <a href="admin_dashboard.php">Dashboard</a>
    <?php if(isset($_SESSION['role']) && ($_SESSION['role'] === 'reviewer' || $_SESSION['role'] === 'admin')): ?>
        <a href="reviewer_dashboard.php" class="btn">Reviewer Dashboard</a>
    <?php endif; ?>
    </div>
</div>

<div class="main">
    <h1>Welcome, <?php echo $_SESSION['fullname']; ?> 👋</h1>

    <!-- TAB NAVIGATION -->
    <div class="tabs">
        <button class="tab-btn active" onclick="openTab(event, 'applicationsTab')">Applications</button>
        <button class="tab-btn" onclick="openTab(event, 'reviewersTab')">Manage Reviewers</button>
    </div>

    <!-- APPLICATIONS TAB -->
    <div id="applicationsTab" class="tab-content" style="display:block;">

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
                <p><?php echo $pendingCount; ?></p>
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
                    y: { beginAtZero: true, suggestedMax: 50, ticks: { precision: 0 } }
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

        <!-- TABLE -->
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
                    <?php else: ?> No File <?php endif; ?>
                </td>
                <td class="actions">
                    <a class="btn" onclick="openModal(
                        '<?php echo addslashes($row['fullname']); ?>',
                        '<?php echo addslashes($row['email']); ?>',
                        '<?php echo addslashes($row['school']); ?>',
                        '<?php echo addslashes($row['course']); ?>',
                        '<?php echo addslashes($row['essay']); ?>',
                    )">View</a>
                    <a class="approve" href="?approve=<?php echo $row['id']; ?>">Approve</a>
                    <a class="reject" href="?reject=<?php echo $row['id']; ?>">Reject</a>
                    <a class="delete" href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete this application?')">Delete</a>
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

    <!-- REVIEWERS TAB -->
    <div id="reviewersTab" class="tab-content" style="display:none;">
        <h2>Manage Reviewers</h2>

        <?php if(isset($reviewer_message)): ?>
            <p style="color:green;font-weight:bold;"><?php echo $reviewer_message; ?></p>
        <?php endif; ?>

        <!-- Add Reviewer Form -->
        <form method="POST" class="reviewer-form">
            <input type="text" name="fullname" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="add_reviewer">Add Reviewer</button>
        </form>

        <!-- Reviewers Table -->
        <table>
            <tr>
                <th>Full Name</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
            <?php while($rev = $reviewers->fetch_assoc()): ?>
            <tr>
                <td><?php echo $rev['fullname']; ?></td>
                <td><?php echo $rev['email']; ?></td>
                <td>
                    <a class="delete" href="?delete_reviewer=<?php echo $rev['id']; ?>" 
                       onclick="return confirm('Delete this reviewer?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

</div>

<!-- MODAL -->
<div class="modal" id="detailsModal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">X</span>
        <h3 id="m_name"></h3>
        <p><b>Email:</b> <span id="m_email"></span></p>
        <p><b>School:</b> <span id="m_school"></span></p>
        <p><b>Course:</b> <span id="m_course"></span></p>
        <p><b>Essay:</b> <span id="m_essay"></span></p>
    </div>
</div>

<script>
function openModal(name,email,school,course,essay){
    document.getElementById("detailsModal").style.display="block";
    document.getElementById("m_name").innerText=name;
    document.getElementById("m_email").innerText=email;
    document.getElementById("m_school").innerText=school;
    document.getElementById("m_course").innerText=course;
    document.getElementById("m_essay").innerText=essay;
}
function closeModal(){
    document.getElementById("detailsModal").style.display="none";
}

// TAB SWITCHING
function openTab(evt, tabName) {
    var tabcontent = document.getElementsByClassName("tab-content");
    for (var i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    var tablinks = document.getElementsByClassName("tab-btn");
    for (var i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.className += " active";
}
</script>

</body>
</html>