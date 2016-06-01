<?php
/**
 * Dice scoreboard Handles the scoreboard logic for the dice 100 game.
 */
class DiceScoreBoard
{
    private $db;
    private $diceLogic;

    /**
     * Constructor
     * Initiates the database and the dice logic.
     *
     * @param Database $db          the database object.
     * @param DiceLogic $diceLogic  the DiceLogic object.
     */
    public function __construct($db, $diceLogic)
    {
        $this->db = $db;
        $this->diceLogic = $diceLogic;
    }

    /**
     * Gets the scoreboard result.
     *
     * Gets the score board result from the database with the possiblity to
     * limit the number of results.
     *
     * @param  int $noOfResults the maximum number of results.
     *
     * @return [] the scoreboard results.
     */
    public function getScoreBoardResults($noOfResults)
    {
        $sql = 'SELECT * FROM Rm_Game ORDER BY points DESC LIMIT ' . $noOfResults;

        return $this->db->ExecuteSelectQueryAndFetchAll($sql);
    }

    /**
     * Generates a save score button.
     *
     * Generates a form with a save score button, if the user has logged in.
     *
     * @param  string $message the message to the player.
     *
     * @return html the form with the save score to the scoreboard button.
     */
    public function generateSaveScoreButton($message)
    {
        $output = null;

        $user = new User($this->db);
        if ($user->isAuthenticated()) {
            $params = $this->createScoreboardDefaultParams();
            $params = $this->getUserInfo($user, $params);
            $params = $this->getPointsIfGameFinished($params);
            $output = $this->createSaveScoreForm($params, $message);
        }

        return $output;
    }

    /**
     * Helper function to create default parameters for the scoreboard.
     *
     * Creates default parameters for the score board and sets the parameters
     * to null.
     *
     * @return [] the array of default parameters for the scoreboard.
     */
    private function createScoreboardDefaultParams()
    {
        $default = array (
            'acronym' => null,
            'name' => null,
            'points' => null
        );

        return $default;
    }

    /**
     * Helper function to get user information.
     *
     * Gets the acronym and name for the user.
     *
     * @param  User $user   the user object.
     * @param  [] $params   the player information.
     *
     * @return [] the user information.
     */
    private function getUserInfo($user, $params)
    {
        $params['acronym'] = $user->getAcronym();
        $params['name'] = $user->getName();

        return $params;
    }

    /**
     * Helper function to get the total score of the game when finished.
     *
     * Checks if the game has finished and if the game is finished, gets the
     * total score for the game session.
     *
     * @param  [] $params the player information.
     *
     * @return [] player information and if game finsished, the score is added
     *            to the player information.
     */
    private function getPointsIfGameFinished($params)
    {
        if ($this->diceLogic->hasGameFinished()) {
            $params['points'] = $this->diceLogic->getPoints();
        }

        return $params;
    }

    /**
     * Helper function to create the form for saving the score.
     *
     * Creates a form to save the score from one session to the scoreboard.
     * If the game has finished, the button change color.
     *
     * @param  [] $params       player information containing acronym, name and score.
     * @param  string $message  the message to the player.
     *
     * @return htmml the form for saving the score.
     */
    private function createSaveScoreForm($params, $message)
    {
        $gameStatus = 'ongoing';
        if (isset($params['points'])) {
            $gameStatus = "finished";
        }

        $output = <<<EOD
        <form class='save-button' method=post>
            <input type='hidden' name='acronym' value="{$params['acronym']}"/>
            <input type='hidden' name='name' value="{$params['name']}"/>
            <input type='hidden' name='points' value="{$params['points']}"/>
            <input class="{$gameStatus}" type='submit' name='save' value='Spara'/>
            <p><output>{$message}</output></p>
        </form>
EOD;

        return $output;
    }

    /**
     * Saves the score to the scoreboard.
     *
     * Saves the acronym, name and points for a player to the database.
     * Returns a message if the information is stored or not.
     *
     * @param  [] $params the player information.
     *
     * @return string the result if the information was stored or not.
     */
    public function saveScoreToScoreboard($params)
    {
        $sql = '
            INSERT INTO Rm_Game (acronym, name, points)
                VALUES (?, ?, ?);
        ';

        $res = $this->db->ExecuteQuery($sql, $params);

        if ($res) {
            $output = 'Dina poäng är inlagda i tävlingen.';
            $this->diceLogic->resetVariables();
        } else {
            $output = 'Dina poäng kunde EJ läggas till i tävlingen.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre>';
        }

        return $output;
    }
}
