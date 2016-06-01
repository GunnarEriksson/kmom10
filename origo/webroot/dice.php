<?php
/**
 * This is a Origo pagecontroller for the dice game page.
 *
 * Handles the game DICE 100. Controls the rolling of the dice and the calculating
 * of the result. If the player has admin rights, the final score can be saved
 * to a scoreboard.
 */
include(__DIR__.'/config.php');

define("MIN_NUM_OF_ROWS", "5");

/**
 * Save score to scoreboard.
 *
 * Saves the score to scoreboard if the user has user rights. Returns a message
 * of the result to save the score to scoreboard.
 *
 * @param  DiceScoreboard $diceScoreBoard the dice scoreboard object.
 * @param  string $acronym the acronym of the player.
 * @param  string $name the name of the player.
 * @param  int $points the final score for the player.
 *
 * @return string a message if the saving of score was successful or not.
 */
function saveScoreToScoreBoard($diceScoreBoard, $acronym, $name, $points)
{
    if (isset($acronym) && isset($name) && isset($points)) {
        $params = array($acronym, $name, $points);
        $message = $diceScoreBoard->saveScoreToScoreboard($params);
    } else {
        $message = null;
    }

    return null;
}

// Get parameters
$acronym  = isset($_POST['acronym']) ? $_POST['acronym'] : null;
$name  = isset($_POST['name']) ? $_POST['name'] : null;
$points  = isset($_POST['points']) ? $_POST['points'] : null;

if(isset($_GET['newGame'])) {
  // Unset the session variable.
  unset($_SESSION['diceLogic']);
}

// Create the object or get it from the session
if(isset($_SESSION['diceLogic'])) {
  $diceLogic = $_SESSION['diceLogic'];
}
else {
  $diceLogic = new DiceLogic();
  $_SESSION['diceLogic'] = $diceLogic;
}

$rollDice = isset($_GET['rollDice']) ? true : false ;
if ($rollDice) {
    $diceLogic->roll();

}

$shouldSaveScore = isset($_GET['savePoints']) ? true : false ;
if ($shouldSaveScore) {
    $diceLogic->saveScore();
}

$db = new Database($origo['database']);
$diceScoreBoard = new DiceScoreBoard($db, $diceLogic);
$message = saveScoreToScoreBoard($diceScoreBoard, $acronym, $name, $points);


$res = $diceScoreBoard->getScoreBoardResults(MIN_NUM_OF_ROWS);
$saveScoreButton = $diceScoreBoard->generateSaveScoreButton($message);

$htmlTable = new HTMLTable();
$scoreBoard = $htmlTable->generateScoreboardTable($res, MIN_NUM_OF_ROWS);

// Do it and store it all in variables in the Origo container.
$origo['title'] = "Tävling";
// Add style for csource
$origo['stylesheets'][] = 'css/dice.css';

$origo['main'] = <<<EOD
<h1>{$origo['title']}</h1>
<section class="game">
<h2>Tärningsspel 100</h2>
<h3>Regler</h3>
<p>I tärningsspelet 100 gäller det att samla ihop poäng för att komma först till 100.
Du kastar i varje omgång en tärning tills du väljer att stanna och spara poängen eller
det dyker upp en etta och du förlorar alla poäng som du inte har sparat i rundan.
Varje gång du sparar kostar det 5 poäng. Förlorar du poäng kostar det vad du förlorade.
Dessa avdrag dras sedan av från summan 100 poäng och blir dina slutpoäng.
<p>Är du medlem och är inloggad kan du spara slutpoängen och vara med i månadens
tävling!</p>
{$diceLogic->getDice()}
<p>Poäng: {$diceLogic->getAccumulatedScore()}</p>
<p>Sparade poäng: {$diceLogic->getSavedScore()}</p>
<p>Meddelande: {$diceLogic->getMessage()}</p>
<ul class="button">
    <li><a href="?newGame">Nytt spel</a></li>
    <li><a href="?savePoints">Spara poäng</a></li>
    <li><a href="?rollDice">Kasta tärning</a></li>
</ul>
</section>
<section class="scoreboard">
<h2>Resultattavla</h2>
<h3>Top Fem</h3>
{$scoreBoard}
{$saveScoreButton}
</section>

EOD;

// Finally, leave it all to the rendering phase of Origo.
include(ORIGO_THEME_PATH);
