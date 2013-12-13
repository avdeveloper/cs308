<?php
/**
 * Home page
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
$todays_events = $db->get_events_for_date($pid, date('Y-m-d'));
?>

<!DOCTYPE html>
<a href="index.php?action=logout">logout</a>

<article>
    <h1>
        Today's Events
        <span><?php echo date('l M j, Y') ?></span>
    </h1>
    <?php if (count($todays_events) > 0): ?>
    <ul>
        <?php foreach ($todays_events as $e): ?>
        <li>
            <time><?php echo $e['start_time'] ?></time>
            <a href="event.php?eid=<?php echo $e['eid'] ?>">
                <?php echo $e['description'] ?>
            </a>
        </li>
        <?php endforeach ?>
    </ul>
    <?php else: ?>
    <p>You have no events for today</p>
    <?php endif ?>
</article>

<?php include 'aside.php' ?>
