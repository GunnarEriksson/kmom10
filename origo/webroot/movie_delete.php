<?php
/**
 * This is a Origo pagecontroller to delete a page or a blog post.
 *
 * Contains reports of each section of the course OOPHP.
 */
include(__DIR__.'/config.php');

$db = new Database($origo['database']);
$content = new Content($db);

$id      = isset($_POST['id'])      ? strip_tags($_POST['id']) : (isset($_GET['id']) ? strip_tags($_GET['id']) : null);
$genre   = isset($_POST['genre'])  ? $_POST['genre'] : null;
$delete  = isset($_POST['delete'])  ? true : false;
$acronym = isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;

is_numeric($id) or die('Check: Id must be numeric.');

$message = null;
$res = null;
$db = new Database($origo['database']);
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

$MovieAdminForm = new MovieAdminForm($db);

$origo['title'] = "Ta bort film från databas";
$origo['stylesheets'][] = 'css/form.css';

// Header
$origo['main'] = <<<EOD
<h1>{$origo['title']}</h1>
{$MovieAdminForm->createDeleteMovieForm($origo['title'], $message, $res)}
EOD;

// Finally, leave it all to the rendering phase of Origo.
include(ORIGO_THEME_PATH);
