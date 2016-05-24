<?php
/**
 * This is a Origo pagecontroller to delete a news blog post.
 *
 * Contains reports of each section of the course OOPHP.
 */
include(__DIR__.'/config.php');

$id      = isset($_POST['id'])      ? strip_tags($_POST['id']) : (isset($_GET['id']) ? strip_tags($_GET['id']) : null);
$delete  = isset($_POST['delete'])  ? true : false;
$erase  = isset($_POST['erase'])  ? true : false;
$acronym = isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;

is_numeric($id) or die('Check: Id must be numeric.');

$db = new Database($origo['database']);
$message = null;
$res = null;
if (isset($acronym)) {
    $content = new Content($db);
    $res = $content->selectContent(array($id));
    if (isset($res) && ((strcmp($acronym , 'admin') === 0) || (strcmp($acronym , $res->author) === 0))) {
        if ($delete) {
            $user = new User($db);
            $author = $user->getAcronym();
            $params = array(
                $res->title,
                $res->slug,
                $res->url,
                $res->data,
                $res->type,
                $res->filter,
                $author,
                $res->category,
                $res->updated,
                $id
            );

            $message = $content->deleteContent($params);

        } else if ($erase) {
            $params = array($id);

            $message = $content->eraseContent($params);
        }
    }

    $origo['debug'] = $db->Dump();
}

$blogAdminForm = new BlogAdminForm($db);

$origo['title'] = "Radera nyhet";
$origo['stylesheets'][] = 'css/form.css';

// Header
$origo['main'] = <<<EOD
<h1>{$origo['title']}</h1>
{$blogAdminForm->createDeleteNewsBlogInDbForm($origo['title'], $res, $message)}
EOD;

// Finally, leave it all to the rendering phase of Origo.
include(ORIGO_THEME_PATH);
