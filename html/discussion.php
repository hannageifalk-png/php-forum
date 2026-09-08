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
    "SELECT * FROM users_groups
     WHERE user_id = ? AND group_id = ?"
);

$memberStmt->execute([
    $_SESSION['user_id'],
    $discussion['group_id']
]);

$membership = $memberStmt->fetch();

if (!$membership) {
    header(
        "Location: individual-group.php?id=" .
        $discussion['group_id']
    );
    exit;
}


/* CREATE REPLY */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['reply'])
) {

    $content = trim($_POST['content'] ?? '');
    $userId = $_SESSION['user_id'];

    if ($content) {

        $replyStmt = $pdo->prepare(
            "INSERT INTO posts
             (discussion_id, user_id, content)
             VALUES (?, ?, ?)"
        );

        $replyStmt->execute([
            $discussionId,
            $userId,
            $content
        ]);

        header(
            "Location: discussion.php?id=" .
            $discussionId
        );

        exit;
    }
}


/* GET POSTS */

$postsStmt = $pdo->prepare(
    "SELECT
        posts.*,
        users.first_name,
        users.last_name,
        users.email
     FROM posts
     JOIN users
     ON posts.user_id = users.id
     WHERE posts.discussion_id = ?
     ORDER BY posts.created_at ASC"
);

$postsStmt->execute([$discussionId]);
$posts = $postsStmt->fetchAll();


require 'includes/header.php';
require 'includes/menu.php';
?>


<div class="discussion-page-header">

    <span>MATCHDAY TALK</span>

    <h1>
        <?= htmlspecialchars($discussion['subject']) ?>
    </h1>

    <a
        href="individual-group.php?id=<?= $discussion['group_id'] ?>"
        class="back-to-club"
    >
        ← Back to club
    </a>

</div>


<div class="discussion-thread">

    <?php foreach ($posts as $post): ?>

        <div class="post-card">

            <div class="post-author">

                <img
                    class="avatar"
                    src="<?= getGravatarUrl($post['email']) ?>"
                    alt="Profile picture"
                >

                <div>
                    <strong>
                        <?= htmlspecialchars($post['first_name']) ?>
                        <?= htmlspecialchars($post['last_name']) ?>
                    </strong>

                    <?php if (!empty($post['created_at'])): ?>
                        <span class="post-date">
                            <?= htmlspecialchars($post['created_at']) ?>
                        </span>
                    <?php endif; ?>
                </div>

            </div>

            <div class="post-content">
                <?= nl2br(htmlspecialchars($post['content'])) ?>
            </div>

        </div>

    <?php endforeach; ?>

</div>


<div class="reply-box">

    <span class="section-label">
        YOUR TURN
    </span>

    <h2>Join the discussion</h2>

    <form method="POST">

        <label for="content">
            Reply
        </label>

        <textarea
            name="content"
            id="content"
            placeholder="Share your thoughts..."
            required
        ></textarea>

        <button type="submit" name="reply">
            Post reply →
        </button>

    </form>

</div>


<?php require 'includes/footer.php'; ?>