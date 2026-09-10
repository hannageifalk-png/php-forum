<?php
session_start();

require 'includes/database.php';
require 'includes/functions.php';
require 'includes/header.php';
require 'includes/menu.php';

$stmt = $pdo->prepare("SELECT * FROM `groups`");
$stmt->execute();

$groups = $stmt->fetchAll();
?>

<div class="groups-header">
    <span>THE STANDS</span>
    <h1>Find your corner of football.</h1>

    <p>
        Discover supporter groups, football topics and communities
        and join the conversations that interest you.
    </p>

    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="/create-group.php" class="create-club-button">
            + Start a Club
        </a>
    <?php endif; ?>
</div>

<div class="groups-list">

<?php 
foreach ($groups as $group): ?>

    <div class="discover-group-card">

        <div class="discover-group-icon">
            ⚽
        </div>

        <div class="discover-group-content">
            <h2><?= htmlspecialchars($group['name']) ?></h2>

            <p>Join the conversation</p>

            <a href="individual-group.php?id=<?= $group['id'] ?>">
                View club →
            </a>
        </div>

    </div>

<?php endforeach; ?>

<?php
include 'includes/footer.php'; 
?>