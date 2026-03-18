<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Scholarship System</title>
    <link rel="stylesheet" href="style.css">
    <style>
      body {
    margin: 0;
    height: 100vh;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    
    background: url('uploads/5.png') center/cover no-repeat;
}

.container {
    width: 100%;
    max-width: 500px;
    margin-right: 190px; 
    
    background: #fff;
    padding: 40px;
    border-radius: 25px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}
.container h2 {
    font-size: 38px;
    font-weight: 700;
    text-align: center;
    margin-bottom: 25px;
    color: #771111;
}
button {
    width: 100%;
    padding: 10px;
    background: #771111;
    color: white;
    border: none;
}
button:hover {
    background-color: #ddaa02;
    cursor: pointer; 
    transition: background-color 0.3s ease;
}
.form-footer a {
    color: #771111; 
    text-decoration: none; 
    font-weight: bold; 
}

.form-footer a:hover {
    color: #ddaa02; 
    text-decoration: underline; 
}
    </style>


<script>
function validateForm(e) {

    const name = document.getElementById("username").value;
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confirm_password").value;

    if (name === "" || email === "" || password === "" || confirmPassword === "") {
        showToast("All fields are required!", "error");
        e.preventDefault();
        return false;
    }

    if (password.length < 6) {
        showToast("Password must be at least 6 characters!", "error");
        e.preventDefault();
        return false;
    }

    if (password !== confirmPassword) {
        showToast("Passwords do not match!", "error");
        e.preventDefault();
        return false;
    }

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!emailPattern.test(email)) {
        document.getElementById("emailError").classList.add("show");
        e.preventDefault();
        return false;
    }

    return true;
}
</script>

</head>
<body>

<div class="container">

<h2>Create Account</h2>

<form id="regForm" action="process_register.php" method="POST">

<div class="form-group">
<label>User Name</label>
<input type="text" name="username" id="username" placeholder="Enter your user name">
</div>

<div class="form-group">
<label>Email</label>
<input type="email" name="email" id="email" placeholder="your@email.com">
<span class="error-message" id="emailError">Please enter a valid email</span>
</div>

<div class="form-group">
<label>Password</label>

<div class="password-wrapper">
<input type="password" name="password" id="password" placeholder="Create password">

<button type="button" class="toggle-password" onclick="togglePassword()">
<svg id="eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
</svg>
</button>
</div>

</div>

<div class="form-group">
    <label>Confirm Password</label>

    <div class="password-wrapper">
        <input type="password" id="confirm_password" placeholder="Confirm password">

        <button type="button" class="toggle-password" onclick="toggleConfirmPassword()">
            <svg id="eye-icon-confirm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7
                -1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
        </button>
    </div>
</div>

<button type="submit">Register</button>

</form>

<div class="form-footer">
Already have an account? <a href="login.php">Login</a>
</div>

</div>

<script>

/* PASSWORD TOGGLE */

function togglePassword() {

const field = document.getElementById("password");
const icon = document.getElementById("eye-icon");

if (field.type === "password") {

field.type = "text";

icon.innerHTML = `
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7
a9.97 9.97 0 011.563-3.029m5.858.908
a3 3 0 114.243 4.243M9.878 9.878l4.242
4.242M9.88 9.88l-3.29-3.29m7.532
7.532l3.29 3.29M3 3l3.59 3.59"/>
`;

}

else {

field.type = "password";

icon.innerHTML = `
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
d="M2.458 12C3.732 7.943 7.523 5
12 5c4.478 0 8.268 2.943
9.542 7-1.274 4.057-5.064
7-9.542 7-4.477
0-8.268-2.943-9.542-7z"/>
`;

}
}

function toggleConfirmPassword() {

    const field = document.getElementById("confirm_password");
    const icon = document.getElementById("eye-icon-confirm");

    if (field.type === "password") {
        field.type = "text";
        icon.innerHTML = `
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7
a9.97 9.97 0 011.563-3.029m5.858.908
a3 3 0 114.243 4.243M9.878 9.878l4.242
4.242M9.88 9.88l-3.29-3.29m7.532
7.532l3.29 3.29M3 3l3.59 3.59"/>
`; // open eye SVG
    } else {
        field.type = "password";
        icon.innerHTML =  `
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
d="M2.458 12C3.732 7.943 7.523 5
12 5c4.478 0 8.268 2.943
9.542 7-1.274 4.057-5.064
7-9.542 7-4.477
0-8.268-2.943-9.542-7z"/>
`; // closed eye SVG
    }
}




/* FORM VALIDATION */

document.getElementById("regForm").addEventListener("submit", validateForm);


/* TOAST MESSAGE */

function showToast(message, type="error") {

const existing = document.querySelector(".toast");
if(existing) existing.remove();

const toast = document.createElement("div");

toast.className = "toast " + type;

toast.innerHTML = `
<span class="toast-icon">${type === "success" ? "✅" : "❌"}</span>
<div class="toast-content">
<h4>${type === "success" ? "Success" : "Error"}</h4>
<p>${message}</p>
</div>
`;

document.body.appendChild(toast);

setTimeout(() => {

toast.style.opacity = "0";
toast.style.transform = "translateX(100%)";

setTimeout(() => toast.remove(),300);

},5000);

}

</script>

</body>
</html>