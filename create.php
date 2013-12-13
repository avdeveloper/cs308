<?php
/**
 * Create an event
 * Allow user to create arbitrary amount of dates
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

if (isset($_POST['event'])) {
    $db->create_event($_POST['event']);
}
?>

<!DOCTYPE html>
<a href="index.php?action=logout">logout</a>

<h1>Create an Event</h1>
<form action="create.php" method="post">
    <p>
        <input name="event[pid]" type="hidden" value="<?php echo $_SESSION['pid'] ?>">
    </p>
    <p>
        <input name="event[description]" type="text" placeholder="description" required>
    </p>
    <p>
        <input name="event[start_time]" type="text" placeholder="start time" pattern="[\d{1,2}:]{2,}" required>
    </p>
    <p>
        <input name="event[duration]" type="text" placeholder="duration" pattern="[\d{1,2}:]{2,}" required>
    </p>
    <p>
        <input name="event[dates]" type="text" placeholder="separate dates by comma">
    </p>
    <p>
        <button type="submit">Create Event</button>
    </p>
</form>

<?php include 'aside.php' ?>
