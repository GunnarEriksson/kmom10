<?php
/**
 * A dice class to handle the dice.
 */
class Dice
{
    private $faces;
    protected $value;

    /**
     * Constructor
     *
     * Sets the number of faces
     *
     * @param integer $faces the number of faces on the dice. Default is six.
     */
    public function __construct($faces=6)
    {
        $this->faces = $faces;
    }

    /**
     * Roll the dice.
     *
     * Sets the of the dice to a random number between one and the
     * number of faces.
     *
     * @return integer the result of the roll. A number between one and the
     *                 number of faces.
     */
    public function roll()
    {
        $this->value = rand(1, $this->faces);

        return $this->value;
    }
}
