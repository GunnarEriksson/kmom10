<?php
/**
 * This is a Origo pagecontroller to add new users to the database.
 *
 * Creates and add a new user profile to the database. Used by users that will
 * be a member of the Rental Movie to be able to rent movies.
 */
include(__DIR__.'/config.php');

// Get parameters
$acronym  = isset($_POST['acronym']) ? $_POST['acronym'] : null;
$name = isset($_POST['name']) ? $_POST['name'] : null;
$info = isset($_POST['info']) ? $_POST['info'] : null;
$email = isset($_POST['email']) ? $_POST['email'] : null;
$password = isset($_POST['password']) ? $_POST['password'] : null;
$save  = isset($_POST['save'])  ? true : false;

$db = new Database($origo['database']);
$userContent = new UserContent($db);

$message = null;
if ($save) {
    $params = array($acronym, $name, $info, $email, $password);
    $message = $userContent->addNewUserToDb($params);
    $origo['debug'] = $db->Dump();
}

// Erase form parameters when content is created successfully;
$formParams = null;
if (!$userContent->isContentCreated()) {
    $formParams = array(
        'acronym' => htmlentities($acronym, null, 'UTF-8'),
        'name' => htmlentities($name, null, 'UTF-8'),
        'info' => htmlentities($info, null, 'UTF-8'),
        'email' => htmlentities($email, null, 'UTF-8'),
    );
}

$userAdminForm = new UserAdminForm();

$origo['title'] = "Skapa ny användare";
$origo['stylesheets'][] = 'css/form.css';

// Header
$origo['main'] = <<<EOD
<h1>{$origo['title']}</h1>
{$userAdminForm->createAddUserToDbFrom($origo['title'], $message, $formParams)}
EOD;

// Finally, leave it all to the rendering phase of Origo.
include(ORIGO_THEME_PATH);
