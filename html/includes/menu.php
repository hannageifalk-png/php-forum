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
    <ul>
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

            <li>
                <a href="/logout.php">Log out</a>
            </li>

            <?php if ($loggedInUser): ?>
                <li class="logged-in-user">
                    Välkommen, <?= htmlspecialchars($loggedInUser['first_name']) ?>
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