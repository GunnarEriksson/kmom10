<?php
/**
 * Dice scoreboard Handles the scoreboard logic for the dice 100 game.
 */
class DiceScoreBoard
{
    private $db;
    private $diceLogic;

    public function __construct($db, $diceLogic)
    {
        $this->db = $db;
        $this->diceLogic = $diceLogic;
    }

    public function getScoreBoardResults($noOfResults)
    {
        $sql = 'SELECT * FROM Rm_Game ORDER BY points DESC LIMIT ' . $noOfResults;

        return $this->db->ExecuteSelectQueryAndFetchAll($sql);
    }

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

    private function createScoreboardDefaultParams()
    {
        $default = array (
            'acronym' => null,
            'name' => null,
            'points' => null
        );

        return $default;
    }

    private function getUserInfo($user, $params)
    {
        $params['acronym'] = $user->getAcronym();
        $params['name'] = $user->getName();

        return $params;
    }

    private function getPointsIfGameFinished($params)
    {
        if ($this->diceLogic->hasGameFinished()) {
            $params['points'] = $this->diceLogic->getPoints();
        }

        return $params;
    }

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
