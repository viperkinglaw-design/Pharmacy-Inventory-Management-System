<?php
session_start();
include("connect.php"); 

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? '');
    $password = $_POST["password"] ?? '';

    if ($username === '' || $password === '') {
        $error = "Please enter both username and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, username FROM users WHERE username = ? AND password = ? LIMIT 1");
        
        if ($stmt === false) {
            $error = "Database error. Please try again later.";
        } else {
            $stmt->bind_param("ss", $username, $password);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user) {
                // Login successful
                session_regenerate_id(true); // Security

                $_SESSION["user_id"]   = $user['id'];
                $_SESSION["username"]  = $user['username'];

                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid username or password.";
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pharmacy Inventory Management System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .error { color: #e74c3c; background:#fdf2f2; padding:12px; border-radius:6px; margin:15px 0; text-align:center; font-weight:bold; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login</h2>

        <?php if (!empty($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <label>Username</label>
            <input type="text" name="username" required autofocus  placeholder="Your username">

            <label>Password</label>
            <input type="password" name="password" required placeholder="Your password">

            <button type="submit">Login</button>
        </form>
    </div>
    <script src="script.js"></script>
</body>
</html>