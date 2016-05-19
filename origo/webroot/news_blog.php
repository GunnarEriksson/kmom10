<?php
/**
 * This is a Origo pagecontroller to show news blog posts from database.
 *
 * Contains reports of each section of the course OOPHP.
 */
include(__DIR__.'/config.php');


// Get parameters
$slug   = isset($_GET['slug'])  ? $_GET['slug']  : null;
$hits     = isset($_GET['hits'])  ? $_GET['hits']  : 8;
$page     = isset($_GET['page'])  ? $_GET['page']  : 1;
$acronym = isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;

is_numeric($hits) or die('Check: Hits must be numeric.');
is_numeric($page) or die('Check: Page must be numeric.');

$parameters = array(
    'slug' => $slug,
    'hits' => $hits,
    'page' => $page
);

$db = new Database($origo['database']);
$textFilter = new TextFilter();
$blog = new Blog($db, $acronym);
$blogs = $blog->getBlogPostsFromSlug($parameters, $textFilter);

$row = $blog->getNumberOfBlogs();
$htmlTable = new HTMLTable();
$hitsPerPage = $htmlTable->getHitsPerPage(array(2, 4, 8), $hits);
$navigatePageBar = $htmlTable->getPageNavigationBar($hits, $page, $blog->getMaxNumPages());



$origo['title'] = "Nyheter";
$origo['stylesheets'][] = 'css/news_blog.css';

$origo['debug'] = $db->Dump();

$origo['main'] = <<<EOD
<section>
<h1>{$origo['title']}</h1>
<div class='news-blog-table'>
    <div class='table-hits'>{$row} träffar. {$hitsPerPage}</div>
</div>
{$blogs}
{$navigatePageBar}
</section>
EOD;

// Finally, leave it all to the rendering phase of Origo.
include(ORIGO_THEME_PATH);
