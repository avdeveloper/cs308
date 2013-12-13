<?php
/**
 * Manage Events the user owns
 *
 * If we don't have a session yet, redirect user back to login page
 *
 * @author Albert Villaroman <avdeveloper@gmail.com>
 * @course CS308 Prof. Phylis Frankl
 */
require_once('classes/query.php');

session_start();
if (! isset($_SESSION['pid'])) {
    header("Location: index.php");
}

$db = new DB();
$pid = $_SESSION['pid'];
$managed_events = $db->get_managed_events($pid);
?>

<!DOCTYPE html>
<a href="index.php?action=logout">logout</a>

<article>
    <h1>Manage Events</h1>
    <?php if ($managed_events > 0): ?>
    <ul>
        <?php foreach ($managed_events as $e): ?>
        <li>
            <span><?php echo $e['attendees'] ?> are attending </span>
            <a href="event.php?eid=<?php echo $e['eid'] ?>">
                <?php echo $e['description'] ?>
            </a>
        </li>
        <?php endforeach ?>
    </ul>
    <?php else: ?>
    <p>You have no events to manage</p>
    <?php endif ?>
</article>

<?php include 'aside.php' ?>
