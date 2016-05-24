<?php
/**
 * This is a Origo pagecontroller to reset the content for movies.
 *
 * Contains reports of each section of the course OOPHP.
 */
include(__DIR__.'/config.php');

$reset = isset($_POST['reset']) ? true : false;

$message = null;
if ($reset) {
    $db = new Database($origo['database']);
    $movieContent = new MovieContent($db);
    $message = $movieContent->resetContent();
    $origo['debug'] = $db->Dump();
}

$movieAdminForm = new MovieAdminForm();


$origo['title'] = "Återställ databasen för filmer";
$origo['stylesheets'][] = 'css/form.css';

$origo['main'] = <<<EOD
<h1>{$origo['title']}</h1>
{$movieAdminForm->createResetMovieDbForm($origo['title'], $message)}
EOD;

// Finally, leave it all to the rendering phase of Origo.
include(ORIGO_THEME_PATH);
