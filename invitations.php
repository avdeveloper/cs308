<?php
/**
 * Manage Pending Invitations
 *
 * If we don't have a session yet, redirect user back to login page
 *
 * @author Albert Villaroman <avdeveloper@gmail.com>
 * @course CS308 Prof. Phylis Frankl
 */
require_once('classes/query.php');

session_start();

$db = new DB();
$pid = $_SESSION['pid'];

if (! isset($_SESSION['pid'])) {
    header("Location: index.php");
}
else if (isset($_POST['invitation'])) {
    $db->respond_to_invitation($_SESSION['pid'], $_POST['invitation']['eid'], $_POST['invitation']['response'], $_POST['invitation']['visibility']);
}

$pending_invitations = $db->get_pending_invitations($pid);
?>

<!DOCTYPE html>
<a href="index.php?action=logout">logout</a>

<article>
    <h1>You have <?php echo count($pending_invitations) ?> pending invitations</h1>
    <?php if (count($pending_invitations) > 0): ?>
    <ul>
        <?php foreach ($pending_invitations as $i): ?>
        <li>
            <?php printf("%s %s is inviting you to %s on %s %s for %s hours", $i['host_fname'], $i['host_lname'], $i['description'], $i['date'], $i['start_time'], $i['duration']) ?>
            <form action="invitations.php" method="post">
                <input name="invitation[pid]" type="hidden" value="<?php echo $_SESSION['pid'] ?>">
                <input name="invitation[eid]" type="hidden" value="<?php echo $i['eid'] ?>">
                <input class="" name="invitation[visibility]" type="number" maxlength="2" value="<?php echo $_SESSION['d_privacy'] ?>" style="width: 3em;">
                <button name="invitation[response]" type="submit" value="1">&#10003;</button>
                <button name="invitation[response]" type="submit" value="2">x</button>
            </form>
        </li>
        <?php endforeach ?>
    </ul>
    <?php else: ?>
    <p>You have no invitations to accept</p>
    <?php endif ?>
</article>

<?php include 'aside.php' ?>
