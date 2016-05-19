<?php
class CalendarMonth
{
    const NUM_OF_CALENDER_COLUMNS = 7;
    const NO_DAYS_FEBRUARY = 28;
    const FIRST_CHUNK_POS = 0;
    const WEEK_NUMBER_POS = 0;
    const FIRST_ARRAY_CHUNK = 1;
    const JANUARY = 1;
    const DECEMBER = 12;

    private $year; // Needed to check which year the month belongs to.
    private $monthName;
    private $month;
    private $weeks;
    private $imageName;
    private $datesInCalendarMonth;

    private $swedishMonths = [
        "",
        "Januari",
        "Februari",
        "Mars",
        "April",
        "Maj",
        "Juni",
        "Juli",
        "Augusti",
        "September",
        "Oktober",
        "November",
        "December",
    ];

    private $monthImages = [
        "",
        "january.jpg",
        "february.jpg",
        "march.jpg",
        "april.jpg",
        "may.jpg",
        "june.jpg",
        "july.jpg",
        "august.jpg",
        "september.jpg",
        "october.jpg",
        "november.jpg",
        "december.jpg",
    ];

    /**
     * Constructor
     *
     * Initiates the variables and creates an array of all days in the calendar
     * month. Uses the array to create all calendar weeks.
     *
     * @param integer $month the calendar month.
     * @param integer $year the calendar year.
     */
    public function __construct($month, $year)
    {
        $this->year = $year;
        $this->month = $month;
        $this->monthName = $this->swedishMonths[$month];
        $this->imageName = $this->monthImages[$month];
        $this->createTableDaysArray();
        $this->createCalendarWeeks();
    }

    /**
     * Helper function to create the table day array.
     *
     * Creates an array of dates, containing a calender months all dates. Dates
     * from previous month and next month are included, if it is necessary to
     * fill the first week and last week of the current month.
     *
     * @return void.
     */
    private function createTableDaysArray()
    {
        $this->datesInCalendarMonth = null;
        $this->getPreMonthDays();
        $this->getMonthDays();
        $this->getPostMonthDays();
    }

    /**
     * Helper function to fill the array of calendar days with dates from previous month.
     *
     * Fills the array with dates from the previous month if the current months
     * does not start on a Monday.
     *
     * @return void.
     */
    private function getPreMonthDays()
    {
        $prevMonthDays = CalendarTime::getMonthNumOfDays($this->getPreviousMonth(), $this->year);
        $numPrevDays = CalendarTime::getNumOfPrevDays($this->month, $this->year);
        $startDay = $prevMonthDays - $numPrevDays + 1;
        $endDay = $prevMonthDays;
        for ($i=$startDay; $i <= $endDay ; $i++) {
            $this->datesInCalendarMonth[] = array($i, 'other-month');
        }
    }

    /**
     * Gets the previous months.
     *
     * Returns the value of the previous month. If the current month is January, the
     * previous month is set to the value for December (turns around).
     *
     * @return integer the value for the previous month (1 - 12).
     */
    public function getPreviousMonth()
    {
        $prevMonth = $this->month - 1;
        if ($prevMonth < self::JANUARY) {
            $prevMonth = self::DECEMBER;
        }

        return $prevMonth;
    }

    /**
     * Helper function to fill the array of calendar days with the dates for the
     * current month.
     *
     * Fills the array of calendar days with the dates for the current month.
     *
     * @return void.
     */
    private function getMonthDays()
    {
        $daysInMonth = CalendarTime::getMonthNumOfDays($this->month, $this->year);
        for ($i=1; $i <= $daysInMonth; $i++) {
            $this->datesInCalendarMonth[] = array($i, '');
        }
    }

    /**
     * Helper function to fill the array of calendar days with dates from next month.
     *
     * Fills the array with dates from the next month if the current months
     * does not ends on a Sunday.
     *
     * @return void.
     */
    private function getPostMonthDays()
    {
        $postDaysInMonth = CalendarTime::getNumOPostDays($this->month, $this->year);
        for ($i=1; $i <= $postDaysInMonth; $i++) {
            $this->datesInCalendarMonth[] = array($i, 'other-month');
        }
    }

