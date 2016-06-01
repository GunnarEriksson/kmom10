<?php
/**
 * This is a Origo pagecontroller to reset the movie database.
 *
 * Handles reset of the movie database via a form. Only users that
 * have admin rights (logged in as admin) are allowed to reset tge movie
 * database.
 */
include(__DIR__.'/config.php');

// Get parameters
$reset      = isset($_POST['reset']) ? true : false;
$acronym    = isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;

$db = new Database($origo['database']);
$movieAdminForm = new MovieAdminForm();

$message = null;
if ($reset && isset($acronym) && (strcmp($acronym , 'admin') === 0)) {

    $movieContent = new MovieContent($db);
    $message = $movieContent->resetContent();
    $origo['debug'] = $db->Dump();
}


$origo['title'] = "Återställ databasen för filmer";
$origo['stylesheets'][] = 'css/form.css';

$origo['main'] = <<<EOD
<h1>{$origo['title']}</h1>
{$movieAdminForm->createResetMovieDbForm($origo['title'], $message)}
EOD;

// Finally, leave it all to the rendering phase of Origo.
include(ORIGO_THEME_PATH);
