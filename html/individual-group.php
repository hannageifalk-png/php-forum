<?php
session_start();

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

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['approve_request'])
    && $membership
    && $membership['role'] === 'admin'
    ) {
        $requestId = $_POST['request_id'] ?? null;
        
        $requestStmt = $pdo->prepare(
            "SELECT * FROM join_requests
         WHERE id = ? AND group_id = ? AND status = ?"
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
             WHERE user_id = ? AND group_id = ?"
        );
        
        $existingMemberStmt->execute([
            $request['user_id'],
            $request['group_id']
        ]);
        
        $existingMember = $existingMemberStmt->fetch();
        
        if (!$existingMember) {
            $memberStmt = $pdo->prepare(
                "INSERT INTO users_groups (user_id, group_id, role)
                 VALUES (?, ?, ?)"
            );
            
            $memberStmt->execute([
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

        echo '<p>Join request approved!</p>';
    }
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

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['change_role'])
    && $membership
    && $membership['role'] === 'admin'
    ) {
        
        $userId = $_POST['user_id'] ?? null;
        $newRole = $_POST['role'] ?? null;
        
        if ($userId && in_array($newRole, ['member', 'admin'])) {
            $updateStmt = $pdo->prepare(
                "UPDATE users_groups SET role = ? WHERE user_id = ? AND group_id = ?"
            );
            
            $updateStmt->execute([
                $newRole,
                $userId,
                $groupId
            ]);
            
            echo '<p>User role updated!</p>';
        }
    }
    
    require 'includes/menu.php';

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
    }

if ($membership && $membership['role'] === 'admin') {

    $memberStmt = $pdo->prepare(
        "SELECT
            users.id,
            users.first_name,
            users.last_name,
            users_groups.role
         FROM users_groups
         JOIN users ON users.id = users_groups.user_id
         WHERE users_groups.group_id = ?"
    );

    $memberStmt->execute([$groupId]);
    $members = $memberStmt->fetchAll();

    echo '<h2>Group members</h2>';

    foreach ($members as $member) {

        echo '<p>';
        echo htmlspecialchars($member['first_name']) . ' ';
        echo htmlspecialchars($member['last_name']) . ' - ';
        echo htmlspecialchars($member['role']);
        echo '</p>';

        ?>

        <form method="POST">
            <input
                type="hidden"
                name="user_id"
                value="<?= $member['id'] ?>"
            >

            <select name="role">
                <option value="member">Member</option>
                <option value="admin">Admin</option>
            </select>

            <button type="submit" name="change_role">
                Change role
            </button>
        </form>

        <?php
    }

    $requestListStmt = $pdo->prepare(
        "SELECT * FROM join_requests
         WHERE group_id = ? AND status = ?"
    );

    $requestListStmt->execute([
        $groupId,
        'pending'
    ]);

    $joinRequests = $requestListStmt->fetchAll();

    echo '<h2>Join requests</h2>';

    foreach ($joinRequests as $request) {

        echo '<p>User ID: ' . htmlspecialchars($request['user_id']) . '</p>';

        ?>

        <form method="POST">
            <input
                type="hidden"
                name="request_id"
                value="<?= $request['id'] ?>"
            >

            <button type="submit" name="approve_request">
                Approve
            </button>
        </form>

        <?php
    }
} else {
    ?>

    <p>You are not a member of this group.</p>

    <form method="POST">
        <button type="submit" name="join_group">Join group</button>
    </form>

<?php
}