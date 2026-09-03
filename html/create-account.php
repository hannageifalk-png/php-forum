<?php
session_start();

require 'includes/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo '<h1>Create account</h1>';
}
?>

<form method="POST">
    <label>First Name:</label>
    <input type="text" id="first_name" name="first_name" required>

    <label>Last Name:</label>
    <input type="text" id="last_name" name="last_name" required>

    <label>Email:</label>
    <input type="email" id="email" name="email" required>

    <label>Password:</label>
    <input type="password" id="password" name="password" required>

    <button type="submit">Create Account</button>
</form>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Hantera formulärinlämning
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validera och skapa kontot
    if ($first_name && $last_name && $email && $password) {
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo '<p>Email is already in use.</p>';
        } else {
            // Skapa kontot (logik för att spara användaren i databasen)
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                 "INSERT INTO users (first_name, last_name, email, password_hash) 
                 VALUES (?, ?, ?, ?)");

            if ($stmt->execute([$first_name, $last_name, $email, $password_hash])) {
                $user_id = $pdo->lastInsertId();
                $_SESSION['user_id'] = $user_id;
                echo '<p>Account created successfully!</p>';
                
            } else {
                echo '<p>Error creating account.</p>';
            }
        }
    } else {
        echo '<p>Please fill in all fields.</p>';
    }
}
?>