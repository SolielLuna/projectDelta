<?php
session_start();
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            // Store session data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];

            // Redirect to profile page
            header("Location: profile.php");
            exit();

        } else {
            echo "<h3>Invalid Password!</h3>";
            echo "<a href='login.php'>Try Again</a>";
        }

    } else {
        echo "<h3>User not found!</h3>";
        echo "<a href='login.php'>Try Again</a>";
    }

    $conn->close();
}
?>