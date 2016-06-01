<?php
/**
 * This is a Origo pagecontroller for the movie page.
 *
 * Handles two variants of the presentation of movies in the database.
 * The first variant shows the movies in a table with possibility to order the
 * movies according to title, year or price. Both ascending and descending. The
 * presentation has a paging function, which the user can chose how many movies
 * per page should be shown.
 *
 * The second variant shows information about a movie. If the user has logged
 * in, it is possible to rent a movie.
 *
 * Both variants are supported by breadcrumb navigation.
 *
 * A user that has admin rights, could create, edit and delete movies in the
 * database.
 */
include(__DIR__.'/config.php');

define('MOIVE_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'movie');

/**
 * Adds characters to the title.
 *
 * Adds characters to the title
 *
 * @param string $title the title with characters added.
 */
function addCharToTitle($title, $char)
{
    $title = $char . $title;
    $title .= $char;

    return $title;
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
function generateBreadcrumbNavigation($db, $id, $genre, $menu)
{
    $pathParams = array('id' => $id, 'genre' => $genre);
    $breadcrumb = new Breadcrumb($db, MOIVE_PATH, $pathParams, $menu);
    $breadcrumbNav = $breadcrumb->createMovieBreadcrumb();

    return $breadcrumbNav;
}

/**
 * Generates movie table table container
 *
 * Creates a moive section with a search form to search for movies, an admin form
 * for users with admin rights, number of hits, hits per page and a table of
 * movies.
 *
 * The admin form contains two buttons. One to add new movies in database and
 * one button to reset the movie database.
 *
 * @param  html $movieSearchForm    a form to search for movies.
 * @param  html $adminForm          an admin form for user with admin rights only.
 * @param  int $row                 the number of movies from the search.
 * @param  int $hitsPerPage         how many movie items thas should be shown per page.
 * @param  html $movieTable the table with movie(s).
 *
 * @return html the movie table container.
 */
function generateMovieTableContentContainer($movieSearchForm, $adminForm, $row, $hitsPerPage, $movieTable)
{
    $movie = <<<EOD
        {$movieSearchForm}
        {$adminForm}
        <div class='movie-table'>
            <div class='table-hits'>{$row} träffar. {$hitsPerPage}</div>
            {$movieTable}
        </div>
EOD;

    return $movie;
}

/**
 * Generate movie content information view.
 *
 * Creates the movie content information view where the movie is presented in
 * detail with links to Imdb and Youtube. If the user has logged in, it will
 * be possible to rent the movie.
 *
 * @param  MovieAdminForm $movieAdminForm   the moive admin form object.
 * @param  [] $res                          details of the movie from the database.
 * @param  string $result                   the result of renting the movie.
 *
 * @return html  the movie content information view.
 */
function generateMovieContentInformationView($movieAdminForm, $res, $result)
{
    $movieContentView = new MovieContentView();
    $rentButton = $movieAdminForm->createRentMovieForm($res, $result);
    $movieContentInfo = $movieContentView->generateMovieContentView($res, $rentButton);

    return $movieContentInfo;
}

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
$substringChar = isset($_GET['substring'])  ? $_GET['substring'] : null;
$path = isset($_GET['path']) ? $_GET['path'] : null;

// Increase the possibility to get a match when the search for movie
// comes from the header search.
if (isset($substringChar)) {
    $title = addCharToTitle($title, $substringChar);
}

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

$db = new Database($origo['database']);
$movieAdminForm = new MovieAdminForm();

if ($id) {
    // Movie content information view.
    is_numeric($id) or die('Check: Id must be numeric.');
    $movieSearch = new MovieSearch($db, $parameters);
    $res = $movieSearch->searchMovie();
    $movie = generateMovieContentInformationView($movieAdminForm, $res, $result);

    $path = basename($_SERVER['PHP_SELF']) . "?id=$id";

} else {
    // Movie table with paging function.

    // Check that incoming parameters are valid
    is_numeric($hits) or die('Check: Hits must be numeric.');
    is_numeric($page) or die('Check: Page must be numeric.');
    is_numeric($year1) || !isset($year1)  or die('Check: Year must be numeric or not set.');
    is_numeric($year2) || !isset($year2)  or die('Check: Year must be numeric or not set.');

    $movieSearch = new MovieSearch($db, $parameters);
    $res = $movieSearch->searchMovie();
    if ($movieSearch->getNumberOfRows() == 1) {
        $id = $movieSearch->getIdForFirstMovie($res);
    }

    $movieSearchForm = $movieSearch->getMovieSearchForm();

    $paging = new Paging();
    $hitsPerPage = $paging->getHitsPerPage(array(2, 4, 8), $hits);
    $navigatePageBar = $paging->getPageNavigationBar($hits, $page, $movieSearch->getMaxNumPages());

    $htmlTable = new HTMLTable();
    $movieTable = $htmlTable->generateMovieTable($res, $navigatePageBar, $genre);
    $row = $movieSearch->getNumberOfRows();

    $sqlDebug = $db->Dump();

    $adminForm = $movieAdminForm->generateMovieAdminForm();

    $movie = generateMovieTableContentContainer($movieSearchForm, $adminForm, $row, $hitsPerPage, $movieTable);
    $path = basename($_SERVER['PHP_SELF']);
}

$breadcrumbNav = generateBreadcrumbNavigation($db, $id, $genre, $menu);

 // Do it and store it all in variables in the Origo container.
$origo['title'] = "Filmer";
$origo['stylesheets'][] = 'css/movie.css';
$origo['stylesheets'][] = 'css/movie_content.css';
$origo['stylesheets'][] = 'css/form.css';
$origo['stylesheets'][] = 'css/breadcrumb.css';

$origo['main'] = <<<EOD
{$breadcrumbNav}
<h1>{$origo['title']}</h1>
{$movie}
EOD;

// Finally, leave it all to the rendering phase of Origo.
include(ORIGO_THEME_PATH);
