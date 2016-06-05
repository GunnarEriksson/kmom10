<?php
/**
 * This is a Origo pagecontroller to delete user profiles in the database.
 *
 * Hamdles the deletion of user profiles in the database. Only a user who has
 * admin rights (logged in as user), could delete user profiles.
 */
include(__DIR__.'/config.php');

// Get parameters
$id     = isset($_POST['id']) ? strip_tags($_POST['id']) : (isset($_GET['id']) ? strip_tags($_GET['id']) : null);
$acronym  = isset($_POST['acronym']) ? $_POST['acronym'] : null;
$name = isset($_POST['name']) ? $_POST['name'] : null;
$info = isset($_POST['info']) ? $_POST['info'] : null;
$email = isset($_POST['email']) ? $_POST['email'] : null;
$delete  = isset($_POST['delete'])  ? true : false;
$user = isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;

// Check that incoming parameters are valid
is_numeric($id) or die('Check: Id must be numeric.');

$userAdminForm = new UserAdminForm();

$message = null;
$res = null;
if (isset($user) && (strcmp($user , 'admin') === 0)) {
    $db = new Database($origo['database']);
    if ($delete) {
        $userContent = new UserContent($db);
        $params = array($id);
        $message = $userContent->deleteUserInDb($params);
    }

    $parameters = array('id' => $id, 'acronym' => $acronym);
    $userSearch = new UserSearch($db, $parameters);
    $res = $userSearch->searchUser();

    $origo['debug'] = $db->Dump();
}

$origo['title'] = "Radera användareprofil";
$origo['stylesheets'][] = 'css/form.css';

// Header
$origo['main'] = <<<EOD
<h1>{$origo['title']}</h1>
{$userAdminForm->createDeleteUserInDbFrom($origo['title'], $res, $message)}
EOD;

// Finally, leave it all to the rendering phase of Origo.
include(ORIGO_THEME_PATH);
