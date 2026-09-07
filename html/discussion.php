<?php
session_start();

require_once 'includes/database.php';
require_once 'includes/functions.php';

$discussionId = $_GET['id'] ?? null;

if (!$discussionId) {
    echo '<p>Discussion not found.</p>';
    exit;
}

$stmt = $pdo->prepare(
    "SELECT * FROM discussions WHERE id = ?"
);
$stmt->execute([$discussionId]);
$discussion = $stmt->fetch();

if (!$discussion) {
    echo '<p>Discussion not found.</p>';
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$memberStmt = $pdo->prepare(
    "SELECT * FROM users_groups WHERE user_id = ? AND group_id = ?"
);

$memberStmt->execute([$_SESSION['user_id'], $discussion['group_id']]);
$membership = $memberStmt->fetch();

if (!$membership) {
    header("Location: individual-group.php?id=" . $discussion['group_id']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply'])) {

    $content = $_POST['content'] ?? '';
    $userId = $_SESSION['user_id'];

    if ($content) {

        $replyStmt = $pdo->prepare(
            "INSERT INTO posts (discussion_id, user_id, content)
             VALUES (?, ?, ?)"
        );

        $replyStmt->execute([
            $discussionId,
            $userId,
            $content
        ]);

        header("Location: discussion.php?id=" . $discussionId);
        exit;
    }
}

require 'includes/menu.php';

echo '<h1>' . htmlspecialchars($discussion['subject']) . '</h1>';
echo '<p>Created by User ID: ' . $discussion['user_id'] . '</p>';
echo '<h2>Posts</h2>';

$postsStmt = $pdo->prepare(
    "SELECT 
    posts.*,
    users.first_name,
    users.last_name,
    users.email
    FROM posts
    JOIN users ON posts.user_id = users.id
    WHERE posts.discussion_id = ?"
);
$postsStmt->execute([$discussionId]);
$posts = $postsStmt->fetchAll();

foreach ($posts as $post) {
    ?>
    <div class="member-info">
    <?php
        $gravatarUrl = getGravatarUrl($post['email']);
        echo '<div class="post-card">';
        echo '<img class="avatar" src="' . $gravatarUrl . '" alt="Profile picture">';
        echo '<p class="post-author">Posted by: ' . htmlspecialchars($post['first_name']) . ' ' . htmlspecialchars($post['last_name']) . '</p>';
        echo '<p>' . htmlspecialchars($post['content']) . '</p>';
        echo '</div>';
    ?>
    </div>
    <?php
}

?>

<h2>Reply</h2>

<form method="POST">
    <textarea
        name="content"
        id="content"
        placeholder="Write your reply..."
        required
    ></textarea>

    <button type="submit" name="reply">Reply</button>
</form>