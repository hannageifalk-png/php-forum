<?php

session_start();

require_once 'includes/database.php';

// kontrollera inloggning
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// hämta token från URL
$token = $_GET['token'] ?? null;
if (!$token) {
    echo '<p>Invalid invitation link.</p>';
    exit;
}

$stmt = $pdo->prepare(
    "SELECT * FROM group_invitations 
    WHERE token = ?
    AND expires_at > NOW()
    AND used_at IS NULL
");

$stmt->execute([$token]);
$invitation = $stmt->fetch();

if (!$invitation) {
    echo '<p>Invalid invitation link.</p>';
    exit;
}

$groupId = $invitation['group_id'];

$memberStmt = $pdo->prepare(
    "SELECT * FROM users_groups
     WHERE user_id = ? AND group_id = ?"
);

$memberStmt->execute([
    $_SESSION['user_id'],
    $groupId
]);

$membership = $memberStmt->fetch();

if ($membership) {
    echo '<p>You are already a member of this group.</p>';
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        "INSERT INTO users_groups (user_id, group_id, role)
         VALUES (?, ?, ?)"
    );

    $stmt->execute([
        $_SESSION['user_id'],
        $groupId,
        'member'
    ]);

    $updateStmt = $pdo->prepare(
        "UPDATE group_invitations
         SET used_at = NOW()
         WHERE id = ?"
    );

    $updateStmt->execute([
        $invitation['id']
    ]);

    $pdo->commit();

    echo '<p>You have successfully joined the group.</p>';

} catch (Exception $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo '<p>Error joining group: ' . htmlspecialchars($e->getMessage()) . '</p>';
}