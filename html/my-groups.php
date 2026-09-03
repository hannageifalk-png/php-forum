<?php
session_start();

require 'includes/menu.php';
require 'includes/database.php';

if (!isset($_SESSION['user_id'])) {
    echo '<p>You need to log in to view your groups.</p>';
    exit;
}

$stmt = $pdo->prepare(
    "SELECT groups.*
     FROM groups
     JOIN users_groups ON groups.id = users_groups.group_id
     WHERE users_groups.user_id = ?"
);
$stmt->execute([$_SESSION['user_id']]);
$groups = $stmt->fetchAll();

echo '<h1>My Groups</h1>';

if ($groups) {
    foreach ($groups as $group) {
        echo '<h2>' . htmlspecialchars($group['name']) . '</h2>';
        echo '<p>ID: ' . $group['id'] . '</p>';
        echo '<a href="individual-group.php?id=' . $group['id'] . '">View Group</a>';
    }
} else {
    echo '<p>You are not a member of any groups.</p>';
}


