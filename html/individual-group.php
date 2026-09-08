<?php
session_start();

require_once 'includes/database.php';
require_once 'includes/functions.php';

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
    "SELECT * FROM users_groups
     WHERE user_id = ? AND group_id = ?"
);

$memberStmt->execute([
    $_SESSION['user_id'],
    $groupId
]);

$membership = $memberStmt->fetch();


/* JOIN GROUP */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['join_group'])
) {

    $requestStmt = $pdo->prepare(
        "SELECT * FROM join_requests
         WHERE user_id = ?
         AND group_id = ?
         AND status = ?"
    );

    $requestStmt->execute([
        $_SESSION['user_id'],
        $groupId,
        'pending'
    ]);

    $existingRequest = $requestStmt->fetch();

    if ($existingRequest) {

        $message = 'You have already sent a join request for this group.';

    } else {

        $joinStmt = $pdo->prepare(
            "INSERT INTO join_requests
             (user_id, group_id, status)
             VALUES (?, ?, ?)"
        );

        $joinStmt->execute([
            $_SESSION['user_id'],
            $groupId,
            'pending'
        ]);

        $message = 'Join request sent!';
    }
}


/* APPROVE JOIN REQUEST */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['approve_request'])
    && $membership
    && $membership['role'] === 'admin'
) {

    $requestId = $_POST['request_id'] ?? null;

    $requestStmt = $pdo->prepare(
        "SELECT * FROM join_requests
         WHERE id = ?
         AND group_id = ?
         AND status = ?"
    );

    $requestStmt->execute([
        $requestId,
        $groupId,
        'pending'
    ]);

    $request = $requestStmt->fetch();

    if ($request) {

        $existingMemberStmt = $pdo->prepare(
            "SELECT * FROM users_groups
             WHERE user_id = ?
             AND group_id = ?"
        );

        $existingMemberStmt->execute([
            $request['user_id'],
            $request['group_id']
        ]);

        $existingMember = $existingMemberStmt->fetch();

        if (!$existingMember) {

            $newMemberStmt = $pdo->prepare(
                "INSERT INTO users_groups
                 (user_id, group_id, role)
                 VALUES (?, ?, ?)"
            );

            $newMemberStmt->execute([
                $request['user_id'],
                $request['group_id'],
                'member'
            ]);
        }

        $updateStmt = $pdo->prepare(
            "UPDATE join_requests
             SET status = ?
             WHERE id = ?"
        );

        $updateStmt->execute([
            'approved',
            $requestId
        ]);

        $message = 'Join request approved!';
    }
}


/* CREATE DISCUSSION */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['create_post'])
    && $membership
) {

    $subject = trim($_POST['subject'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($subject && $content) {

        $discussionsStmt = $pdo->prepare(
            "INSERT INTO discussions
             (group_id, user_id, subject)
             VALUES (?, ?, ?)"
        );

        $discussionsStmt->execute([
            $groupId,
            $_SESSION['user_id'],
            $subject
        ]);

        $discussionId = $pdo->lastInsertId();

        $postsStmt = $pdo->prepare(
            "INSERT INTO posts
             (user_id, discussion_id, content)
             VALUES (?, ?, ?)"
        );

        $postsStmt->execute([
            $_SESSION['user_id'],
            $discussionId,
            $content
        ]);
    }
}


/* CHANGE MEMBER ROLE */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['change_role'])
    && $membership
    && $membership['role'] === 'admin'
) {

    $userId = $_POST['user_id'] ?? null;
    $newRole = $_POST['role'] ?? null;

    if (
        $userId
        && $userId != $_SESSION['user_id']
        && in_array($newRole, ['member', 'admin'])
    ) {

        $updateStmt = $pdo->prepare(
            "UPDATE users_groups
             SET role = ?
             WHERE user_id = ?
             AND group_id = ?"
        );

        $updateStmt->execute([
            $newRole,
            $userId,
            $groupId
        ]);

        $message = 'User role updated!';
    }
}


/* CREATE INVITATION */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['create_invitation'])
    && $membership
    && $membership['role'] === 'admin'
) {

    $token = bin2hex(random_bytes(32));

    $expiresAt = date(
        'Y-m-d H:i:s',
        strtotime('+24 hours')
    );

    $invitationStmt = $pdo->prepare(
        "INSERT INTO group_invitations
         (group_id, created_by, token, expires_at)
         VALUES (?, ?, ?, ?)"
    );

    $invitationStmt->execute([
        $groupId,
        $_SESSION['user_id'],
        $token,
        $expiresAt
    ]);

    $invitationLink =
    'http://localhost:8080/join-group.php?token=' .
    urlencode($token);
}


/* LOAD PAGE DATA */

$discussionStmt = $pdo->prepare(
    "SELECT * FROM discussions
     WHERE group_id = ?
     ORDER BY created_at DESC"
);

$discussionStmt->execute([$groupId]);
$discussions = $discussionStmt->fetchAll();


$members = [];
$joinRequests = [];

if ($membership && $membership['role'] === 'admin') {

    $memberListStmt = $pdo->prepare(
        "SELECT
            users.id,
            users.first_name,
            users.last_name,
            users.email,
            users_groups.role
         FROM users_groups
         JOIN users
         ON users.id = users_groups.user_id
         WHERE users_groups.group_id = ?"
    );

    $memberListStmt->execute([$groupId]);
    $members = $memberListStmt->fetchAll();


    $requestListStmt = $pdo->prepare(
        "SELECT
            join_requests.id,
            join_requests.user_id,
            users.first_name,
            users.last_name
         FROM join_requests
         JOIN users
         ON users.id = join_requests.user_id
         WHERE join_requests.group_id = ?
         AND join_requests.status = ?"
    );

    $requestListStmt->execute([
        $groupId,
        'pending'
    ]);

    $joinRequests = $requestListStmt->fetchAll();
}


