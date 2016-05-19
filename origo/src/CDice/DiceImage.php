<?php
/**
 *  A dice with images as graphical representation.
 */
class DiceImage extends Dice
{
    const NUM_OF_FACES = 6;

    /**
     * Constructor
     * Calls the parent constructor with the number of faces set to six.
     */
    public function __construct()
    {
        parent::__construct(self::NUM_OF_FACES);
    }

    /**
     * Gets the roll as an image list.
     *
     * Sets the number of the dice class to the result of the roll. The
     * class is used to via an image show a face of a dice with a number of
     * dots that corresponds to the value of the roll.
     *
     * @return html the list with the class dice with a number that corresponds
     *              to the result of the roll.
     */
    public function getRollAsImageList()
    {
        $html = "<ul class='dice'>";
        $html .= "<li class='dice-{$this->value}'></li>";
        $html .= "</ul>";
        return $html;
    }
}
