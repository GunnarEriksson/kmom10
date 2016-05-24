<?php
/**
 * This is a Origo pagecontroller to reset the content for news blog posts
 *
 * Contains reports of each section of the course OOPHP.
 */
include(__DIR__.'/config.php');

$reset = isset($_POST['reset']) ? true : false;

$message = null;
if ($reset) {
    $db = new Database($origo['database']);
    $content = new Content($db);
    $message = $content->resetContentInDb();
    $origo['debug'] = $db->Dump();
}

$blogAdminForm = new BlogAdminForm();


$origo['title'] = "Återställ databasen för nyheter";
$origo['stylesheets'][] = 'css/form.css';

$origo['main'] = <<<EOD
<h1>{$origo['title']}</h1>
{$blogAdminForm->createResetNewsBlogsDbForm($origo['title'], $message)}
EOD;

// Finally, leave it all to the rendering phase of Origo.
include(ORIGO_THEME_PATH);
