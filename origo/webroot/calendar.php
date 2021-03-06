<?php
/**
 * This is a Origo pagecontroller for the calendar page.
 *
 * Contains a calendar view per month with a picture of the movie of the month.
 */
include(__DIR__.'/config.php');

if(empty($_GET)) {
  // Unset the session variable.
  unset($_SESSION['calendar']);
}

// Create the object or get it from the session
if(isset($_SESSION['calendar'])) {
  $calendar = $_SESSION['calendar'];
}
else {
  $calendar = new CalendarLogic();
  $_SESSION['calendar'] = $calendar;
}

$month = isset($_GET['month']) ? $_GET['month'] : null ;
if (isset($month)) {
    $calendar->updateCalendar($month);
}

// Do it and store it all in variables in the Origo container.
$origo['title'] = "Kalender";
// Add style for csource
$origo['stylesheets'][] = 'css/calendar.css';
$origo['main'] = <<<EOD
<h1>{$origo['title']}</h1>
<div class="calendar-wrapper">
    <div class="calendar-header">
        <img src="img.php?src=calendar/{$calendar->getMonthImgName()}&amp;width=980&amp;height=500" alt="Bild för månaden {$calendar->getMonth()}"/>
        <a href="?month={$calendar->getPreviousMonth()}">&lt;&lt;</a>
        <h2 class="calendar-date">{$calendar->getMonth()} {$calendar->getYear()}</h2>
        <a class="text-align-right" href="?month={$calendar->getNextMonth()}">>></a>
    </div>
    <table class='calendar'>
        {$calendar->getTableHeader()}
        {$calendar->getTableBody()}
    </table>
</div>
EOD;

// Finally, leave it all to the rendering phase of Origo.
include(ORIGO_THEME_PATH);
