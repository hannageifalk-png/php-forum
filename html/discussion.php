<?php
session_start();

require 'includes/database.php';

$discussionId = $_GET['id'] ?? null;

if (!$discussionId) {
    echo '<p>Discussion not found.</p>';
    exit;
}

// Hämta diskussionen
$stmt = $pdo->prepare(
    "SELECT * FROM discussions WHERE id = ?"
);
$stmt->execute([$discussionId]);
$discussion = $stmt->fetch();

if (!$discussion) {
    echo '<p>Discussion not found.</p>';
    exit;
}

// Hantera svar innan något HTML-innehåll skrivs ut
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply'])) {

    $content = $_POST['content'] ?? '';
    $userId = $_SESSION['user_id'] ?? null;

    if ($content && $userId) {

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

// Menyn kommer först efter all kod som kan använda header()
require 'includes/menu.php';

// Visa diskussionen
echo '<h1>' . htmlspecialchars($discussion['subject']) . '</h1>';
echo '<p>Created by User ID: ' . $discussion['user_id'] . '</p>';
echo '<h2>Posts</h2>';

// Hämta alla posts/svar
$postsStmt = $pdo->prepare(
    "SELECT * FROM posts WHERE discussion_id = ?"
);
$postsStmt->execute([$discussionId]);
$posts = $postsStmt->fetchAll();

// Visa alla posts
foreach ($posts as $post) {
    echo '<div>';
    echo '<p>' . htmlspecialchars($post['content']) . '</p>';
    echo '<p>Posted by User ID: ' . $post['user_id'] . '</p>';
    echo '</div>';
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