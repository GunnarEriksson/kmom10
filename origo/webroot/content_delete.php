<?php
/**
 * This is a Origo pagecontroller to delete a news blog post.
 *
 * Deletes a blog post from the news page. If user has admin rights, all blog
 * posts can be deleted. If the user has user rights, only blog posts that the
 * user has created can be deleted.
 *
 * The user has two choices to delete a blog post. If a blog post is deleted, the
 * blog post is still in the database and can be recreated. If a blog post is
 * erased, it is removed from the database and can NOT be recreated.
 */
include(__DIR__.'/config.php');

/**
 * Set content parameters.
 *
 * Stores the content parameters in an array.
 *
 * @param [] $res the content result from database.
 *
 * @return [] array with content parameters.
 */
function setResultParameters($res)
{
    $params = array(
        $res->title,
        $res->slug,
        $res->url,
        $res->data,
        $res->type,
        $res->filter,
        $res->author,
        $res->category,
        $res->updated,
        $res->id
    );

    return $params;
}

$id      = isset($_POST['id'])      ? strip_tags($_POST['id']) : (isset($_GET['id']) ? strip_tags($_GET['id']) : null);
$delete  = isset($_POST['delete'])  ? true : false;
$erase  = isset($_POST['erase'])  ? true : false;
$acronym = isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;

is_numeric($id) or die('Check: Id must be numeric.');

$db = new Database($origo['database']);
$blogAdminForm = new BlogAdminForm($db);
$message = null;
$res = null;

if (isset($acronym)) {
    $content = new Content($db);
    $res = $content->selectContent(array($id));

    // Admin can delete all contents and user can delete own contents
    if (isset($res) && ((strcmp($acronym , 'admin') === 0) || (strcmp($acronym , $res->author) === 0))) {
        if ($delete) {
            $params = setResultParameters($res);
            $message = $content->deleteContent($params);

        } else if ($erase) {
            $params = array($id);

            $message = $content->eraseContent($params);
        }
    }

    $origo['debug'] = $db->Dump();
}

$origo['title'] = "Radera nyhet";
$origo['stylesheets'][] = 'css/form.css';

// Header
$origo['main'] = <<<EOD
<h1>{$origo['title']}</h1>
{$blogAdminForm->createDeleteNewsBlogInDbForm($origo['title'], $res, $message)}
EOD;

// Finally, leave it all to the rendering phase of Origo.
include(ORIGO_THEME_PATH);
