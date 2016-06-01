<?php
/**
 * This is a Origo pagecontroller to show news blog posts from database.
 *
 * Handles the presentation of news blog post(s). The presentation has support
 * for breadcrumb navigation and paging, where the user can chose how many
 * blog posts should be shown per page.
 *
 * If a user has logged in, the user can create new blog posts and edit blog post
 * the user has created.
 *
 * If user has admin rights, the user can create, edit and delete all blog posts.
 */
include(__DIR__.'/config.php');

define('NEWS_BLOG_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'news_blog');

/**
 * Generates page hits bar.
 *
 * Generate a page hits bar with the functions show how many hits the search
 * gave and a possibilty to chose how many blog post items should be shown
 * per page. Possible number of blog post to show per page is 2, 4 or 8.
 *
 * @param  Blog $blog   the blog object.
 * @param  HTMLTable    $htmlTable the HTML table object.
 * @param  int $hits    the number of choosen hits (2, 4 or 8).
 *
 * @return html the page hits bar.
 */
function generatePageHitsBar($blog, $htmlTable, $hits)
{
    $row = $blog->getNumberOfBlogs();
    $hitsPerPage = $htmlTable->getHitsPerPage(array(2, 4, 8), $hits);
    $tableHits =<<<EOD
        <div class='table-hits'>{$row} träffar. {$hitsPerPage}</div>
EOD;

    return $tableHits;
}

/**
 * Generates breadcrumb navigation.
 *
 * Creates a breadcrumb navigation list.
 *
 * @param  Database $db     the database object.
 * @param  int $id          the id of the moive.
 * @param  string $genre    the movie genre.
 * @param  [] $menu         the navigation bar menus from the config file.
 *
 * @return html the breadcrumb navigation list.
 */
 function generateBreadcrumbNavigation($db, $parameters, $menu)
 {
     $pathParams = array('slug' => $parameters['slug'], 'category' => $parameters['category']);
     $breadcrumb = new Breadcrumb($db, NEWS_BLOG_PATH, $pathParams, $menu);
     $breadcrumbNav = $breadcrumb->createNewsBlogBreadcrumb();

     return $breadcrumbNav;
 }

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
$tableHitsBar = null;
$navigatePageBar = null;
if (!isset($slug)) {
    $row = $blog->getNumberOfBlogs();
    $htmlTable = new HTMLTable();
    $tableHitsBar = generatePageHitsBar($blog, $htmlTable, $hits);

    $newsBlogCategories = $blog->createNewsBlogCategoryForm();
    $navigatePageBar = $htmlTable->getPageNavigationBar($hits, $page, $blog->getMaxNumPages());
    $blogAdminForm = new BlogAdminForm();
    $adminNewsBlogsForm = $blogAdminForm->generateNewsBlogsAdminForm();
}

$breadcrumbNav = generateBreadcrumbNavigation($db, $parameters, $menu);


$origo['title'] = "Nyheter";
$origo['stylesheets'][] = 'css/news_blog.css';
$origo['stylesheets'][] = 'css/form.css';
$origo['stylesheets'][] = 'css/breadcrumb.css';

$origo['debug'] = $db->Dump();

$origo['main'] = <<<EOD
{$breadcrumbNav}
<h1>{$origo['title']}</h1>
<div class='news-blog-table'>
    {$adminNewsBlogsForm}
    {$newsBlogCategories}
    {$tableHitsBar}
</div>
{$blogs}
{$navigatePageBar}
EOD;

// Finally, leave it all to the rendering phase of Origo.
include(ORIGO_THEME_PATH);
