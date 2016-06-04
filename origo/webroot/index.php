<?php
/**
 * This is a Origo pagecontroller for the me page.
 *
 * Contains a short presentation of the author of this page.
 *
 */

// Include the essential config-file which also creates the $origo variable with its defaults.
include(__DIR__.'/config.php');

/**
 * Generates three movie items for new movies.
 *
 * Creates three movie items for the latest added movies in the database. The movie
 * items consists of an image for the move, the move title and an link to a
 * page to get more information about the moive.
 *
 * @param  FrontPage $frontPage the front page object.
 *
 * @return html HTML tags for the three latest added movies.
 */
function generateNewMovieItems($frontPage)
{
    $image = array('width' => 200, 'height' => 280, 'sharpen' => false);
    $parameters = array('hits' => 3, 'page' => 1, 'orderby' => 'published', 'order' => 'desc');
    $newMovies = $frontPage->generateHtmlTagsForMovieItems($parameters, $image, "new-movie");

    return $newMovies;
}

/**
 * Generates the last rented movie item.
 *
 * Creates movie items for the last rented movie in the database. The movie
 * items consists of an image for the move, the move title and an link to a
 * page to get more information about the moive.
 *
 * @param  FrontPage $frontPage the front page object.
 *
 * @return html HTML tags for the last rented movies.
 */
function generateLastRentedMovieItem($frontPage)
{
    $image = array('width' => 119, 'height' => 170, 'sharpen' => true);
    $parameters = array('hits' => 1, 'page' => 1, 'orderby' => 'rented', 'order' => 'desc');
    $lastRented = $frontPage->generateHtmlTagsForMovieItems($parameters, $image, "sidebar-movie");

    return $lastRented;
}

/**
 * Generates the most rented movie item.
 *
 * Creates movie items for the most rented movie in the database. The movie
 * items consists of an image for the move, the move title and an link to a
 * page to get more information about the moive.
 *
 * @param  FrontPage $frontPage the front page object.
 *
 * @return html HTML tags for the most rented movies.
 */
function generateMostRentedMovieItem($frontPage)
{
    $image = array('width' => 119, 'height' => 170, 'sharpen' => true);
    $parameters = array('hits' => 1, 'page' => 1, 'orderby' => 'rents', 'order' => 'desc');
    $mostRented = $frontPage->generateHtmlTagsForMovieItems($parameters, $image, "sidebar-movie");

    return $mostRented;
}

/**
 * Gets blog post.
 *
 * Gets three last news blog posts from the database.
 *
 * @param  Database $db the database object.
 *
 * @return html the three last news blog posts.
 */
function getLastAddedBlogPosts($db)
{
    $textFilter = new TextFilter();
    $blog = new Blog($db);
    $parameters = array('slug' => null, 'hits' => 3,
        'page' => 1,
        'category' => null
    );

    $blogs = $blog->getBlogPostsFromSlug($parameters, $textFilter);

    return $blogs;
}

$db = new Database($origo['database']);
$frontPage = new FrontPage($db);

$newMovies = generateNewMovieItems($frontPage);
$genres = $frontPage->generateGenresList();
$lastRented = generateLastRentedMovieItem($frontPage);
$mostRented = generateMostRentedMovieItem($frontPage);
$blogs = getLastAddedBlogPosts($db);


// Do it and store it all in variables in the Origo container.
$origo['title'] = "Hem";
$origo['stylesheets'][] = 'css/front_page.css';
$origo['stylesheets'][] = 'css/news_blog.css';

$origo['main'] = <<<EOD
<section>
    <h1>Välkommen till Rental Movies</h1>
    <div class="sidbar">
        <section class="genres-section">
            <h2>Genres</h2>
            {$genres}
        </section>
        <section class="most-rented">
            <h2>Mest hyrda</h2>
            {$mostRented}
        </section>
        <section class="latest-rented">
            <h2>Senast hyrda</h2>
            {$lastRented}
        </section>
    </div>
    <div class="main-container">
        <section class='new-movies'>
            <h2>Senaste filmer</h2>
            {$newMovies}
        </section>
        <section class='news'>
            <h2>Senaste nyheterna</h2>
            {$blogs}
        </section>
    </div>
</section>
EOD;

// Finally, leave it all to the rendering phase of Origo.
include(ORIGO_THEME_PATH);
