<?php
/**
 * This is a Origo pagecontroller to show news blog posts from database.
 *
 * Contains reports of each section of the course OOPHP.
 */
include(__DIR__.'/config.php');

define('NEWS_BLOG_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'news_blog');

// Get parameters
$slug   = isset($_GET['slug'])  ? $_GET['slug']  : null;
$hits     = isset($_GET['hits'])  ? $_GET['hits']  : 8;
$page     = isset($_GET['page'])  ? $_GET['page']  : 1;
$category = isset($_GET['category']) ? $_GET['category'] : null;
$acronym = isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;

is_numeric($hits) or die('Check: Hits must be numeric.');
is_numeric($page) or die('Check: Page must be numeric.');

$parameters = array(
    'slug' => $slug,
    'hits' => $hits,
    'page' => $page,
    'category' => $category
);

$db = new Database($origo['database']);
$textFilter = new TextFilter();
$blog = new Blog($db, $acronym);
$blogs = $blog->getBlogPostsFromSlug($parameters, $textFilter, $category);

$adminNewsBlogsForm = null;
$newsBlogCategories = null;
$tableHits = null;
$navigatePageBar = null;
if (!isset($slug)) {
    $row = $blog->getNumberOfBlogs();
    $htmlTable = new HTMLTable();
    $hitsPerPage = $htmlTable->getHitsPerPage(array(2, 4, 8), $hits);
    $tableHits =<<<EOD
        <div class='table-hits'>{$row} träffar. {$hitsPerPage}</div>
EOD;

    $newsBlogCategories = $blog->createNewsBlogCategoryForm();
    $navigatePageBar = $htmlTable->getPageNavigationBar($hits, $page, $blog->getMaxNumPages());
    $blogAdminForm = new BlogAdminForm();
    $adminNewsBlogsForm = $blogAdminForm->generateNewsBlogsAdminForm();
}

$pathParams = array('slug' => $slug, 'category' => $category);
$breadcrumb = new Breadcrumb($db, NEWS_BLOG_PATH, $pathParams, $menu);
$breadcrumbNav = $breadcrumb->createNewsBlogBreadcrumb();


$origo['title'] = "Nyheter";
$origo['stylesheets'][] = 'css/news_blog.css';
$origo['stylesheets'][] = 'css/form.css';
$origo['stylesheets'][] = 'css/breadcrumb.css';

$origo['debug'] = $db->Dump();

$origo['main'] = <<<EOD
<section>
{$breadcrumbNav}
<h1>{$origo['title']}</h1>
<div class='news-blog-table'>
    {$adminNewsBlogsForm}
    {$newsBlogCategories}
    {$tableHits}
</div>
{$blogs}
{$navigatePageBar}
</section>
EOD;

// Finally, leave it all to the rendering phase of Origo.
include(ORIGO_THEME_PATH);
