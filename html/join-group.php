<?php

session_start();

require_once 'includes/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$token = $_GET['token'] ?? null;

$message = '';
$success = false;
$groupId = null;

if (!$token) {

    $message = 'This invitation link is invalid.';

} else {

    $stmt = $pdo->prepare(
        "SELECT * FROM group_invitations
         WHERE token = ?
         AND expires_at > NOW()
         AND used_at IS NULL"
    );

    $stmt->execute([$token]);
    $invitation = $stmt->fetch();

    if (!$invitation) {

        $message = 'This invitation link is invalid or has expired.';

    } else {

        $groupId = $invitation['group_id'];

        $memberStmt = $pdo->prepare(
            "SELECT * FROM users_groups
             WHERE user_id = ?
             AND group_id = ?"
        );

        $memberStmt->execute([
            $_SESSION['user_id'],
            $groupId
        ]);

        $membership = $memberStmt->fetch();

        if ($membership) {

            $message = 'You are already a member of this club.';

        } else {

            try {

                $pdo->beginTransaction();

                $stmt = $pdo->prepare(
                    "INSERT INTO users_groups
                     (user_id, group_id, role)
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

                $success = true;
                $message = 'Welcome to the club! You are now a member.';

            } catch (Exception $e) {

                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                $message = 'Something went wrong. Please try again.';
            }
        }
    }
}

require 'includes/header.php';
require 'includes/menu.php';
?>


<div class="invite-page">

    <div class="invite-result-card">

        <?php if ($success): ?>

            <div class="invite-icon">⚽</div>

            <span class="section-label">
                WELCOME TO THE CLUB
            </span>

            <h1>You're in!</h1>

        <?php else: ?>

            <span class="section-label">
                CLUB INVITATION
            </span>

            <h1>Invitation</h1>

        <?php endif; ?>


        <p>
            <?= htmlspecialchars($message) ?>
        </p>


        <?php if ($groupId): ?>

            <a
                href="individual-group.php?id=<?= $groupId ?>"
                class="invite-button"
            >
                Go to club →
            </a>

        <?php else: ?>

            <a
                href="groups.php"
                class="invite-button"
            >
                Explore The Stands →
            </a>

        <?php endif; ?>

    </div>

</div>


<?php require 'includes/footer.php'; ?>