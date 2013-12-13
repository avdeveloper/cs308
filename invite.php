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

if ( isset($_GET['pid']) && isset($_GET['eid']) ) {
    $db->invite($_GET['pid'], $_GET['eid']);
}

header("Location: event.php?eid={$_GET['eid']}");
?>
