<?php
session_start();

require 'includes/menu.php';
require 'includes/database.php';
require 'includes/functions.php';

$myGroups = getMyGroups();
?>
<h1>My Groups</h1>

<a href="create-group.php">Create Group</a>
<ul>

<?php
foreach ($myGroups as $group) {
    ?>
    <li>
        <a href="individual-group.php?id=<?= $group['id']; ?>">
    <?= $group['name']; ?>
</a>
    </li>
    <?php
}
?>

</ul>

<?php
$stmt = $pdo->prepare("SELECT * FROM groups");
$stmt->execute();

$groups = $stmt->fetchAll();

foreach ($groups as $group) {
    echo '<div class="group-card">';

    echo '<h2>' . htmlspecialchars($group['name']) . '</h2>';

    echo '<a class="view-group" href="individual-group.php?id=' . $group['id'] . '">
            View group
          </a>';

    echo '</div>';
}