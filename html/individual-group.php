<?php
session_start();

require 'includes/menu.php';
require 'includes/database.php';

$groupId = $_GET['id'] ?? null;

$stmt = $pdo->prepare(
    "SELECT * FROM groups WHERE id = ?"
);
$stmt->execute([$groupId]);
$group = $stmt->fetch();

if (!isset($_SESSION['user_id'])) {
    echo '<p>You need to log in to view this group.</p>';
    exit;
}

$memberStmt = $pdo->prepare(
    "SELECT * FROM users_groups WHERE user_id = ? AND group_id = ?"
);

$memberStmt->execute([$_SESSION['user_id'], $groupId]);
$membership = $memberStmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join_group'])) {

    $requestStmt = $pdo->prepare(
        "SELECT * FROM join_requests 
         WHERE user_id = ? AND group_id = ? AND status = ?"
    );

    $requestStmt->execute([
        $_SESSION['user_id'],
        $groupId,
        'pending'
    ]);

    $existingRequest = $requestStmt->fetch();

    if ($existingRequest) {

        echo '<p>You have already sent a join request for this group.</p>';

    } else {

        $joinStmt = $pdo->prepare(
            "INSERT INTO join_requests (user_id, group_id, status)
             VALUES (?, ?, ?)"
        );

        $joinStmt->execute([
            $_SESSION['user_id'],
            $groupId,
            'pending'
        ]);

        echo '<p>Join request sent!</p>';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_request']) && $membership && $membership['role'] === 'admin') {

    $requestId = $_POST['request_id'] ?? null;

    $requestStmt = $pdo->prepare(
        "SELECT * FROM join_requests WHERE id = ?"
    );

    $requestStmt->execute([$requestId]);
    $request = $requestStmt->fetch();

    $memberStmt = $pdo->prepare(
        "INSERT INTO users_groups (user_id, group_id, role) VALUES (?, ?, ?)"
    );

    $memberStmt->execute([
        $request['user_id'],
        $request['group_id'],
        'member'
    ]);

    $updateStmt = $pdo->prepare(
        "UPDATE join_requests SET status = ? WHERE id = ?"
    );

    $updateStmt->execute([
        'approved',
        $requestId
    ]);

    echo '<p>Join request approved!</p>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_post']) && $membership) {

    $subject = $_POST['subject'] ?? '';
    $content = $_POST['content'] ?? '';

    $discussionsStmt = $pdo->prepare(
        "INSERT INTO discussions (group_id, user_id, subject)
         VALUES (?, ?, ?)"
    );

    $discussionsStmt->execute([
        $groupId,
        $_SESSION['user_id'],
        $subject
    ]);

    $discussionId = $pdo->lastInsertId();
    $postsStmt = $pdo->prepare(
        "INSERT INTO posts (user_id, discussion_id, content) 
         VALUES (?, ?, ?)"
    );

    $postsStmt->execute([
        $_SESSION['user_id'],
        $discussionId,
        $content
    ]);
}


if (!$group) {

    echo '<p>Group not found.</p>';

} elseif ($membership) {

    echo '<h1>' . htmlspecialchars($group['name']) . '</h1>';
    echo '<p>ID: ' . $group['id'] . '</p>';
?>
    <h2>Create post</h2>

    <form method="POST">
        <label for="subject">Subject:</label>
        <input type="text" id="subject" name="subject" required>

        <label for="content">Post:</label>
        <textarea id="content" name="content" required></textarea>

        <button type="submit" name="create_post">Create post</button>
    </form>

<?php

$discussionStmt = $pdo->prepare(
    "SELECT * FROM discussions
     WHERE group_id = ?"
);

$discussionStmt->execute([$groupId]);
$discussions = $discussionStmt->fetchAll();

foreach ($discussions as $discussion) {
    echo '<a href="discussion.php?id=' . $discussion['id'] . '">';
    echo '<h3>' . htmlspecialchars($discussion['subject']) . '</h3>';
    echo '</a>';
}


if ($membership && $membership['role'] === 'admin') {

$requestListStmt = $pdo->prepare(
    "SELECT * FROM join_requests WHERE group_id = ? AND status = ?"
    );

$requestListStmt->execute([$groupId, 'pending']);
$joinRequests = $requestListStmt->fetchAll();

    foreach ($joinRequests as $request) {
        echo '<p>User ID: ' . $request['user_id'] . '</p>';
        ?>

        <form method="POST">
            <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
            <button type="submit" name="approve_request">Approve</button>
        </form>

        <?php
    }
}

} else {
    ?>

        <p>You are not a member of this group.</p>

        <form method="POST">
            <button type="submit" name="join_group">Join group</button>
        </form>

        <?php
    }