<?php
/**
 * This is a Origo pagecontroller to show a user profile.
 *
 * Shows a user profile and has a button if the user wants to edit the profile.
 */
include(__DIR__.'/config.php');

// Get parameters
$acronym = isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;

$db = new Database($origo['database']);

$parameters = array('acronym' => $acronym);
$userSearch = new UserSearch($db, $parameters);
$res = $userSearch->searchUser();
$userProfile = $userSearch->cleanProfileParameters($res);

if (empty($userProfile['updated'])) {
    $adminInfo = "Publicerad: " . $userProfile['published'];
} else {
    $adminInfo = "Uppdaterad: " . $userProfile['updated'];
}

$origo['title'] = "Användareprofil";
$origo['stylesheets'][] = 'css/user.css';

// Header
$origo['main'] = <<<EOD
<h1>{$origo['title']}</h1>
<h2>{$userProfile['name']}</h2>
<p><strong>Användarnamn</strong><br/>{$userProfile['acronym']}</p>
<p><strong>Information</strong/><br/>{$userProfile['info']}</p>
<p><strong>E-post</strong><br/>{$userProfile['email']}</p>
<p class="small-italic">{$adminInfo}</p>
<form action="user_edit.php" method=get>
    <input type='hidden' name='id' value="{$userProfile['id']}"/>
    <input type='submit' name='change' value='Ändra'/>
</form>
EOD;

// Finally, leave it all to the rendering phase of Origo.
include(ORIGO_THEME_PATH);
