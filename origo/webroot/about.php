<?php
/**
 * This is a Origo pagecontroller for about RM page.
 *
 * Contains a short presentation of the company.
 *
 */

// Include the essential config-file which also creates the $origo variable with its defaults.
include(__DIR__.'/config.php');


// Do it and store it all in variables in the Origo container.
$origo['title'] = "Om RM";

$origo['main'] = <<<EOD
<h1>{$origo['title']}</h1>
<p>Rental Movies är ett företag som har sysslat med uthyrning av film under 15 år och
när vi var som störst hade 125 butiker över hela landet. Vi var tidigt ute att erbjuda
våra kunder att hyra film över nätet, först med att skicka filmer och sedan kunna
erbjuda våra kunder att ladda ner filmer online. 2011 ställde vi om våran
affärsverksamhet och sålde våra fysiska butiker för att enbart erbjuda filmer online.</p>

<p>Vi kan erbjuda dig ett stort utbud av filmer och genom ett unikt sammarbete med de
största filmbolagen kan vi erbjuda dig de senaste filmerna före flera av våra konkurrenter.</p>

<p>Vi vill göra det lätt för dig som älskar film att få tillgång till de filmer som
du vill se, oavsett var du bor i landet. Du behöver inte åka iväg för att se dina
favoritfilmer, utan du kan lugnt sitta i din soffa eller favoritfotölj och välja vilka
filmer du vill se. Laddar du ner en film, så har du tillgång till filmen under 24 timmar.
Laddar du ner 2 filmer, så har du tillgång till filmerna under 48 timmar o s v.</p>

<p>Välkommen till oss på Rental Movies<p>
EOD;

// Finally, leave it all to the rendering phase of Origo.
include(ORIGO_THEME_PATH);
