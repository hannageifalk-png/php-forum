<footer class="site-footer">
    <div class="footer-content">

        <div class="footer-about">
            <h2>Forza Football</h2>
            <p>
                Where football fans come together.<br>
                Talk. Debate. Support.
            </p>
        </div>

        <div class="footer-links">
            <h3>Explore</h3>

            <a href="/groups.php">The Stands</a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/my-groups.php">My Clubs</a>
                <a href="/create-group.php">Start a Club</a>
            <?php else: ?>
                <a href="/login.php">Log in</a>
                <a href="/create-account.php">Join Forza</a>
            <?php endif; ?>
        </div>

    </div>

    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> Forza Football</p>
    </div>
</footer>