require 'includes/header.php';
require 'includes/menu.php';
?>


<?php if (isset($message)): ?>

    <p class="page-message">
        <?= htmlspecialchars($message) ?>
    </p>

<?php endif; ?>


<?php if (!$group): ?>

    <p>Group not found.</p>


<?php elseif ($membership): ?>


    <div class="group-page-header">

        <span>CLUBHOUSE</span>

        <h1>
            <?= htmlspecialchars($group['name']) ?>
        </h1>

        <p>
            Talk football, start a discussion and join the conversation.
        </p>

    </div>


    <div class="create-post-box">

        <h2>Start a discussion</h2>

        <form method="POST">

            <label for="subject">
                Subject
            </label>

            <input
                type="text"
                id="subject"
                name="subject"
                placeholder="e.g. Who should start this weekend?"
                required
            >

            <label for="content">
                Your post
            </label>

            <textarea
                id="content"
                name="content"
                placeholder="Share your thoughts..."
                required
            ></textarea>

            <button
                type="submit"
                name="create_post"
            >
                Start discussion →
            </button>

        </form>

    </div>


    <div class="discussions-section">

        <div class="discussions-heading">

            <span class="section-label">
                MATCHDAY TALK
            </span>

            <h2>Latest discussions</h2>

        </div>


        <?php if ($discussions): ?>

            <?php foreach ($discussions as $discussion): ?>

                <div class="discussion-card">

                    <a href="discussion.php?id=<?= $discussion['id'] ?>">

                        <h3>
                            <?= htmlspecialchars($discussion['subject']) ?>
                        </h3>

                        <span>
                            Join discussion →
                        </span>

                    </a>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <p class="empty-message">
                No discussions yet. Start the first one!
            </p>

        <?php endif; ?>

    </div>


    <?php if ($membership['role'] === 'admin'): ?>

        <div class="club-management">

            <span class="section-label">
                CLUB MANAGEMENT
            </span>

            <h2>Manage club</h2>


            <div class="management-grid">


                <div class="management-card">

                    <h3>Members</h3>


                    <?php foreach ($members as $member): ?>

                        <div class="member-row">

                            <div class="member-info">

                                <img
                                    class="avatar"
                                    src="<?= getGravatarUrl($member['email']) ?>"
                                    alt="Profile picture"
                                >

                                <div>

                                    <p>
                                        <?= htmlspecialchars($member['first_name']) ?>
                                        <?= htmlspecialchars($member['last_name']) ?>
                                    </p>

                                    <span class="member-role">
                                        <?= htmlspecialchars(
                                            ucfirst($member['role'])
                                        ) ?>
                                    </span>

                                </div>

                            </div>


                            <?php if ($member['id'] != $_SESSION['user_id']): ?>

                                <form method="POST">

                                    <input
                                        type="hidden"
                                        name="user_id"
                                        value="<?= $member['id'] ?>"
                                    >

                                    <select name="role">

                                        <option
                                            value="member"
                                            <?= $member['role'] === 'member'
                                                ? 'selected'
                                                : '' ?>
                                        >
                                            Member
                                        </option>

                                        <option
                                            value="admin"
                                            <?= $member['role'] === 'admin'
                                                ? 'selected'
                                                : '' ?>
                                        >
                                            Admin
                                        </option>

                                    </select>

                                    <button
                                        type="submit"
                                        name="change_role"
                                    >
                                        Save
                                    </button>

                                </form>

                            <?php endif; ?>

                        </div>

                    <?php endforeach; ?>

                </div>


                <div class="management-card">

                    <h3>Requests</h3>


                    <?php if ($joinRequests): ?>

                        <?php foreach ($joinRequests as $request): ?>

                            <div class="request-row">

                                <p>
                                    <?= htmlspecialchars($request['first_name']) ?>
                                    <?= htmlspecialchars($request['last_name']) ?>
                                </p>

                                <form method="POST">

                                    <input
                                        type="hidden"
                                        name="request_id"
                                        value="<?= $request['id'] ?>"
                                    >

                                    <button
                                        type="submit"
                                        name="approve_request"
                                    >
                                        Approve
                                    </button>

                                </form>

                            </div>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <p class="empty-message">
                            No pending requests.
                        </p>

                    <?php endif; ?>

                </div>


                <div class="management-card invite-card">

                    <h3>Invite</h3>

                    <p>
                        Create a one-time invitation link valid for 24 hours.
                    </p>

                    <form method="POST">

                        <button
                            type="submit"
                            name="create_invitation"
                        >
                            Create invite link
                        </button>

                    </form>


                   <?php if (isset($invitationLink)): ?>

                        <div class="invitation-result">

                            <p>Invitation link created:</p>

                            <input
                                type="text"
                                value="<?= htmlspecialchars($invitationLink) ?>"
                                readonly
                                onclick="this.select()"
                            >

                            <a
                                href="<?= htmlspecialchars($invitationLink) ?>"
                                class="invite-preview-link"
                            >
                                Open invitation →
                            </a>

                        </div>

                    <?php endif; ?>

                </div>


            </div>

        </div>

    <?php endif; ?>


<?php else: ?>


    <div class="group-page-header">

        <span>CLUBHOUSE</span>

        <h1>
            <?= htmlspecialchars($group['name']) ?>
        </h1>

        <p>
            You are not a member of this club yet.
        </p>

    </div>


    <div class="join-club-box">

        <p>
            Send a request to join the club and take part in the discussions.
        </p>

        <form method="POST">

            <button
                type="submit"
                name="join_group"
            >
                Request to join →
            </button>

        </form>

    </div>


<?php endif; ?>


<?php require 'includes/footer.php'; ?>