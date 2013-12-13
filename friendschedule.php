<?php
/**
 * View Friends' Schedule
 * If no friend is selected then ask for one and date to view
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

if (isset($_POST['friend'])) {
    $friend = $_POST['friend'];
    $friend_schedule = $db->get_friend_schedule($_SESSION['pid'], $friend['pid'], $friend['date']);
}

$friends = $db->get_friends($_SESSION['pid']);
?>

<!DOCTYPE html>
<a href="index.php?action=logout">logout</a>

<article>
    <h1>View a Friend's Schedule</h1>
    <form action="friendschedule.php" method="post">
        <select name="friend[pid]">
            <?php foreach ($friends as $f): ?>
            <option value="<?php echo $f['pid'] ?>"><?php echo "{$f['fname']} {$f['lname']}" ?></option>
            <?php endforeach ?>
        </select>
        <input name="friend[date]" type="date">
        <button type="submit">go</button>
    </form>
    <?php if (! empty($friend_schedule)): ?>
        <?php if ($friend_schedule[0]['level'] < 2): ?>
        <p><?php echo "{$friend_schedule[0]['fname']} {$friend_schedule[0]['lname']}" ?> is busy</p>
        <?php else: ?>
        <p>
            Showing
            <?php echo "{$friend_schedule[0]['fname']}'s schedule for {$_POST['friend']['date']}" ?>
        </p>
        <ul>
            <?php foreach ($friend_schedule as $s): ?>
                <?php if ($s['visibility'] <= $s['level']): ?>
                <li>
                    <time><?php echo $s['start_time'] ?></time>
                    <span><?php echo $s['description'] ?></span>
                    <span>for <?php echo $s['duration'] ?> hours</span>
                </li>
                <?php endif ?>
            <?php endforeach ?>
        </ul>
        <?php endif ?>
    <?php endif ?>
</article>

<?php include 'aside.php' ?>
