<?php
/**
 * This is a Origo pagecontroller for the login page
 *
 * Handles the login for this website.
 */

include(__DIR__.'/config.php');
$db = new Database($origo['database']);
$user = new User($db);

// Check if user and password is okey
if(isset($_POST['login'])) {
    $user->login($_POST['acronym'], $_POST['password']);
    header('Location: login.php');
}

$output = $user->getUserLoginStatus();

 // Do it and store it all in variables in the Origo container.
$origo['title'] = "Login";
$origo['stylesheets'][] = 'css/form.css';

$origo['main'] = <<<EOD
<h1>{$origo['title']}</h1>
<form method=post>
  <fieldset>
  <legend>{$origo['title']}</legend>
  <p><label>Användarnamn:<br/><input type='text' name='acronym' value=''/></label></p>
  <p><label>Lösenord:<br/><input type='password' name='password' value=''/></label></p>
  <p><input type='submit' name='login' value='Login'/></p>
  <output><strong>{$output}</strong></output>
  </fieldset>
</form>
<form action="user_create.php" method=get>
  <fieldset>
  <legend>Skapa nytt konto</legend>
  <output>Inte ännu medlem? Skapa nytt konto.</output><br/>
  <input type='submit' name='createUser' value='Skapa'/>
  </fieldset>
</form>
EOD;

// Finally, leave it all to the rendering phase of Origo.
include(ORIGO_THEME_PATH);
