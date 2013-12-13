<?php
/**
 * View a single event
 * If the viewer of this event is the owner then show the form to invite guests to attend
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

$event = isset($_GET['eid']) ? $db->get_event($_SESSION['pid'], $_GET['eid']) : '';
$people_to_invite = isset($_GET['eid']) ? $db->get_people_to_invite($_SESSION['pid'], $_GET['eid']) : '';
?>

<!DOCTYPE html>
<a href="index.php?action=logout">logout</a>

<?php if (! empty($event)): ?>
<article>
    <h1><?php echo $event['description'] ?></h1>
    <p>
        <?php echo $event['start_time'] ?>
        for
        <?php echo $event['duration'] ?>
        hours long
    </p>
    <?php // TODO add how many people are invited and have accepted ?>
    <?php if (! empty($people_to_invite)): ?>
    <h2>Invite the following guests</h2>
    <ul>
        <?php foreach ($people_to_invite as $p): ?>
        <li><a href="invite.php?pid=<?php echo $p['pid'] ?>&eid=<?php echo $event['eid'] ?>"><?php echo "{$p['fname']} {$p['lname']}" ?></a></li>
        <?php endforeach ?>
    </ul>
    <?php endif ?>
</article>
<?php else: ?>
<p>Please specify an event</p>
<?php endif ?>

<?php include 'aside.php' ?>
