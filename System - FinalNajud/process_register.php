<?php
include "db.php";

// Initialize variables and error array
$errors = [];
$success = false;

// Only process if POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Sanitize inputs
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $username = trim($_POST['username'] ?? '');
    
    // Validation
    if (empty($username)) {
        $errors[] = "User name is required";
    } elseif (strlen($username) < 2 || strlen($username) > 100) {
        $errors[] = "User name must be between 2 and 100 characters";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    } elseif (!preg_match("/[A-Z]/", $password) || 
              !preg_match("/[a-z]/", $password) || 
              !preg_match("/[0-9]/", $password)) {
        $errors[] = "Password must contain uppercase, lowercase, and numbers";
    }
    
    // Check if email already exists
    if (empty($errors)) {
        $check_sql = "SELECT id FROM users WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $errors[] = "Email already registered";
        }
        $check_stmt->close();
    }
    
    // If no errors, proceed with registration
    if (empty($errors)) {
        // Hash password securely
        $hashed_password = password_hash($password, PASSWORD_ARGON2ID);
        
        // Use prepared statement to prevent SQL injection
        $sql = "INSERT INTO users (username, email, password, created_at) 
                VALUES (?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $email, $hashed_password);
        
        if ($stmt->execute()) {
            $success = true;
            
            // Optional: Send welcome email or set session
            session_start();
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            
            // Redirect to dashboard (optional)
            // header("Location: dashboard.php");
            // exit();
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
        
        $stmt->close();
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration - ScholarFlow</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .message-container {
            max-width: 500px;
            margin: 100px auto;
            padding: 40px;
            text-align: center;
        }
        
        .success-box {
            background: #e8f5e9;
            border: 2px solid #4caf50;
            border-radius: 10px;
            padding: 30px;
            animation: slideIn 0.5s ease;
        }
        
        .error-box {
            background: #ffebee;
            border: 2px solid #f44336;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            text-align: left;
        }
        
        .error-box ul {
            margin: 0;
            padding-left: 20px;
            color: #c62828;
        }
        
        .success-icon {
            font-size: 60px;
            color: #4caf50;
            margin-bottom: 20px;
        }
        
        .btn-home {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: #1976d2;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            transition: all 0.3s;
        }
        
        .btn-home:hover {
            background: #0d47a1;
            transform: translateY(-2px);
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<div class="message-container">

<?php if ($success): ?>

    <div class="success-box">
        <div class="success-icon">✓</div>
        <h2>Registration Successful!</h2>
        <p>Welcome to ScholarFlow, <?php echo htmlspecialchars($username); ?>!</p>
        <p>Your account has been created successfully.</p>
        <a href="profile.php" class="btn-home">Go to Profile</a>
        <br><br>
        <a href="index.php" style="color: #666; font-size: 14px;">← Back to Homepage</a>
    </div>

<?php else: ?>

    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <h3 style="color: #c62828; margin-top: 0;">Please fix the following:</h3>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <a href="register.php" class="btn-home">← Back to Registration</a>

<?php endif; ?>

</div>

</body>
</html>