<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: ../{$_SESSION['role']}/{$_SESSION['role']}_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Barangay Calauag | Login</title>
</head>

<body>

    <h2>Barangay Calauag Educational System</h2>

    <form method="POST" action="process_login.php">
        <input type="text" name="username" placeholder="Username or Email" required maxlength="50"><br><br>

        <input type="password" name="password" id="password" placeholder="Password" required minlength="6"><br><br>

        <input type="checkbox" onclick="togglePassword()"> Show Password<br><br>

        <button type="submit">Login</button>
    </form>

    <p><small>For residents and barangay staff of Brgy. Calauag (Educational System)</small></p>

    <a href="../index.php">Back to Home</a>

    <script src="../assets/js/auth/password_toggle.js"></script>

</body>

</html>