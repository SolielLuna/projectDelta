<?php
include "db.php";

/* ADMIN */
$email = "admin@example.com";
$password = password_hash("admin123", PASSWORD_DEFAULT);
$fullname = "Super Admin";
$role = "admin";

$conn->query("INSERT INTO admins (email,password,fullname,role)
VALUES ('$email','$password','$fullname','$role')");


/* REVIEWER */
$email = "reviewer@example.com";
$password = password_hash("reviewer123", PASSWORD_DEFAULT);
$fullname = "Reviewer";
$role = "reviewer";

$conn->query("INSERT INTO admins (email,password,fullname,role)
VALUES ('$email','$password','$fullname','$role')");

echo "Admin and Reviewer accounts created!";
$conn->close();
?>