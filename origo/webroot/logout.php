<?php
/**
 * This is a Origo pagecontroller for the logout page
 *
 * Handles the logout for this website.
 */

include(__DIR__.'/config.php');

$db = new Database($origo['database']);
$user = new User($db);

// Logout the user
if(isset($_POST['logout'])) {
  $user->logout();

  header('Location: logout.php');
}

$output = $user->getUserLoginStatus();

$origo['title'] = "Logout";
$origo['stylesheets'][] = 'css/form.css';

$origo['main'] = <<<EOD
<h1>{$origo['title']}</h1>
<form method=post>
    <fieldset>
    <legend>{$origo['title']}</legend>
    <p><input type='submit' name='logout' value='Logout'/></p>
    <output><strong>{$output}</strong></output>
    </fieldset>
</form>
EOD;

// Finally, leave it all to the rendering phase of Origo.
include(ORIGO_THEME_PATH);
