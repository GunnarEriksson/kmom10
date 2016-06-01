<?php
/**
 * This is a Origo pagecontroller to add news blogs to the database.
 *
 * Adds new blog posts to the database if the user has user rights.
 */
include(__DIR__.'/config.php');

// Get parameters
$title  = isset($_POST['title']) ? $_POST['title'] : null;
$url    = isset($_POST['url'])   ? strip_tags($_POST['url']) : null;
$data   = isset($_POST['data'])  ? $_POST['data'] : null;
$type   = isset($_POST['type'])  ? $_POST['type'] : null;
$filter = isset($_POST['filter']) ? $_POST['filter'] : null;
$category = isset($_POST['category']) ? $_POST['category'] : null;
$published = isset($_POST['published']) && !empty($_POST['published']) ? strip_tags($_POST['published']) : null;
$save   = isset($_POST['save'])  ? true : false;
$acronym = isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;

$message = null;
$db = new Database($origo['database']);
$blogAdminForm = new BlogAdminForm($db);

if ($save && isset($acronym)) {
    $user = new User($db);
    $author = $user->getAcronym();
    $params = array($title, null, null, $data, $type, $filter, $author, $category, $published);
    $content = new Content($db);
    $message = $content->createContent($params);
    $origo['debug'] = $db->Dump();
}

$origo['title'] = "Lägg till nyheter i databasen";
$origo['stylesheets'][] = 'css/form.css';

// Header
$origo['main'] = <<<EOD
<h1>{$origo['title']}</h1>
{$blogAdminForm->createNewsBlogToDbForm($origo['title'], $message)}
EOD;

// Finally, leave it all to the rendering phase of Origo.
include(ORIGO_THEME_PATH);
