<?php
/**
 * This is a Origo pagecontroller for the user admin page.
 *
 * Handles the presentations of the user profiles. The user profiles are shown
 * in a table which has support for paging and sortning the user profiles.
 *
 * If the user has user rights (has logged in and is not admin), the user can
 * update user profiles created by the user.
 *
 * If the user has admin rights (logged in as admin), the user can edit all
 * user profiles and delete all user profiles except the admin profile.
 * The administrator can not edit the administrator acronym and name.
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

$paging = new Paging();
$hitsPerPage = $paging->getHitsPerPage(array(2, 4, 8), $hits);
$navigatePageBar = $paging->getPageNavigationBar($hits, $page, $userSearch->getMaxNumPages());

$htmlTable = new HTMLTable();
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
