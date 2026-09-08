<?php
session_start();

require 'includes/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare(
        "SELECT * FROM users WHERE email = ?"
    );

    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (
        $user &&
        password_verify($password, $user['password_hash'])
    ) {
        $_SESSION['user_id'] = $user['id'];

        header("Location: index.php");
        exit;
    }

    $error = 'Invalid email or password.';
}

require 'includes/header.php';
require 'includes/menu.php';
?>

<div class="auth-page">

    <div class="auth-header">
        <span>WELCOME BACK</span>

        <h1>Log in to Forza Football.</h1>

        <p>
            Jump back into your clubs and football discussions.
        </p>
    </div>


    <div class="auth-card">

        <h2>Log in</h2>

        <?php if ($error): ?>
            <p class="auth-error">
                <?= htmlspecialchars($error) ?>
            </p>
        <?php endif; ?>

        <form method="POST">

            <label for="email">
                Email
            </label>

            <input
                type="email"
                id="email"
                name="email"
                placeholder="you@example.com"
                required
            >

            <label for="password">
                Password
            </label>

            <input
                type="password"
                id="password"
                name="password"
                placeholder="Password"
                required
            >

            <button type="submit">
                Log in →
            </button>

        </form>

        <div class="auth-switch">
            <p>
                New to Forza?
                <a href="create-account.php">
                    Create an account
                </a>
            </p>
        </div>

    </div>

</div>

<?php require 'includes/footer.php'; ?>