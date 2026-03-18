<?php
include "db.php";

// Check if form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $fullname = $_POST['fullname'];
    
    $stmt = $conn->prepare("INSERT INTO reviewers (email, password, fullname) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $password, $fullname);
    
    if ($stmt->execute()) {
        echo "<h2 style='color: green;'>✓ Reviewer account created successfully!</h2>";
        echo "<p>Email: " . htmlspecialchars($email) . "</p>";
        echo "<p>Name: " . htmlspecialchars($fullname) . "</p>";
        echo "<a href='login.php'>Go to Login</a>";
    } else {
        echo "<h2 style='color: red;'>Error creating reviewer account</h2>";
        echo "<p>" . $conn->error . "</p>";
    }
    $stmt->close();
    $conn->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Reviewer Account</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            max-width: 500px;
            margin: 50px auto;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(156, 39, 176, 0.15);
        }
        .reviewer-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .reviewer-badge-large {
            background: #9c27b0;
            color: white;
            padding: 10px 25px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 15px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #424242;
        }
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e3f2fd;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .form-group input:focus {
            outline: none;
            border-color: #9c27b0;
            box-shadow: 0 0 0 3px rgba(156, 39, 176, 0.1);
        }
        .btn-create {
            width: 100%;
            padding: 14px;
            background: #9c27b0;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-create:hover {
            background: #7b1fa2;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(156, 39, 176, 0.3);
        }
        .note {
            background: #f3e5f5;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #9c27b0;
            font-size: 13px;
            color: #666;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="reviewer-header">
        <span class="reviewer-badge-large">REVIEWER ROLE</span>
        <h2>Create Reviewer Account</h2>
    </div>
    
    <div class="note">
        <strong>Note:</strong> Reviewers can view, edit, approve, and reject scholarship applications. They cannot delete applications permanently without confirmation.
    </div>
    
    <form method="POST" action="">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="fullname" placeholder="Enter full name" required>
        </div>
        
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="reviewer@example.com" required>
        </div>
        
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Create a secure password" required minlength="8">
        </div>
        
        <button type="submit" class="btn-create">Create Reviewer Account</button>
    </form>
    
    <div style="text-align: center; margin-top: 20px;">
        <a href="login.php" style="color: #666; text-decoration: none;">← Back to Login</a>
    </div>
</div>

</body>
</html>