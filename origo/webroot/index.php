<?php
/**
 * This is a Origo pagecontroller for the me page.
 *
 * Contains a short presentation of the author of this page.
 *
 */

// Include the essential config-file which also creates the $origo variable with its defaults.
include(__DIR__.'/config.php');


// Do it and store it all in variables in the Origo container.
$origo['title'] = "Hem";

$origo['main'] = <<<EOD
<article>
    <h1>Välkommen till Rental Movies</h1>
    <p>Här kommer inom snart en presentation vad Rental Movies kan erbjuda.</p>
</article>
EOD;

// Finally, leave it all to the rendering phase of Origo.
include(ORIGO_THEME_PATH);
