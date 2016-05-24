<?php
/**
 * This is a Origo pagecontroller to edit content for pages and blog posts
 *
 * Contains reports of each section of the course OOPHP.
 */
include(__DIR__.'/config.php');

$db = new Database($origo['database']);
$content = new Content($db);

// Get parameters
$id     = isset($_POST['id'])    ? strip_tags($_POST['id']) : (isset($_GET['id']) ? strip_tags($_GET['id']) : null);
$title  = isset($_POST['title']) ? $_POST['title'] : null;
$slug   = isset($_POST['slug'])  ? $_POST['slug']  : null;
$url    = isset($_POST['url'])   ? strip_tags($_POST['url']) : null;
$data   = isset($_POST['data'])  ? $_POST['data'] : null;
$type   = isset($_POST['type'])  ? $_POST['type'] : null;
$filter = isset($_POST['filter']) ? $_POST['filter'] : null;
$author = isset($_POST['author']) ? $_POST['author'] : null;
$category = isset($_POST['category']) ? $_POST['category'] : null;
$published = isset($_POST['published']) && !empty($_POST['published']) ? strip_tags($_POST['published']) : null;
$deleted = isset($_POST['deleted']) ? strip_tags($_POST['deleted']) : null;
$save   = isset($_POST['save'])  ? true : false;
$acronym = isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;

// Check that incoming parameters are valid
is_numeric($id) or die('Check: Id must be numeric.');

$message = null;
$res = null;
if (isset($acronym)) {
    if ($save && ((strcmp($acronym , 'admin') === 0) || (strcmp($acronym , $author) === 0))) {
        $url = empty($url) ? null : $url;
        $slug = empty($slug) ? null : $slug;
        $content = new Content($db);
        $user = new User($db);
        $author = $user->getAcronym();
        $params = array($title, $slug, $url, $data, $type, $filter, $author, $category, $published, $deleted, $id);
        $message = $content->updateContentInDb($params);
    }

    $res = $content->selectContent(array($id));

    $origo['debug'] = $db->Dump();
}

$blogAdminForm = new BlogAdminForm($db);

$origo['title'] = "Uppdatera nyheter";
$origo['stylesheets'][] = 'css/form.css';

// Header
$origo['main'] = <<<EOD
<h1>{$origo['title']}</h1>
{$blogAdminForm->createEditNewsBlogInDbForm($origo['title'], $res, $message)}
EOD;

// Finally, leave it all to the rendering phase of Origo.
include(ORIGO_THEME_PATH);
