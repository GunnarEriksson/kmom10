<?php
/**
 * This is a Origo pagecontroller to edit content for movies
 *
 * Contains reports of each section of the course OOPHP.
 */
include(__DIR__.'/config.php');

// Get parameters
$id     = isset($_POST['id'])    ? strip_tags($_POST['id']) : (isset($_GET['id']) ? strip_tags($_GET['id']) : null);
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
$published = isset($_POST['published'])  ? $_POST['published'] : null;
$rented = isset($_POST['rented'])  ? $_POST['rented'] : null;
$rents = isset($_POST['rents'])  ? $_POST['rents'] : null;
$genre   = isset($_POST['genre'])  ? $_POST['genre'] : null;
$save   = isset($_POST['save'])  ? true : false;
$acronym = isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;

// Check that incoming parameters are valid
is_numeric($id) or die('Check: Id must be numeric.');

$message = null;
$res = null;
$db = new Database($origo['database']);
if (isset($acronym) && (strcmp($acronym , 'admin') === 0)) {
    if ($save) {
        $movieContent = new MovieContent($db);
        $params = array($title, $director, $length, $year, $plot, $image, $subtext, $speech, $quality, $format, $price, $imdb, $youtube, $published, $rented, $rents, $id);
        $message = $movieContent->editFilmInDb($params, $genre);
    }

    $parameters = array('id' => $id, 'orderby' => 'id', 'order' => 'asc');
    $movieSearch = new MovieSearch($db, $parameters);
    $res = $movieSearch->searchMovie();

    $origo['debug'] = $db->Dump();
}


$MovieAdminForm = new MovieAdminForm($db);

$origo['title'] = "Uppdatera filminnehåll";
$origo['stylesheets'][] = 'css/form.css';

// Header
$origo['main'] = <<<EOD
<h1>{$origo['title']}</h1>
{$MovieAdminForm->createEditMovieInDbForm($origo['title'], $res, $message)}
EOD;

// Finally, leave it all to the rendering phase of Origo.
include(ORIGO_THEME_PATH);
