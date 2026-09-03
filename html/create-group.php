<?php
require 'includes/database.php';
session_start();

if (isset($_SESSION['user_id'])) {

    // GET → visa formuläret
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        ?>
        
        <form method="POST">
            <label for="name">Group name:</label>
            <input type="text" id="name" name="name" required>

            <button type="submit">Create group</button>
        </form>

        <?php
    }

    // POST → ta emot formuläret
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $groupName = $_POST['name'] ?? '';

        if ($groupName) {
            $stmt = $pdo->prepare(
                "INSERT INTO groups (name, created_by) VALUES (?, ?)"
            );
            $stmt->execute([$groupName, $_SESSION['user_id']]);
            $groupId = $pdo->lastInsertId();
            header("Location: individual-group.php?id=" . $groupId);
            exit;

            $memberStmt = $pdo->prepare(
                "INSERT INTO users_groups (user_id, group_id, role) VALUES (?, ?, ?)"
            );

            $memberStmt->execute([
                $_SESSION['user_id'],
                $groupId,
                'admin'
            ]);
            echo '<p>Group "' . htmlspecialchars($groupName) . '" created successfully!</p>';
        } else {
            echo '<p>Please enter a group name.</p>';
        }
    }

} else {
    echo '<p>You must be logged in to create a group.</p>';
}
?>