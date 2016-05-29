<?php
/**
 * This is a Origo pagecontroller for the movie page.
 *
 * Contains reports of each section of the course OOPHP.
 */
 include(__DIR__.'/config.php');

 // Get parameters
$id       = isset($_GET['id']) ? $_GET['id'] : null;
$acronym  = isset($_GET['acronym'])  ? $_GET['acronym']  : null;
$name     = isset($_GET['name'])  ? $_GET['name']  : null;
$hits     = isset($_GET['hits'])  ? $_GET['hits']  : 8;
$page     = isset($_GET['page'])  ? $_GET['page']  : 1;
$orderby  = isset($_GET['orderby']) ? strtolower($_GET['orderby']) : 'id';
$order    = isset($_GET['order'])   ? strtolower($_GET['order'])   : 'asc';

// Check that incoming parameters are valid
is_numeric($hits) or die('Check: Hits must be numeric.');
is_numeric($page) or die('Check: Page must be numeric.');

$parameters = array(
    'id' => $id,
    'acronym' => $acronym,
    'name' => $name,
    'hits' => $hits,
    'page' => $page,
    'orderby' => $orderby,
    'order' => $order
);

$db = new Database($origo['database']);
$userSearch = new UserSearch($db, $parameters);
$res = $userSearch->searchUser();
$userSearchForm = $userSearch->generateUserSearchForm();

$htmlTable = new HTMLTable();
$hitsPerPage = $htmlTable->getHitsPerPage(array(2, 4, 8), $hits);
$navigatePageBar = $htmlTable->getPageNavigationBar($hits, $page, $userSearch->getMaxNumPages());
$userTable = $htmlTable->generateUserTable($res, $navigatePageBar);
$row = $userSearch->getNumberOfRows();

$userAdminForm = new UserAdminForm();
$adminForm = $userAdminForm->generateUserAdminForm();

$origo['title'] = "Användarkonton";
$origo['stylesheets'][] = 'css/form.css';
$origo['stylesheets'][] = 'css/movie.css';

$origo['main'] = <<<EOD
    <h1>{$origo['title']}</h1>
    {$userSearchForm}
    {$adminForm}
    <div class='movie-table'>
        <div class='table-hits'>{$row} träffar. {$hitsPerPage}</div>
        {$userTable}
    </div>
EOD;

// Finally, leave it all to the rendering phase of Origo.
include(ORIGO_THEME_PATH);
