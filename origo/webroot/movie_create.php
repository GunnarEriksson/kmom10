<?php
/**
 * This is a Origo pagecontroller to add movies to the database.
 *
 * Handles adding of movies to the database via a form. Only users that
 * have admin rights (logged in as admin) are allowed to add movies in the
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
$title  = isset($_POST['title']) ? $_POST['title'] : null;
$director = isset($_POST['director']) ? $_POST['director'] : null;
$length = isset($_POST['length']) ? $_POST['length'] : null;
$year = isset($_POST['year']) ? $_POST['year'] : null;
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
$file = isset($_FILES['image']) ? $_FILES['image'] : null;
$acronym = isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;

$db = new Database($origo['database']);
$MovieAdminForm = new MovieAdminForm($db);

$message = null;
$fileUploadMessage = null;
if ($save && isset($acronym) && (strcmp($acronym , 'admin') === 0)) {
    try {
        $image = uploadImage($file);
        $fileUploadMessage = "Bild har laddats upp!";
    } catch (Exception $error) {
        $image = null;
        $fileUploadMessage = $error->getMessage();
    }

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
{$MovieAdminForm->createAddMovieToDbForm($origo['title'], $message, $fileUploadMessage)}
EOD;

// Finally, leave it all to the rendering phase of Origo.
include(ORIGO_THEME_PATH);
