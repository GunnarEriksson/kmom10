<?php
include(__DIR__.'/config.php');

// Get parameters
$id     = isset($_POST['id'])    ? strip_tags($_POST['id']) : (isset($_GET['id']) ? strip_tags($_GET['id']) : null);
$rents = isset($_POST['rents'])  ? $_POST['rents'] : null;
$save   = isset($_POST['save'])  ? true : false;
$acronym = isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;

// Check that incoming parameters are valid
is_numeric($id) or die('Check: Id must be numeric.');

$message = null;
$res = null;
$db = new Database($origo['database']);
if (isset($acronym)) {
    if ($save) {
        $movieContent = new MovieContent($db);
        $params = array($rents, $id);
        $message = $movieContent->updateNumOfRentsInDb($params);

        $origo['debug'] = $db->Dump();
    }
}

// Redirect to the movie
header("Location: " . $_SERVER['HTTP_REFERER'] . '&result=' . $message);
