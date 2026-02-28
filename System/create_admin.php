<?php
include "db.php";

$email = "admin@example.com";
$password = password_hash("AdminPass123", PASSWORD_DEFAULT); // hashed password
$fullname = "Super Admin";

$sql = "INSERT INTO admins (email, password, fullname) VALUES ('$email', '$password', '$fullname')";
if ($conn->query($sql) === TRUE) {
    echo "Admin account created successfully!";
} else {
    echo "Error: " . $conn->error;
}
$conn->close();
?>