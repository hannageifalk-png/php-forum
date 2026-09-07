<?php

$loggedInUser = null;

if (isset($_SESSION['user_id'])) {
    require_once 'database.php';

    $userStmt = $pdo->prepare(
        "SELECT first_name FROM users WHERE id = ?"
    );

    $userStmt->execute([$_SESSION['user_id']]);
    $loggedInUser = $userStmt->fetch();
}

?>

<nav>
    <div class="nav-top">

        <a href="/index.php" class="nav-brand">
            Football Forum
        </a>

        <button class="menu-toggle" type="button" aria-label="Open menu">
            ☰
        </button>

    </div>

    <ul id="nav-menu" class="nav-links">

        <li>
            <a href="/groups.php">Groups</a>
        </li>

        <?php if (isset($_SESSION['user_id'])): ?>

            <li>
                <a href="/my-groups.php">My Groups</a>
            </li>

            <li>
                <a href="/create-group.php">Create Group</a>
            </li>

            <?php if ($loggedInUser): ?>
                <li class="logged-in-user">
                    Welcome, <?= htmlspecialchars($loggedInUser['first_name']) ?>
                </li>
            <?php endif; ?>

            <li>
                <a href="/logout.php">Log out</a>
            </li>

        <?php else: ?>

            <li>
                <a href="/login.php">Log in</a>
            </li>

            <li>
                <a href="/create-account.php">Create account</a>
            </li>

        <?php endif; ?>

    </ul>
</nav>

<link rel="stylesheet" href="/style.css">

<script>
    const menuButton = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('#nav-menu');

    menuButton.addEventListener('click', function () {
        navMenu.classList.toggle('open');
    });
</script>