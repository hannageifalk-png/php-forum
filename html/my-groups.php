<?php
session_start();

require 'includes/database.php';
require 'includes/header.php';
require 'includes/menu.php';

if (!isset($_SESSION['user_id'])) {
    echo '<p>You need to log in to view your groups.</p>';
    exit;
}

$stmt = $pdo->prepare(
    "SELECT `groups`.*
     FROM `groups`
     JOIN users_groups ON `groups`.id = users_groups.group_id
     WHERE users_groups.user_id = ?"
);
$stmt->execute([$_SESSION['user_id']]);
$groups = $stmt->fetchAll();

?>
<div class="my-groups-header">
    <span>MY CLUBS</span>
    <h1>Your football. Your communities.</h1>
    <p>
        Jump back into the conversations and communities you follow.
    </p>
</div>

<div class="my-groups-list">

<?php if ($groups): ?>

    <?php foreach ($groups as $group): ?>

        <div class="my-group-card">

            <div class="my-group-icon">
                ⚽
            </div>

            <div class="my-group-content">

                <h2>
                    <?= htmlspecialchars($group['name']) ?>
                </h2>

                <a href="individual-group.php?id=<?= $group['id'] ?>">
                    Enter club →
                </a>
            </div>

        </div>

    <?php endforeach; ?>

<?php else: ?>

    <p>You are not a member of any clubs yet.</p>

<?php endif; ?>

</div>

<?php require 'includes/footer.php'; ?>