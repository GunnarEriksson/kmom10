<?php
/**
 * This is a Origo pagecontroller to edit movies in the database.
 *
 * Handles edit of movies in the database via a form. Only users that
 * have admin rights (logged in as admin) are allowed to edit movies in the
 * database.
 */
include(__DIR__.'/config.php');

define('MOVIE_FOLDER_PATH', "img/movie/");
define('MAX_IMAGE_SIZE', "204800");

/**
 * Uploads a image to defined folder.
 *
 * Uploads the image to defined folder with the possibility to set maximum
 * image size that is allowed to upload.
 *
 * @param  [] $file the file array with upload information.
 *
 * @return string the path and image name with extension.
 */
function uploadImage($file)
{
    $imagePath = null;
    if (isset($file)) {
        $fileUploader = new FileUploader(MOVIE_FOLDER_PATH, MAX_IMAGE_SIZE);
        $imagePath = $fileUploader->uploadImage($file);

        $imagePath = getImagePathUsedInDb($imagePath);
    }

    return $imagePath;
}

/**
 * Gets the image path that is used in database.
 *
 * Creates the link, which is stored in the database, that is used in the img
 * HTML tag to show the image. The function removes the root image folder name
 * because Image class uses the root image folder as a start point for the image
 * folders.
 *
 * @param  [] $imagePath the path and name for the image.
 *
 * @return string the path and name of the image with the root image folder removed.
 */
function getImagePathUsedInDb($imagePath)
{
    if (isset($imagePath)) {
        $imagePath = explode("img/", $imagePath);
    }

    return $imagePath[1];
}

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
$rent = isset($_POST['rent'])  ? $_POST['rent'] : null;
$file = isset($_FILES['image']) ? $_FILES['image'] : null;
$acronym = isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;

// Check that incoming parameters are valid
is_numeric($id) or die('Check: Id must be numeric.');

$db = new Database($origo['database']);
$MovieAdminForm = new MovieAdminForm($db);

$message = null;
$fileUploadMessage = null;
$res = null;
if (isset($acronym) && (strcmp($acronym , 'admin') === 0)) {
    if ($save) {
        if ($file && !empty($file['name'])) {
            try {
                $image = uploadImage($file);
                $fileUploadMessage = "Bild har laddats upp!";
            } catch (Exception $error) {
                $image = null;
                $fileUploadMessage = $error->getMessage();
            }
        }

        $movieContent = new MovieContent($db);
        $params = array($title, $director, $length, $year, $plot, $image, $subtext, $speech, $quality, $format, $price, $imdb, $youtube, $published, $rented, $rents, $id);
        $message = $movieContent->editFilmInDb($params, $genre);
    }

    $parameters = array('id' => $id, 'orderby' => 'id', 'order' => 'asc');
    $movieSearch = new MovieSearch($db, $parameters);
    $res = $movieSearch->searchMovie();

    $origo['debug'] = $db->Dump();
}


$origo['title'] = "Uppdatera filminnehåll";
$origo['stylesheets'][] = 'css/form.css';

// Header
$origo['main'] = <<<EOD
<h1>{$origo['title']}</h1>
{$MovieAdminForm->createEditMovieInDbForm($origo['title'], $res, $message, $fileUploadMessage)}
EOD;

// Finally, leave it all to the rendering phase of Origo.
include(ORIGO_THEME_PATH);
