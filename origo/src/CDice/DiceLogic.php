<?php
/**
 * Handles the logic for the dice 100 game.
 */
class DiceLogic
{
    const LOSING_SCORE_NO = 1;
    const POINTS_AT_WIN = 100;

    private $dice;
    private $score;
    private $savedScore;
    private $playerMessage;
    private $hasWon;
    private $points;

    /**
     * Constructor
     *
     * Creates a object of the DiceImage class. Sets the score and saved
     * score to zero.
     */
    public function __construct()
    {
        $this->dice = new DiceImage();
        $this->resetVariables();
    }

    /**
     * Resets dice logic variables.
     *
     * @return void.
     */
    public function resetVariables()
    {
        $this->score = 0;
        $this->savedScore = 0;
        $this->points = 0;
        $this->playerMessage = null;
        $this->hasWon = false;
    }

    /**
     * Rolls the dice.
     *
     * Rolls the dice and returns the result. If the dice shows one, all
     * unsaved scores are lost (set to zero) and a message to the player is
     * set.
     * Checks if the player has won, if the player has won a new game is started
     * by reset the scores and clear the message to the player.
     *
     * @return void.
     */
    public function roll()
    {
        if (!$this->hasWon) {
            $this->playerMessage = null;
            $diceResult = $this->dice->roll();
            if ($diceResult == self::LOSING_SCORE_NO) {
                $this->points -= $this->score;
                $this->score = 0;
                $this->playerMessage = "Det blev en etta. Du förlorade alla poäng du inte hade sparat!";
            } else {
                $this->score += $diceResult;
                if ($this->isGameFinished()) {
                    $this->endGame();
                }
            }
        }
    }

    /**
     * Helper function to check if user has user rights.
     *
     * Checks if a user has logged in.
     *
     * @return boolean true if the user has logged in, false otherwise.
     */
    private function isUserMode()
    {
        $isAdminMode = false;
        $acronym = isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;
        if (isset($acronym)) {
            $isAdminMode = true;
        }

        return $isAdminMode;
    }

    /**
     * Helper function to check if the game has finished.
     *
     * Checks if the saved and unsaved score is 100 or more.
     *
     * @return boolean true if score is 100 or more, false otherwise.
     */
    private function isGameFinished()
    {
        $isGameFinished = false;

        $totalResult = $this->score + $this->savedScore;
        if ($totalResult >= self::POINTS_AT_WIN) {
            $isGameFinished = true;
        }

        return $isGameFinished;
    }

    /**
     * Helper function to end the game.
     *
     * Checks if the flag has won i set. If set, calls the method to
     * calculate the final score and send a meesage to the player.
     *
     * @return void.
     */
    private function endGame()
    {
        $this->hasWon = true;
        $this->calculatePoints();
        $this->setMessageToPlayer();
    }

    /**
     * Helper function to calculate the final score.
     *
     * Adds a bonus of 100 points to the score and check if the score is
     * zero or greater. If not, the final score is set to zero.
     *
     * @return void.
     */
    private function calculatePoints()
    {
        $this->points += 100;
        $this->points = $this->points < 0 ? 0 : $this->points;
    }

    /**
     * Helper function to send a message to a player.
     *
     * Sets a message for a player. If a player has logged in, the player has
     * the possiblity to save the score for the competition score board.
     *
     * @return void
     */
    private function setMessageToPlayer()
    {
        if ($this->isUserMode()) {
            $this->playerMessage = 'Du fick ' . $this->points . " poäng. Vill du vara med i tävlingen, spara poängen med den gröna knappen!";
        } else {
            $this->playerMessage = 'Du fick ' . $this->points . " poäng!";
        }
    }

    /**
     * Get the dice result as an image list.
     *
     * Gets the result of the roll as an image list. The class of the list
     * item makes it possible via an image to show a dice with a number of
     * dots that corresponds to the result.
     *
     * @return html a list item with a class that can be used via an image
     *              to visualize the value of the dice.
     */
    public function getDice()
    {
        return $this->dice->getRollAsImageList();
    }

    /**
     * Gets the accumulated score.
     *
     * Returns the result of the accumulated score. The score that can be lost
     * if the result of the roll is one.
     *
     * @return integer the accumulated score.
     */
    public function getAccumulatedScore()
    {
        return $this->score;
    }

    /**
     * Saves accumulated score.
     *
     * Adds the accumulated score to the saved one. Sets the accumulated
     * score to zero.
     *
     * @return void.
     */
    public function saveScore()
    {
        $this->savedScore += $this->score;
        $this->score = 0;
        $this->points -= 5;
    }

    /**
     * Gets the saved score.
     *
     * Returns the score that has been saved.
     *
     * @return integer the saved score.
     */
    public function getSavedScore()
    {
        return $this->savedScore;
    }

    /**
     * Gets the message to the player.
     *
     * Returns a message to the player, if there is any.
     *
     * @return string the message, otherwise null.
     */
    public function getMessage()
    {
        return $this->playerMessage;
    }

    /**
     * Returns if the game has finsished or not.
     *
     * Returns the flag hasWon, which have the status if the game has finsished
     * or not.
     *
     * @return boolean true if the game has finished, false otherwise.
     */
    public function hasGameFinished()
    {
        return $this->hasWon;
    }

    /**
     * Gets the points of the game.
     *
     * Returns the points of the game. To get the final score, the game must
     * have finsished.
     *
     * @return int the score from the game.
     */
    public function getPoints()
    {
        return $this->points;
    }
}
