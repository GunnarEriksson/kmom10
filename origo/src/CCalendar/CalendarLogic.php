<?php
/**
 * Handles the logic for the calendar
 */
class CalendarLogic
{
    const NUM_OF_CALENDER_ROWS = 5;
    const NUM_OF_CALENDER_COLUMNS = 7;
    const DATE_POS = 0;
    const CLASS_POS = 1;
    const JANUARY = 1;
    const DECEMBER = 12;
    const NO_DAYS_FEBRUARY = 28;

    private $calendarMonth;

    /**
     * Constructor
     *
     * Gets the current time stamp and creates the calendar month.
     */
    public function __construct()
    {
        $timeStamp = time();
        $month = date('n', $timeStamp);
        $year = date('Y', $timeStamp);
        $this->calendarMonth = new CalendarMonth($month, $year);
    }

    /**
     * Updates the calendar.
     *
     * Updates the calendar with the month that is send as an argument. Increases
     * or decreases the year if the month belongs to the next or previous year.
     * New calendar month is created.
     *
     * @param  integer $month the month that should be shown i calendar.
     *
     * @return void.
     */
    public function updateCalendar($month)
    {
        $currentMonth = $this->calendarMonth->getMonth();
        $currentYear = $this->calendarMonth->getYear();
        if ($currentMonth == self::DECEMBER && $month == self::JANUARY) {
            $currentYear++;
        } else if ($currentMonth == self::JANUARY && $month == self::DECEMBER) {
            $currentYear--;
        } else {
            // Do nothing. It is the same year.
        }

        $this->calendarMonth = new CalendarMonth($month, $currentYear);
    }

    /**
     * Gets the table head of the calendar.
     *
     * Creates and return the table header of the calendar, containing week and
     * the days of the week.
     *
     * @return html the table header of the calendar.
     */
    public function getTableHeader()
    {
        $tableHead = null;
        $tableHead .= "<thead>";
        $tableHead .= "<tr>";
        $tableHead .= "<th>Vecka</th>";
        $tableHead .= "<th>Måndag</th>";
        $tableHead .= "<th>Tisdag</th>";
        $tableHead .= "<th>Onsdag</th>";
        $tableHead .= "<th>Torsdag</th>";
        $tableHead .= "<th>Fredag</th>";
        $tableHead .= "<th>Lördag</th>";
        $tableHead .= "<th>Söndag</th>";
        $tableHead .= "</tr>";
        $tableHead .= "</thead>";

        return $tableHead;
    }

    /**
     * Gets the table body of the calendar.
     *
     * Creates and returns the table body of the calendar, containing week
     * numbers and all dates in the calendar month. Dates from previous and next
     * month included if it is necessary to fill out the first and last week of
     * the calendar month.
     *
     * @return html the table body of the calendar.
     */
    public function getTableBody()
    {
        $weeks = $this->calendarMonth->getAllWeeks();
        $tableBody = "<tbody>";
        foreach ($weeks as $week) {
            $tableBody .= "<tr>";
            $tableBody .= '<td>' . $week->getWeekNumber() . '</td>';
            $tableBody .= $this->getTableRowDays($week);
            $tableBody .= "</tr>";
        }
        $tableBody .= "</tbody>";

        return $tableBody;
    }

    /**
     * Helper function to get all days for a week.
     *
     * Creates table of days for a week from the week object.
     *
     * @param CalendarWeek $week the week object with all days and infor of the week.
     *
     * @return html the table rows of days for a week.
     */
    private function getTableRowDays($week)
    {
        $tableRow = null;
        $days = $week->getAllDays();
        foreach ($days as $day) {
            $tableRow .= "<td class='{$day->getInfo()}'>{$day->getDate()}</td>";
        }

        return $tableRow;
    }

    /**
     * Get the current year.
     *
     * Returns the current year.
     *
     * @return integer the current year.
     */
    public function getYear()
    {
        return  $this->calendarMonth->getYear();
    }

    /**
     * Gets the previous month.
     *
     * Returns the value for the next month. If the month is January, the
     * value is set to the value of December (turns around).
     *
     * @return integer the previous month to the month that is shown in the
     *                 calendar (1 - 12).
     */
    public function getPreviousMonth()
    {
        return $this->calendarMonth->getPreviousMonth();
    }

    /**
     * Gets the name of current month in swedish.
     *
     * Returns the name of the current month in swedish.
     *
     * @return string the name of the current month in swedish.
     */
    public function getMonth()
    {
        return $this->calendarMonth->getNameOfMonth();
    }

    /**
     * Gets the value of the next month.
     *
     * Returns the value for the next month. If the month is December, the
     * value is set to the value of January (turns around).
     *
     * @return integer the value of the next month (1 - 12).
     */
    public function getNextMonth()
    {
        return $this->calendarMonth->getNextMonth();
    }

    /**
     * Gets the image name of the current month.
     *
     * Returns the image name of the current month.
     *
     * @return string the image name of the current month.
     */
    public function getMonthImgName()
    {
        return $this->calendarMonth->getMonthImgName();
    }
}
