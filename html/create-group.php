<?php
require 'includes/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {

    $groupName = $_POST['name'] ?? '';

    if ($groupName) {

        $stmt = $pdo->prepare(
            "INSERT INTO groups (name, created_by) VALUES (?, ?)"
        );

        $stmt->execute([
            $groupName,
            $_SESSION['user_id']
        ]);

        $groupId = $pdo->lastInsertId();

        $memberStmt = $pdo->prepare(
            "INSERT INTO users_groups (user_id, group_id, role)
             VALUES (?, ?, ?)"
        );

        $memberStmt->execute([
            $_SESSION['user_id'],
            $groupId,
            'admin'
        ]);

        header("Location: individual-group.php?id=" . $groupId);
        exit;
    }

    $error = 'Please enter a group name.';
}

require 'includes/header.php';
require 'includes/menu.php';
?>

<div class="create-group-page">

    <div class="create-group-header">
        <span>START A CLUB</span>

        <h1>Build your own corner of football.</h1>

        <p>
            Create a community for your club, league or favourite
            football topic and bring supporters together.
        </p>
    </div>

    <?php if (isset($_SESSION['user_id'])): ?>

        <div class="create-group-card">

            <div class="create-group-icon">⚽</div>

            <h2>Name your club</h2>

            <p>
                Choose a name that tells supporters what your community is about.
            </p>

            <?php if (isset($error)): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <form method="POST">

                <label for="name">Club name</label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    placeholder="e.g. Premier League Fans"
                    required
                >

                <button type="submit">
                    Create my club →
                </button>

            </form>

        </div>

    <?php else: ?>

        <p class="create-group-login">
            You must be logged in to start a club.
        </p>

    <?php endif; ?>

</div>

<?php require 'includes/footer.php'; ?>