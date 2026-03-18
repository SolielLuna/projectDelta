<?php
session_start();
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check normal users
    $sql_user = "SELECT * FROM users WHERE email=?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("s", $email);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();

    if ($result_user->num_rows == 1) {
        $user = $result_user->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];  // Changed from fullname
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = 'user';
            header("Location: profile.php");
            exit();
        } else {
            echo "<h3>Invalid Password!</h3><a href='login.php'>Try Again</a>";
            exit();
        }
    }

    // Check admin users
    $sql_admin = "SELECT * FROM admins WHERE email=?";
    $stmt_admin = $conn->prepare($sql_admin);
    $stmt_admin->bind_param("s", $email);
    $stmt_admin->execute();
    $result_admin = $stmt_admin->get_result();

    if ($result_admin->num_rows == 1) {
        $admin = $result_admin->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['username'] = $admin['fullname'];  // Keep fullname for admin or change to username
            $_SESSION['email'] = $admin['email'];
            $_SESSION['role'] = 'admin';
            header("Location: admin_dashboard.php");
            exit();
        } else {
            echo "<h3>Invalid Password!</h3><a href='login.php'>Try Again</a>";
            exit();
        }
    }

    // Check reviewers
    $sql_reviewer = "SELECT * FROM reviewers WHERE email=?";
    $stmt_reviewer = $conn->prepare($sql_reviewer);
    $stmt_reviewer->bind_param("s", $email);
    $stmt_reviewer->execute();
    $result_reviewer = $stmt_reviewer->get_result();

    if ($result_reviewer->num_rows == 1) {
        $reviewer = $result_reviewer->fetch_assoc();
        if (password_verify($password, $reviewer['password'])) {
            $_SESSION['reviewer_id'] = $reviewer['id'];
            $_SESSION['username'] = $reviewer['fullname'];  // Keep fullname for reviewer or change
            $_SESSION['email'] = $reviewer['email'];
            $_SESSION['role'] = 'reviewer';
            header("Location: reviewer_dashboard.php");
            exit();
        } else {
            echo "<h3>Invalid Password!</h3><a href='login.php'>Try Again</a>";
            exit();
        }
    }

    // If neither found
    echo "<h3>User not found!</h3><a href='login.php'>Try Again</a>";
    $conn->close();
}
?>