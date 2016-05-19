<?php
/**
 * Handles the time units for the calendar.
 */
class CalendarTime
{
    const NUM_OF_CALENDER_COLUMNS = 7;

    /**
     * Static function to get the number of days in a month.
     *
     * Returns the number of days in a month according to the Gregorian
     * calendar.
     *
     * @param  integer $month the month as an value between one and twelve.
     * @param  integer $year the year as four digits.
     * @return integer the number of days for the specific month.
     */
    public static function getMonthNumOfDays($month, $year)
    {
        return cal_days_in_month(CAL_GREGORIAN, $month, $year);
    }

    /**
     * Gets the number of days in previous month for the first week of current month.
     *
     * Returns the number of days of the current months first week that belongs
     * to the previous month. For example if the first day of a month starts on
     * Wednesday, the result from the function is two because Monday and Tuesday
     * belongs to the previous month.
     *
     * @param  integer $month the current month
     * @param  integer $year the current year.
     *
     * @return integer the number of days of the current months first week that
     *                 belongs to the previous month.
     */
    public static function getNumOfPrevDays($month, $year)
    {
        return date('w', mktime(0, 0, 0, $month, 0, $year));
    }

    /**
     * Gets the number of days in next month for the last week of current month.
     *
     * Returns the number of days of the current months last week that belongs
     * to the next month. For example if the last day of a month ends at
     * Thursday, the result from the function is three because Friday, Saturday
     * and Sunday belongs to the next month. If the the result is seven or more,
     * the number of days is set to zero to prevent a whole week with dates of
     * the next month.
     *
     * @param  integer $month the current month
     * @param  integer $year the current year.
     *
     * @return integer the number of days of the current months last week that
     *                 belongs to the next month.
     */
    public static function getNumOPostDays($month, $year)
    {
        $numOfDaysInMonth = self::getMonthNumOfDays($month, $year);
        $postDays = (self::NUM_OF_CALENDER_COLUMNS - (date('w', mktime(0, 0, 0, $month, $numOfDaysInMonth, $year))));
        // Prevent whole row of post days to be written.
        if ($postDays >= self::NUM_OF_CALENDER_COLUMNS) {
            $postDays = 0;
        }
        return $postDays;
    }
}
