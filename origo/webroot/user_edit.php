<?php
/**
 * This is a Origo pagecontroller to udpate user profile in the database.
 *
 * Contains reports of each section of the course OOPHP.
 */
include(__DIR__.'/config.php');


// Get parameters
$id     = isset($_POST['id']) ? strip_tags($_POST['id']) : (isset($_GET['id']) ? strip_tags($_GET['id']) : null);
$acronym  = isset($_POST['acronym']) ? $_POST['acronym'] : null;
$name = isset($_POST['name']) ? $_POST['name'] : null;
$info = isset($_POST['info']) ? $_POST['info'] : null;
$email = isset($_POST['email']) ? $_POST['email'] : null;
$password = isset($_POST['password']) && !empty($_POST['password']) ? $_POST['password'] : null;
$user = isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;
$save  = isset($_POST['save'])  ? true : false;

// Check that incoming parameters are valid
is_numeric($id) or die('Check: Id must be numeric.');

$message = null;
$res = null;
if (isset($user)) {
    $db = new Database($origo['database']);
    if ($save && ((strcmp($user , 'admin') === 0) || (strcmp($user , $acronym) === 0))) {
        $userContent = new UserContent($db);
        $params = array($acronym, $name, $info, $email, $password, $id);
        $message = $userContent->updateUserInDb($params);
    }

    $parameters = array('id' => $id, 'acronym' => $acronym);
    $userSearch = new UserSearch($db, $parameters);
    $res = $userSearch->searchUser();

    $origo['debug'] = $db->Dump();
}


$userAdminForm = new UserAdminForm();

$origo['title'] = "Uppdatera användareprofil";
$origo['stylesheets'][] = 'css/form.css';

// Header
$origo['main'] = <<<EOD
<h1>{$origo['title']}</h1>
{$userAdminForm->createEditUserInDbForm($origo['title'], $res, $message)}
EOD;

// Finally, leave it all to the rendering phase of Origo.
include(ORIGO_THEME_PATH);
