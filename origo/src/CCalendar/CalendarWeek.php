<?php
/**
 * Handles the week in the calendar.
 */
class CalendarWeek
{
    const DATE = 0;
    const INFORMATION = 1;

    private $weekNumber;
    private $days;

    /**
     * Constructor
     *
     * Sets the number of the week and adds the days to the week.
     *
     * @param integer $weekNumber the week number.
     * @param [] $weekOfDays the days that belongs to the week.
     */
    public function __construct($weekNumber, $weekOfDays)
    {
        $this->weekNumber = $weekNumber;
        $this->addDaysToWeek($weekOfDays);
    }

    /**
     * Add days to the week.
     *
     * Creates the days that belongs to the week.
     *
     * @param [] $weekOfDays the days of the week containing date and information.
     */
    public function addDaysToWeek($weekOfDays)
    {
        foreach ($weekOfDays as $day) {
            $this->days[] = new CalendarDay($day[self::DATE], $day[self::INFORMATION]);
        }
    }

    /**
     * Gets the week number of the week.
     *
     * @return integer the week number.
     */
    public function getWeekNumber()
    {
        return $this->weekNumber;
    }

    /**
     * Gets an array of all day objects.
     *
     * @return [] all CalendarDay objects.
     */
    public function getAllDays()
    {
        return $this->days;
    }
}