    /**
     * Helper function to create all calendar weeks.
     *
     * The array, which contains all dates for the calendar month, is dived in
     * parts of seven days (week). The devided chunks is sent to CalendarWeek
     * with the week number to create the weeks.
     *
     * @return void.
     */
    private function createCalendarWeeks()
    {
        $chunkNo = 1;
        $chunks = array_chunk($this->datesInCalendarMonth, self::NUM_OF_CALENDER_COLUMNS);
        foreach ($chunks as $chunk) {
            $weekNo = $this->getWeekNumber($chunk[self::FIRST_CHUNK_POS][self::WEEK_NUMBER_POS], $chunkNo);
            $this->weeks[] = new CalendarWeek($weekNo, $chunk);
            $chunkNo++;
        }
    }

    /**
     * Helper function to get the week number for the calendar weeks.
     *
     * Returns the week number, which the day belongs to.
     *
     * @param  integer $day the day to get the week number it belongs to.
     * @param  integer $chunkNo the number of the chunk where the day is stored.
     *
     * @return integer the week number for the day.
     */
    private function getWeekNumber($day, $chunkNo)
    {
        if ($this->isDaysInPrevMonth($day, $chunkNo)) {
            $weekNo = $this->getWeekNumberPrevMonth($day);
        } else {
            $weekNo = date('W', strtotime("$this->year-$this->month-$day"));
        }

        return $weekNo;
    }

    /**
     * Helper function to check if the day belongs to the previous month.
     *
     * Checks if the day belongs to the previous month. If the date is 23 or
     * more (cound be the last week of February) and is in the first, it is
     * regarded as belonging to the previous month.
     * Makes it possible to separate days in previous month from days in the
     * current month.
     *
     * @param integer  $day the day to check if it belongs to previous month.
     * @param integer  $chunkNo the number of the chunk where the day is stored.
     *
     * @return boolean  true if the day belongs to the previous month, otherwise false.
     */
    private function isDaysInPrevMonth($day, $chunkNo)
    {
        $isPrevDay = false;
        $minDayLastColumn = self::NO_DAYS_FEBRUARY - self::NUM_OF_CALENDER_COLUMNS;
        if ($day > $minDayLastColumn && $chunkNo == self::FIRST_ARRAY_CHUNK) {
            $isPrevDay = true;
        }

        return $isPrevDay;
    }

    /**
     * Helper method to get the week number for the previous month.
     *
     * Gets the week number for the first calendar week when the start day of the
     * calendar month belongs to a previous month. Handles if the start date
     * belongs to a month in a previous year.
     *
     * @param  integer $day gets the week number for the day that belongs to a
     *                      previous month.
     *
     * @return integer the week number for the day of a previous month.
     */
    private function getWeekNumberPrevMonth($day)
    {
        $month = $this->getPreviousMonth();
        if ($month == self::DECEMBER) {
            // Week number belongs to previous year
            $prevYear = $this->year - 1;
            $weekNo = date('W', strtotime("$prevYear-$month-$day"));
        } else {
            $weekNo = date('W', strtotime("$this->year-$month-$day"));
        }

        return $weekNo;
    }

    /**
     * Gets the number of the month.
     *
     * @return integer the number of the month.
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * Gets the name of the month in swedish.
     *
     * @return string the name of the month in swedish.
     */
    public function getNameOfMonth()
    {
        return $this->monthName;
    }

    /**
     * Gets the name of the image that belongs to the month.
     *
     * @return string the image name of the month.
     */
    public function getMonthImgName()
    {
        return $this->monthImages[$this->month];
    }

    /**
     * Gets the next months.
     *
     * Returns the value of the next month. If the current month is December, the
     * next month is set to the value for January (turns around).
     *
     * @return integer the value for the next month (1 - 12).
     */
    public function getNextMonth()
    {
        $nextMonth = $this->month + 1;
        if ($nextMonth > self::DECEMBER) {
            $nextMonth = self::JANUARY;
        }

        return $nextMonth;
    }

    /**
     * Gets an array of all week objects.
     *
     * @return [] all CalendarWeek objects.
     */
    public function getAllWeeks()
    {
        return $this->weeks;
    }

    /**
     * Gets the year.
     *
     * @return integer the year.
     */
    public function getYear()
    {
        return $this->year;
    }
}
