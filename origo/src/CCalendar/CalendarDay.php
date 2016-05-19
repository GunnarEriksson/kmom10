<?php
/**
 * Handles the day in the calendar.
 */
class CalendarDay
{
    private $date;
    private $info;

    /**
     * Constructor
     *
     * Sets the parameters date and if the date belongs to the current month.
     *
     * @param integer  $date the date of the day.
     * @param string $info the information of the day, for example that the
     *                     day belongs to another month.
     */
    public function __construct($date, $info)
    {
        $this->date = $date;
        $this->info = $info;
    }

    /**
     * Gets the date of the day.
     *
     * Returns the date of the day.
     *
     * @return integer the date of the day (1 - 31).
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Returns the information about the day
     *
     * @return string the information about the day.
     */
    public function getInfo()
    {
        return $this->info;
    }
}
