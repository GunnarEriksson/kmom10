<?php
/**
 * This is a Origo pagecontroller to edit content for pages and blog posts
 *
 * Edits a blog post from the news page. If user has admin rights, all blog
 * posts can be edited. If the user has user rights, only blog posts that the
 * user has created can be edited.
 */
include(__DIR__.'/config.php');

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

$db = new Database($origo['database']);
$blogAdminForm = new BlogAdminForm($db);
$message = null;
$res = null;

if (isset($acronym)) {
    $content = new Content($db);

    if ($save && ((strcmp($acronym , 'admin') === 0) || (strcmp($acronym , $author) === 0))) {
        $url = empty($url) ? null : $url;
        $slug = empty($slug) ? null : $slug;
        $user = new User($db);
        $author = $user->getAcronym();
        $params = array($title, $slug, $url, $data, $type, $filter, $author, $category, $published, $deleted, $id);
        $message = $content->updateContent($params);
    }

    $res = $content->selectContent(array($id));

    $origo['debug'] = $db->Dump();
}

$origo['title'] = "Uppdatera nyheter";
$origo['stylesheets'][] = 'css/form.css';

// Header
$origo['main'] = <<<EOD
<h1>{$origo['title']}</h1>
{$blogAdminForm->createEditNewsBlogInDbForm($origo['title'], $res, $message)}
EOD;

// Finally, leave it all to the rendering phase of Origo.
include(ORIGO_THEME_PATH);
