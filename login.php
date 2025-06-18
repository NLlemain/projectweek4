<?php
require 'config/database.php'; // Zorg ervoor dat dit bestand de databaseverbinding bevat
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Controleer of de gebruiker bestaat
    $stmt = $pdo->prepare("SELECT * FROM user WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Sla gebruikersgegevens op in de sessie
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: index.php');
        exit();
    } else {
        echo "Ongeldige gebruikersnaam of wachtwoord.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://fonts.googleapis.com/css?family=Inter:400,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="container">
        <div class="logo">
            <!-- Simple lightning bolt SVG icon -->
            <svg fill="none" viewBox="0 0 24 24">
                <path d="M13 2L3 14h7v8l9-12h-7V2z" fill="currentColor"/>
            </svg>
        </div>
        <div class="title">Smart Energy</div>
        <div class="subtitle">Dashboard Login</div>
        <div class="login-card">
            <h2>Welcome back</h2>
            <p>Sign in to access your energy dashboard</p>
            <form action="login.php" method="post" autocomplete="off">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-icon">
                        <input type="text" id="username" name="username" placeholder="Enter you username" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-icon">
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                </div>
                <div class="options">
                    <label>
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                </div>
                <a href="#">Forgot Password?</a>
                <button type="submit" class="login-btn">→ Log In</button>
            </form>
            <div class="divider">or</div>
            <div class="signup-link">
                Don't have an account? <a href="registratie.php">Create Account</a>
            </div>
        </div>
    </div>
</body>
</html>