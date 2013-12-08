<?php
/**
 * Login Page
 * If the user is not yet signed in to the app, then show this page and ask him/her for the
 * user id and password. Otherwise, redirect user to homepage.
 *
 * Check session to determine whether user is currently signed in to WebCal or not
 *
 * @author Albert Villaroman <avdeveloper@gmail.com>
 * @course CS308 Prof. Phylis Frankl
 */

require_once('classes/query.php');
$db = new DB();

if (isset($_POST['user'])) {
    $result = $db->login_user($_POST['user']);

    if (isset($result['pid'])) {
        session_start();
        $_SESSION['pid'] = $result['pid'];
        $_SESSION['d_privacy'] = $result['d_privacy'];
        header("Location: home.php");
    }
    else {
        $message = 'We could not find a user with that combination of id and password. Please try again.';
    }
}
else if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_start();
    session_destroy();
}
else {
    $message = 'Please login to WebCal with your id and password';
}

?>

<!DOCTYPE html>
<p id="message"><?php echo isset($message) ? $message : ''; ?></p>
<form action="index.php" method="POST">
    <fieldset>
        <legend>Login to WebCal</legend>
        <input type="text" name="user[pid]" placeholder="enter your id">
        <input type="password" name="user[password]" placeholder="password">
        <input type="submit" value="Log in">
    </fieldset>
</form>
