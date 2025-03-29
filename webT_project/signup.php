<?php
session_start();
$host = '127.0.0.1'; // Database host
$db = 'picogram'; // Database name
$user = 'root'; // Database username
$pass = ''; // Database password

// Create connection
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle signup form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $hashed_password);

    if ($stmt->execute()) {
        echo "<script>alert('Signup successful! You can now log in.'); window.location.href='login.html';</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .signup-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .error {
            color: red;
            font-size: 0.9em;
        }
    </style>
</head>
<body>

<div class="signup-container">
    <h2 class="text-center">Signup</h2>
    <form id="signupForm" method="POST" onsubmit="return validateForm()">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" class="form-control" id="username" name="username" required>
            <div class="error" id="usernameError"></div>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
            <div class="error" id="passwordError"></div>
        </div>
        <div class="form-group">
            <label for="confirmPassword">Confirm Password</label>
            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
            <div class="error" id="confirmPasswordError"></div>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Signup</button>
    </form>
    <p class="text-center mt-3">Already have an account? <a href="login.html">Login here</a></p>
</div>

<script>
    function validateForm() {
        let isValid = true;

        // Clear previous error messages
        document.getElementById('usernameError').innerText = '';
        document.getElementById('passwordError').innerText = '';
        document.getElementById('confirmPasswordError').innerText = '';

        // Validate username
        const username = document.getElementById('username').value;
        if (username.length < 3) {
            document.getElementById('usernameError').innerText = 'Username must be at least 3 characters long.';
            isValid = false;
        }

        // Validate password
        const password = document.getElementById('password').value;
        if (password.length < 6) {
            document.getElementById('passwordError').innerText = 'Password must be at least 6 characters long.';
            isValid = false;
        }

        // Validate confirm password
        const confirmPassword = document.getElementById('confirmPassword').value;
        if (password !== confirmPassword) {
            document.getElementById('confirmPasswordError').innerText = 'Passwords do not match.';
            isValid = false;
        }

        return isValid;
    }
</script>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>