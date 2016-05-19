<?php
/**
 * This is a Origo pagecontroller to reset the content for movies.
 *
 * Contains reports of each section of the course OOPHP.
 */
include(__DIR__.'/config.php');

$reset = isset($_POST['reset']) ? true : false;

$db = new Database($origo['database']);
$movieContent = new MovieContent($db);
$message = $movieContent->resetContent();
$MovieAdminForm = new MovieAdminForm();

$origo['title'] = "Återställ databasen för filmer";
$origo['stylesheets'][] = 'css/form.css';
$origo['debug'] = $db->Dump();

$origo['main'] = <<<EOD
<h1>{$origo['title']}</h1>
{$MovieAdminForm->createResetMovieDbForm($origo['title'], $message)}
EOD;

// Finally, leave it all to the rendering phase of Origo.
include(ORIGO_THEME_PATH);
