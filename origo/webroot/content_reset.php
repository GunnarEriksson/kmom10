<?php
/**
 * This is a Origo pagecontroller to reset the content for news blog posts
 *
 * If user has admin rights (logged in as admin), the blog posts in database
 * is reset to the default values.
 */
include(__DIR__.'/config.php');

/**
 * Resets database.
 *
 * Gets a connection with the database and sends a request to content to
 * reset content in the database. Returns a message of the result.
 *
 * @param [] dbConfig the database configuration.
 *
 * @return a message of the result to reset content in the database.
 */
function resetDb($dbConfig)
{
    $db = new Database($dbConfig);
    $content = new Content($db);
    $message = $content->resetContentInDb();
    $origo['debug'] = $db->Dump();

    return $message;
}

$reset = isset($_POST['reset']) ? true : false;
$acronym = isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;

$blogAdminForm = new BlogAdminForm();

$message = null;
if ($reset && isset($acronym) && strcmp($acronym , 'admin') === 0) {
    $message = resetDb($origo['database']);
}

$origo['title'] = "Återställ databasen för nyheter";
$origo['stylesheets'][] = 'css/form.css';

$origo['main'] = <<<EOD
<h1>{$origo['title']}</h1>
{$blogAdminForm->createResetNewsBlogsDbForm($origo['title'], $message)}
EOD;

// Finally, leave it all to the rendering phase of Origo.
include(ORIGO_THEME_PATH);
