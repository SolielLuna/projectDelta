<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="style.css">

    <script>
        function validateForm() {
            let name = document.forms["regForm"]["fullname"].value;
            let email = document.forms["regForm"]["email"].value;
            let password = document.forms["regForm"]["password"].value;

            if (name == "" || email == "" || password == "") {
                alert("All fields are required!");
                return false;
            }

            if (password.length < 6) {
                alert("Password must be at least 6 characters!");
                return false;
            }

            return true;
        }
    </script>
</head>
<body>

<div class="container">
    <h2>Register</h2>

    <form name="regForm" action="process_register.php" method="POST" onsubmit="return validateForm()">
        
        <label>Full Name</label>
        <input type="text" name="fullname">

        <label>Email</label>
        <input type="email" name="email">

        <label>Password</label>
        <input type="password" name="password">

        <button type="submit">Register</button>
    </form>

</div>

</body>
</html>