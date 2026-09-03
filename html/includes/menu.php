<link rel="stylesheet" href="style.css">
<nav>
    <ul>
        <li><a href="/">Home</a></li>
        <li><a href="groups.php">Groups</a></li>

        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="logout.php">Log out</a></li>
        <?php else: ?>
            <li><a href="login.php">Log in</a></li>
        <?php endif; ?>
        <li><a href="my-groups.php">My Groups</a></li>
    </ul>
</nav>
