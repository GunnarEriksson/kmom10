<?php
/**
 * This is a Origo pagecontroller for the me page.
 *
 * Contains a short presentation of the author of this page.
 *
 */

// Include the essential config-file which also creates the $origo variable with its defaults.
include(__DIR__.'/config.php');

$db = new Database($origo['database']);

$parameters = array('hits' => 3, 'page' => 1, 'orderby' => 'published', 'order' => 'desc');
$frontPage = new FrontPage($db);
$newMovies = $frontPage->generateMovieSections($parameters, null, "new-movie");

$genres = $frontPage->generateGenresList();

$image = array('width' => 71, 'height' => 100, 'sharpen' => true);
$parameters = array('hits' => 1, 'page' => 1, 'orderby' => 'rented', 'order' => 'desc');
$lastRented = $frontPage->generateMovieSections($parameters, $image, "sidebar-movie");

$parameters = array('hits' => 1, 'page' => 1, 'orderby' => 'rents', 'order' => 'desc');
$mostRented = $frontPage->generateMovieSections($parameters, $image, "sidebar-movie");

$textFilter = new TextFilter();
$blog = new Blog($db, null);
$parameters = array('slug' => null, 'hits' => 3,
    'page' => 1,
    'category' => null
);
$blogs = $blog->getBlogPostsFromSlug($parameters, $textFilter);



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
