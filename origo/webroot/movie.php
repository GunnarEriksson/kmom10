<?php
/**
 * This is a Origo pagecontroller for the movie page.
 *
 * Contains reports of each section of the course OOPHP.
 */
 include(__DIR__.'/config.php');

 define('MOIVE_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'movie');

 // Get parameters
$id       = isset($_GET['id']) ? $_GET['id'] : null;
$title    = isset($_GET['title']) ? $_GET['title'] : null;
$hits     = isset($_GET['hits'])  ? $_GET['hits']  : 8;
$page     = isset($_GET['page'])  ? $_GET['page']  : 1;
$year1    = isset($_GET['year1']) && !empty($_GET['year1']) ? $_GET['year1'] : null;
$year2    = isset($_GET['year2']) && !empty($_GET['year2']) ? $_GET['year2'] : null;
$orderby  = isset($_GET['orderby']) ? strtolower($_GET['orderby']) : 'id';
$order    = isset($_GET['order'])   ? strtolower($_GET['order'])   : 'asc';
$genre    = isset($_GET['genre']) ? $_GET['genre'] : null;
$result   = isset($_GET['result'])  ? $_GET['result'] : null;
$path = isset($_GET['path']) ? $_GET['path'] : null;

$db = new Database($origo['database']);
$movieAdminForm = new MovieAdminForm();

if ($id) {
    $parameters = array(
        'id' => $id,
        'title' => $title,
        'hits' => $hits,
        'page' => $page,
        'year1' => $year1,
        'year2' => $year2,
        'orderby' => $orderby,
        'order' => $order,
        'genre' => $genre,
    );

    $movieSearch = new MovieSearch($db, $parameters);
    $res = $movieSearch->searchMovie();
    $movieContentView = new MovieContentView();
    $rentButton = $movieAdminForm->createRentMovieForm($res, $result);
    $movie = $movieContentView->generateMovieContentView($res, $rentButton);
    $path = basename($_SERVER['PHP_SELF']) . "?id=$id";

} else {

    // Check that incoming parameters are valid
    is_numeric($hits) or die('Check: Hits must be numeric.');
    is_numeric($page) or die('Check: Page must be numeric.');
    is_numeric($year1) || !isset($year1)  or die('Check: Year must be numeric or not set.');
    is_numeric($year2) || !isset($year2)  or die('Check: Year must be numeric or not set.');

    $parameters = array(
        'id' => $id,
        'title' => $title,
        'hits' => $hits,
        'page' => $page,
        'year1' => $year1,
        'year2' => $year2,
        'orderby' => $orderby,
        'order' => $order,
        'genre' => $genre,
    );

    $movieSearch = new MovieSearch($db, $parameters);
    $res = $movieSearch->searchMovie();
    if ($movieSearch->getNumberOfRows() == 1) {
        $id = $movieSearch->getIdForFirstMovie($res);
    }

    $movieSearchForm = $movieSearch->getMovieSearchForm();

    $htmlTable = new HTMLTable();
    $hitsPerPage = $htmlTable->getHitsPerPage(array(2, 4, 8), $hits);
    $navigatePageBar = $htmlTable->getPageNavigationBar($hits, $page, $movieSearch->getMaxNumPages());
    $movieTable = $htmlTable->generateMovieTable($res, $navigatePageBar, $genre);
    $row = $movieSearch->getNumberOfRows();

    $sqlDebug = $db->Dump();

   $adminForm = $movieAdminForm->generateMovieAdminForm();

    $movie = <<<EOD
        {$movieSearchForm}
        {$adminForm}
        <div class='movie-table'>
            <div class='table-hits'>{$row} träffar. {$hitsPerPage}</div>
            {$movieTable}
        </div>
EOD;

    $path = basename($_SERVER['PHP_SELF']);
}

$pathParams = array('id' => $id, 'genre' => $genre);
$breadcrumb = new Breadcrumb($db, MOIVE_PATH, $pathParams, $menu);
$breadcrumbNav = $breadcrumb->createMovieBreadcrumb();

 // Do it and store it all in variables in the Origo container.
$origo['title'] = "Filmer";
$origo['stylesheets'][] = 'css/movie.css';
$origo['stylesheets'][] = 'css/movie_content.css';
$origo['stylesheets'][] = 'css/form.css';
$origo['stylesheets'][] = 'css/breadcrumb.css';

$origo['main'] = <<<EOD
    <article>
        {$breadcrumbNav}
        <h1>{$origo['title']}</h1>
        {$movie}
    </article>
EOD;

// Finally, leave it all to the rendering phase of Origo.
include(ORIGO_THEME_PATH);
