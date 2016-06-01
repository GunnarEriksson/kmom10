<?php
/**
 * This is a Origo pagecontroller to delete movies in the database.
 *
 * Handles removal of movies in the database via a form. Only users that
 * have admin rights (logged in as admin) are allowed to remove movies in the
 * database.
 */
include(__DIR__.'/config.php');

// Get parameters
$id      = isset($_POST['id'])      ? strip_tags($_POST['id']) : (isset($_GET['id']) ? strip_tags($_GET['id']) : null);
$genre   = isset($_POST['genre'])  ? $_POST['genre'] : null;
$delete  = isset($_POST['delete'])  ? true : false;
$acronym = isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;

is_numeric($id) or die('Check: Id must be numeric.');

$db = new Database($origo['database']);
$MovieAdminForm = new MovieAdminForm($db);

$message = null;
$res = null;
if (isset($acronym) && (strcmp($acronym , 'admin') === 0)) {
    if ($delete) {
        $movieContent = new MovieContent($db);
        $params = array($id);
        $message = $movieContent->removeFilmInDb($params, $genre);
    }

    $parameters = array('id' => $id, 'orderby' => 'id', 'order' => 'asc');
    $movieSearch = new MovieSearch($db, $parameters);
    $res = $movieSearch->searchMovie();

    $origo['debug'] = $db->Dump();
}

$origo['title'] = "Ta bort film från databas";
$origo['stylesheets'][] = 'css/form.css';

// Header
$origo['main'] = <<<EOD
<h1>{$origo['title']}</h1>
{$MovieAdminForm->createDeleteMovieForm($origo['title'], $message, $res)}
EOD;

// Finally, leave it all to the rendering phase of Origo.
include(ORIGO_THEME_PATH);
