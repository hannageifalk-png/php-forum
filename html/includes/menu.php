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
            Forza Football
        </a>

        <button class="menu-toggle" type="button" aria-label="Open menu">
            ☰
        </button>

    </div>

    <ul id="nav-menu" class="nav-links">

        <li>
            <a href="/groups.php">The Stands</a>
        </li>

        <?php if (isset($_SESSION['user_id'])): ?>

            <li>
                <a href="/my-groups.php">My Clubs</a>
            </li>

            <li>
                <a href="/create-group.php">Start a Club</a>
            </li>

           <?php if ($loggedInUser): ?>
            <li class="user-menu">
                <button class="user-menu-button" type="button">
                    <?= htmlspecialchars($loggedInUser['first_name']) ?> ▾
                </button>

                <ul class="user-dropdown">
                    <li>
                        <a href="/logout.php">Log out</a>
                    </li>
                </ul>
            </li>
        <?php endif; ?>

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
    const userMenuButton = document.querySelector('.user-menu-button');
    const userMenu = document.querySelector('.user-menu');
        
        if (userMenuButton && userMenu) {
            userMenuButton.addEventListener('click', function () {
                userMenu.classList.toggle('open');
            });
        }
</script>

