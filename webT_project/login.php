<?php
session_start();

$servername = "127.0.0.1";
$username = "root";
$password = ""; // For database connection only (not the user's password)
$dbname = "picogram";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $hashed_password_from_db = $row["password"]; // Get the hashed password from the database

        if (password_verify($password, $hashed_password_from_db)) { // Correctly verify
            $_SESSION["user_id"] = $row["id"]; // Store user ID in session
            $_SESSION["username"] = $username; // Store username in session
            header("Location: index.php");
            exit();
        } else {
            echo "<script>alert('Incorrect password.'); window.location.href='login.html';</script>";
        }
    } else {
        echo "<script>alert('User  not found.'); window.location.href='login.html';</script>";
    }

    $stmt->close();
}
$conn->close();
?>