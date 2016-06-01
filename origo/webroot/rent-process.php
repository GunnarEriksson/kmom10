<?php
/**
 * This is a Origo process page to rent a movie.
 *
 * Only users that has user rights (has logged in) could rent a movie. When a user
 * rents a movie, the number of rents is stepped by one. In the database, the
 * rent time stamp is set to actual date time. The page returns to the page
 * where it was called from with a message. The message shows if the rent of the
 * movie was successful or not.
 */
include(__DIR__.'/config.php');

// Get parameters
$id     = isset($_POST['id'])    ? strip_tags($_POST['id']) : (isset($_GET['id']) ? strip_tags($_GET['id']) : null);
$rents = isset($_POST['rents'])  ? $_POST['rents'] : null;
$save   = isset($_POST['save'])  ? true : false;
$acronym = isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;

// Check that incoming parameters are valid
is_numeric($id) or die('Check: Id must be numeric.');

$db = new Database($origo['database']);

$isRented = null;
$res = null;
if (isset($acronym)) {
    if ($save) {
        $movieContent = new MovieContent($db);
        $params = array($rents, $id);
        $isRented = $movieContent->updateNumOfRentsInDb($params);

        $origo['debug'] = $db->Dump();
    }
}

// Redirect to the movie
header("Location: " . $_SERVER['HTTP_REFERER'] . '&result=' . $isRented);
