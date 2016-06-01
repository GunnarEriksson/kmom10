<?php
/**
 * This is a Origo pagecontroller to add movies to the database.
 *
 * Handles adding of movies to the database via a form. Only users that
 * have admin rights (logged in as admin) are allowed to add movies in the
 * database.
 */
include(__DIR__.'/config.php');

// Get parameters
$title  = isset($_POST['title']) ? $_POST['title'] : null;
$director = isset($_POST['director']) ? $_POST['director'] : null;
$length = isset($_POST['length']) ? $_POST['length'] : null;
$year = isset($_POST['year']) ? $_POST['year'] : null;
$image = isset($_POST['image']) ? $_POST['image'] : null;
$subtext = isset($_POST['subtext']) ? $_POST['subtext'] : null;
$speech = isset($_POST['speech']) ? $_POST['speech'] : null;
$quality = isset($_POST['quality']) ? $_POST['quality'] : null;
$format = isset($_POST['format']) ? $_POST['format'] : null;
$price = isset($_POST['price']) ? $_POST['price'] : null;
$imdb = isset($_POST['imdb']) ? $_POST['imdb'] : null;
$youtube = isset($_POST['youtube']) ? $_POST['youtube'] : null;
$plot   = isset($_POST['plot'])  ? $_POST['plot'] : null;
$genre   = isset($_POST['genre'])  ? $_POST['genre'] : null;
$save   = isset($_POST['save'])  ? true : false;
$acronym = isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;

$db = new Database($origo['database']);
$MovieAdminForm = new MovieAdminForm($db);

$message = null;
if ($save && isset($acronym) && (strcmp($acronym , 'admin') === 0)) {
    $movieContent = new MovieContent($db);
    $params = array($title, $director, $length, $year, $plot, $image, $subtext, $speech, $quality, $format, $price, $imdb, $youtube);
    $message = $movieContent->addNewFilmToDb($params, $genre);
    $origo['debug'] = $db->Dump();
}

$origo['title'] = "Lägg till ny film i databasen";
$origo['stylesheets'][] = 'css/form.css';

// Header
$origo['main'] = <<<EOD
<h1>{$origo['title']}</h1>
{$MovieAdminForm->createAddMovieToDbForm($origo['title'], $message)}
EOD;

// Finally, leave it all to the rendering phase of Origo.
include(ORIGO_THEME_PATH);
