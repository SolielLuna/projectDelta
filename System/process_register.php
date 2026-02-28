<?php
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (fullname, email, password)
            VALUES ('$fullname', '$email', '$hashed_password')";

    if ($conn->query($sql) === TRUE) {
        echo "<h2>Registration Successful!</h2>";
        echo "<a href='index.php'>Go Back to Homepage</a>";
    } else {
        echo "Error: " . $conn->error;
    }

    $conn->close();
}
?>