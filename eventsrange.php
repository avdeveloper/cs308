<?php
/**
 * View Events by a speicific range of dates
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

$from = ! isset($_POST['from']) ? date('Y-m-d') : $_POST['from'];
$to = ! isset($_POST['to']) ? date('Y-m-d', (strtotime('+7 day', strtotime(date('Y-m-d'))))) : $_POST['to'];
$events_between = $db->get_events_between($pid, $from, $to);
?>

<!DOCTYPE html>
<a href="index.php?action=logout">logout</a>

<form action="eventsrange.php" method="POST">
    <h1>
        Viewing Events from 
        <input type="date" name="from" value="<?php echo $from ?>"> 
        to 
        <input type="date" name="to" value="<?php echo $to ?>">
    </h1>
    <input type="submit" value="search">
</form>
<?php if (count($events_between) > 0): ?>
<ul>
    <?php foreach ($events_between as $e): ?>
    <li>
        <time><?php echo $e['date'] ?> <?php echo $e['start_time'] ?></time>
        <a href="event.php?eid=<?php echo $e['eid'] ?>">
            <?php echo $e['description'] ?>
        </a>
    </li>
    <?php endforeach ?>
</ul>
<?php else: ?>
<p>You have no events between these dates</p>
<?php endif ?>

<?php include 'aside.php' ?>
