<?php
session_start();

require 'includes/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($first_name && $last_name && $email && $password) {

        $stmt = $pdo->prepare(
            "SELECT * FROM users WHERE email = ?"
        );

        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {

            $error = 'Email is already in use.';

        } else {

            $password_hash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $pdo->prepare(
                "INSERT INTO users
                (first_name, last_name, email, password_hash)
                VALUES (?, ?, ?, ?)"
            );

            if (
                $stmt->execute([
                    $first_name,
                    $last_name,
                    $email,
                    $password_hash
                ])
            ) {

                $user_id = $pdo->lastInsertId();
                $_SESSION['user_id'] = $user_id;

                header("Location: index.php");
                exit;

            } else {

                $error = 'Error creating account.';
            }
        }

    } else {

        $error = 'Please fill in all fields.';
    }
}

require 'includes/header.php';
require 'includes/menu.php';
?>

<div class="auth-page">

    <div class="auth-header">

        <span>JOIN FORZA</span>

        <h1>Create your account.</h1>

        <p>
            Join football communities, start discussions
            and connect with other supporters.
        </p>

    </div>


    <div class="auth-card">

        <h2>Create account</h2>

        <?php if ($error): ?>
            <p class="auth-error">
                <?= htmlspecialchars($error) ?>
            </p>
        <?php endif; ?>

        <form method="POST">

            <label for="first_name">
                First name
            </label>

            <input
                type="text"
                id="first_name"
                name="first_name"
                value="<?= htmlspecialchars($first_name ?? '') ?>"
                placeholder="First name"
                required
            >


            <label for="last_name">
                Last name
            </label>

            <input
                type="text"
                id="last_name"
                name="last_name"
                value="<?= htmlspecialchars($last_name ?? '') ?>"
                placeholder="Last name"
                required
            >


            <label for="email">
                Email
            </label>

            <input
                type="email"
                id="email"
                name="email"
                value="<?= htmlspecialchars($email ?? '') ?>"
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
                placeholder="Choose a password"
                required
            >


            <button type="submit">
                Join Forza →
            </button>

        </form>


        <div class="auth-switch">

            <p>
                Already part of Forza?
                <a href="login.php">
                    Log in
                </a>
            </p>

        </div>

    </div>

</div>

<?php require 'includes/footer.php'; ?>