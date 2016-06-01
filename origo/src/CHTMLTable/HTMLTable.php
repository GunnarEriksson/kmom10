<?php
/**
 * Provides HTML tables and functions for returning number of hits, arrows to
 * be able to sort columns in ascending or descending order and support for
 * paging.
 */
class HTMLTable
{
    /**
     * Generate movie table.
     *
     * Generates a movie table with the columns 'Rad', 'Id', 'Bild', 'Titel',
     * 'År' and 'Genre'. Id, Titel and År have arrows so the movies can be
     * sorted in ascending or descending order in those columns.
     *
     * @param [] $res           the result set from the database.
     * @param html $pageNav     the page navigation bar. Default null.
     * @param string $genrePath the genre path to use in the link for breadcrumb
     *                          navigation.
     *
     * @return html the movie table.
     */
    public function generateMovieTable($res, $pageNav = null, $genrePath = null)
    {
        $table = "<table>";
        $table .= $this->createMovieTableHead();

        if (isset($res)) {
            if (!empty($res)) {
                $table .= $this->createMovieTableBody($res, $genrePath);
            } else {
                $table .= $this->createEmptyTableBodyWithMessage();
            }
        } else {
            $table .= $this->createEmptyTableBodyWithErrorMessage();
        }
        $table .= $this->createMovieTableFooter($pageNav);
        $table .= "</table>";

        return $table;
    }

    /**
     * Helper function to create the table head for movie table.
     *
     * Creates the table head for the movie table. Gets an extra column for
     * administration if the user has admin rights.
     *
     * @return html the table head for movie table.
     */
    private function createMovieTableHead()
    {
        $tableHead = "<thead>";
        $tableHead .= "<tr>";
        $tableHead .= "<th>Bild</th>";
        $tableHead .= "<th>Titel" . $this->orderby('title') . "</th>";
        $tableHead .= "<th>Handling</th>";
        $tableHead .= "<th>År" . $this->orderby('year') . "</th>";
        $tableHead .= "<th>Genre</th>";
        $tableHead .= "<th>Pris" . $this->orderby('price') . "</th>";

        if ($this->isAdminMode()) {
            $tableHead .= "<th>Admin</th>";
        }

        $tableHead .= "</tr>";
        $tableHead .= "</thead>";

        return $tableHead;
    }

    /**
     * Function to create links for sorting
     *
     * @param string $column the name of the database column to sort by
     *
     * @return string with links to order by column.
     */
    private function orderby($column)
    {
        $nav  = "<a class='sort-button' href='" . $this->getQueryString(array('orderby'=>$column, 'order'=>'asc')) . "'>&darr;</a>";
        $nav .= "<a class='sort-button' href='" . $this->getQueryString(array('orderby'=>$column, 'order'=>'desc')) . "'>&uarr;</a>";

        return "<span class='orderby'>" . $nav . "</span>";
    }

    /**
     * Helper function to check if the user has admin rights
     *
     * Checks if the user has logged in as admin.
     *
     * @return boolean true if the user has admin rights, false otherwise.
     */
    private function isAdminMode()
    {
        $isAdminMode = false;
        $acronym = isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;
        if (isset($acronym)) {
            if (strcmp ($acronym , 'admin') === 0) {
                $isAdminMode = true;
            }
        }

        return $isAdminMode;
    }

    /**
     * Helper function to create the table body for the movie table.
     *
     * Creates the table body for the movie table. Adds the possibility to edit
     * and remove a movie, if the user has admin rights.
     *
     * @param  [] $res              the result from the database.
     * @param  string $genrePath    genre added in the link for breadcrumb navigation.
     *
     * @return html the table body for the movie table.
     */
    private function createMovieTableBody($res, $genrePath)
    {
        $tableBody = "<tbody>";
        foreach ($res as $key => $row) {
            $tableBody .= "<tr>";
            $tableBody .= "<td>" . $this->createLink("<img src='img.php?src=" . htmlentities($row->image) . "&amp;width=71&amp;height=100&amp;sharpen' alt='" . htmlentities($row->title) . "'/>", $row->id, $genrePath) . "</td>";
            $tableBody .= "<td>" . $this->createLink(htmlentities($row->title), $row->id, $genrePath) . "</td>";
            $tableBody .= "<td>" . $this->createLink($this->getSubstring(htmlentities($row->plot), 260), $row->id, $genrePath) . "</td>";
            $tableBody .= "<td>" . htmlentities($row->year) . "</td>";
            $tableBody .= "<td>" . htmlentities($row->genre) . "</td>";
            $tableBody .= "<td>" . htmlentities($row->price) . "</td>";

            if ($this->isAdminMode()) {
                $tableBody .= "<td><a href='movie_edit.php?id="  . htmlentities($row->id) . "'><img class='admin-icon' src='img/icons/edit.png' title='Uppdatera film' alt='Uppdatera' /></a>";
                $tableBody .= "<a href='movie_delete.php?id=" . htmlentities($row->id) . "'><img class='admin-icon' src='img/icons/delete.png' title='Ta bort film' alt='Ta_bort' /></a></td>";
            }

            $tableBody .= "</tr>\n";
        }

        $tableBody .= "</tbody>";

        return $tableBody;
    }

    /**
     * Helper function to surround an item with a link.
     *
     * Creates a link around an item.
     *
     * @param  var $item            the item to sourround a link to.
     * @param  int $itemIdInTable   the id to a movie, acts as a path.
     * @param  string $genrePath    the genre of the movie, acts as an path.
     *
     * @return htmml the item surounded with a link.
     */
    private function createLink($item, $itemIdInTable, $genrePath)
    {
        $ref = null;

        if (isset($genrePath))
        {
            $ref .= "?genre={$genrePath}&id={$itemIdInTable}";
        } else {
            $ref .= "?id={$itemIdInTable}";
        }

        return "<a href='{$ref}'>{$item}</a>";
    }

    /**
     * Helper function to get a substring of a string.
     *
     * Returns specified part of an text. A helper function checks the next nearest
     * space in the text for the specified length to prevent a word to be
     * truncated.
     *
     * @param  string $textString   the string to be truncated.
     * @param  int $numOfChar       the maximum length of the text.
     *
     * @return string               the truncated text.
     */
    private function getSubstring($textString, $numOfChar)
    {
        $textEndPos = $this->getSpacePosInString($textString, $numOfChar);
        if ($textEndPos === 0) {
            $text = substr($textString, 0, $numOfChar);
        } else {
            $text = substr($textString, 0, $textEndPos);
            $text .= " ...";
        }

        return $text;
    }

    /**
     * Helper function to find the next space in a string.
     *
     * Finds the next space from the specified position.
     *
     * @param  string $textString   the text string to find a space in.
     * @param  int $offset          the position to find the next space from.
     *
     * @return int the position of the next space from the specified position.
     */
    private function getSpacePosInString($textString, $offset)
    {
        $pos = 0;
        if (strlen($textString) >= $offset) {
            $pos = strpos($textString, ' ', $offset);
        }

        return $pos;
    }

    /**
     * Helper function to create a table body with an error message only.
     *
     * Creates an empty table body with the error message no connection with the
     * database or database has no contents. Number of columns depends if the user
     * has admin rights or not. If the user has admin rights, there is an extra
     * column for administration.
     *
     * @return html a table body containing the error message no connction with
     *              the database or database has no content.
     */
    private function createEmptyTableBodyWithErrorMessage($spanNormal, $spanAdmin)
    {
        $tableBody = "<tbody>";
        if ($this->isAdminMode()) {
            $tableBody .= "<td colspan = '7'><span class='message'>Ingen kontakt med databas eller databas saknar innehåll</span></td>";
        } else {
            $tableBody .= "<td colspan = '6'><span class='message'>Ingen kontakt med databas eller databas saknar innehåll</span></td>";
        }
        $tableBody .= "</tbody>";

        return $tableBody;
    }

    /**
     * Helper function to create a table body with a message.
     *
     * Creates an empty table body with the message no search result matched
     * your search. Number of columns depends if the user has admin rights or not.
     * If the user has admin rights, there is an extra column for administration.
     *
     * @return html a table body containing the message o search result matched
     *              your search.
     */
    private function createEmptyTableBodyWithMessage()
    {   $tableBody = "<tbody>";
        if ($this->isAdminMode()) {
            $tableBody .= "<td colspan = '7'><span class='message'>Inga sökresultat matchade din sökning</span></td>";
        } else {
            $tableBody .= "<td colspan = '6'><span class='message'>Inga sökresultat matchade din sökning</span></td>";
        }
        $tableBody .= "</tbody>";

        return $tableBody;
    }

    /**
     * Helper function to create a movie table footer.
     *
     * Creates a table footer for a movie table. Number of columns depends if
     * the user has admin rights or not. If the user has admin rights, there is
     * an extra column for administration.
     *
     * @param  html $pageNav the navigation bar for page navigation.
     *
     * @return html the table footer for the movie table.
     */
    private function createMovieTableFooter($pageNav)
    {
        $tableFooter = "<tfoot>";
        $tableFooter .= "<tr>";

        if ($this->isAdminMode()) {
            $tableFooter .= "<td colspan = '7'>{$pageNav}</td>";
        } else {
            $tableFooter .= "<td colspan = '6'>{$pageNav}</td>";
        }

        $tableFooter .= "</tr>";
        $tableFooter .= "</tfoot>";

        return $tableFooter;
    }

    /**
     * Use the current querystring as base, modify it according to $options and return the modified query string.
     *
     * @param array $options    to set/change.
     * @param string $prepend   this to the resulting query string
     *
     * @return string with an updated query string.
     */
    public function getQueryString($options=array(), $prepend='?')
    {
        // parse query string into array
        $query = array();
        parse_str($_SERVER['QUERY_STRING'], $query);

        // Modify the existing query string with new options
        $query = array_merge($query, $options);

        // Return the modified querystring
        return $prepend . htmlentities(http_build_query($query));
    }

    /**
     * Create links for hits per page.
     *
     * @param array $hits       a list of hits-options to display.
     * @param array $current    current value, default null
     * .
     * @return string as a link to this page.
     */
    public function getHitsPerPage($hits, $current=null)
    {
        $nav = "Träffar per sida: ";
        foreach($hits AS $val) {
            if($current == $val) {
                $nav .= "<span class='selected'>$val </span>";
            }
            else {
                $nav .= "<a href='" . $this->getQueryString(array('hits' => $val)) . "'>$val</a> ";
            }
        }

        return $nav;
    }

    /**
     * Create navigation among pages.
     *
     * @param integer $hits per page.
     * @param integer $page current page.
     * @param integer $max number of pages.
     * @param integer $min is the first page number, usually 0 or 1.
     * @return string as a link to this page.
     */
    public function getPageNavigation($hits, $page, $max, $min=1)
    {
        $nav  = ($page != $min) ? "<a href='" . $this->getQueryString(array('page' => $min)) . "'>&lt;&lt;</a> " : '&lt;&lt; ';
        $nav .= ($page > $min) ? "<a href='" . $this->getQueryString(array('page' => ($page > $min ? $page - 1 : $min) )) . "'>&lt;</a> " : '&lt; ';

        for($i=$min; $i<=$max; $i++) {
            if($page == $i) {
                $nav .= "$i ";
            }
            else {
              $nav .= "<a href='" . $this->getQueryString(array('page' => $i)) . "'>$i</a> ";
          }
        }

        $nav .= ($page < $max) ? "<a href='" . $this->getQueryString(array('page' => ($page < $max ? $page + 1 : $max) )) . "'>&gt;</a> " : '&gt; ';
        $nav .= ($page != $max) ? "<a href='" . $this->getQueryString(array('page' => $max)) . "'>&gt;&gt;</a> " : '&gt;&gt; ';

        return $nav;
    }

    /**
     * Create navigation bar among pages.
     *
     * @param integer $hits per page.
     * @param integer $page current page.
     * @param integer $max number of pages.
     * @param integer $min is the first page number, usually 0 or 1.
     * @return string as a link to this page.
     */
    public function getPageNavigationBar($hits, $page, $max, $min=1)
    {
        $nav = "<div class='navigationBar'>";
        $nav .= "<ul class='backButtons'>";
        $nav .= "<li>";
        $nav .= ($page != $min) ? "<a href='" . $this->getQueryString(array('page' => $min)) . "'><span class='button'>&lt;&lt;</span></a> " : "<span class='button'>&lt;&lt;</span>";
        $nav .= "</li>";
        $nav .= "<li>";
        $nav .= ($page > $min) ? "<a href='" . $this->getQueryString(array('page' => ($page > $min ? $page - 1 : $min) )) . "'><span class='button'>&lt;</span></a> " : "<span class='button'>&lt;</span>";
        $nav .= "</li>";
        $nav .= "</ul>";

        $nav .= "<ul class='forwardButtons'>";
        $nav .= "<li>";
        $nav .= ($page < $max) ? "<a href='" . $this->getQueryString(array('page' => ($page < $max ? $page + 1 : $max) )) . "'><span class='right-button'>&gt;</span></a> " : "<span class='right-button'>&gt;</span>";
        $nav .= "</li>";
        $nav .= "<li>";
        $nav .= ($page != $max) ? "<a href='" . $this->getQueryString(array('page' => $max)) . "'><span class='right-button'>&gt;&gt;</span></a> " : "<span class='right-button'>&gt;&gt;</span>";
        $nav .= "</li>";
        $nav .= "</ul>";

        $nav .= "<ul class='pageNumbers'>";
        for($i=$min; $i<=$max; $i++) {
            $nav .= "<li>";
            if($page == $i) {
                $nav .= "<span class='button selected'>$i</span>";
            }
            else {
              $nav .= "<a href='" . $this->getQueryString(array('page' => $i)) . "'><span class='button'>$i</span></a> ";
          }
          $nav .= "</li>";
        }
        $nav .= "</ul>";

        $nav .= "</div>";

        return $nav;
    }

    /**
     * Generates the scoreboard table.
     *
     * Creates the scoreboard table for game results.
     *
     * @param  [] $res the result from the database.
     * @param  [] $minNumOfRows the minimum number of rows. Used to fill up the
     *                          table if number of results in db is less than
     *                          the minimum number of rows.
     *
     * @return html the scoreboard table.
     */
    public function generateScoreboardTable($res, $minNumOfRows)
    {
        $html = "<table>";
        $html .= $this->createScoreboardTableHead();
        $html .= $this->createScoreboardTableBody($res, $minNumOfRows);
        $html .= $this->createScoreboardTableFooter();
        $html .= "</table>";

        return $html;
    }

    /**
     * Helper function to create scoreboard table head.
     *
     * Creates the table head for the scoreboard table.
     *
     * @return html the scoreboard table head.
     */
    private function createScoreboardTableHead()
    {
        $tableHead = "<thead>";
        $tableHead .= "<tr>";
        $tableHead .= "<th>Namn</th>";
        $tableHead .= "<th>Poäng</th>";
        $tableHead .= "</thead>";

        return $tableHead;
    }

    /**
     * Helper function to create scoreboard table body.
     *
     * Creates the scoreboard table body. If the number of results in db is less
     * than minimum number of rows, the table is filled with "-".
     *
     * @param  [] $res the result from the database.
     * @param  int $minNumOfRows the minimum number of rows in the table.
     *
     * @return html the scoreboard table body.
     */
    private function createScoreboardTableBody($res, $minNumOfRows)
    {
        $counter = 0;

        $tableBody = "<tbody>";
        foreach ($res as $key => $row) {
            $tableBody .= "<tr>";
            $tableBody .= "<td>" . htmlentities($row->name) . "</td>";
            $tableBody .= "<td>" . htmlentities($row->points) . "</td>";
            $tableBody .= "</tr>\n";

            $counter++;
        }

        // Fill table up to the minimum of rows
        for ($i = $counter; $i < $minNumOfRows; $i++) {
            $tableBody .= "<tr>";
            $tableBody .= "<td>-</td>";
            $tableBody .= "<td>-</td>";
            $tableBody .= "</tr>\n";
        }

        $tableBody .= "</tbody>";

        return $tableBody;
    }

    /**
     * Helper function to create scoreboard table footer.
     *
     * Creates the scoreboard table footer.
     *
     * @return html the scoreboard table footer.
     */
    private function createScoreboardTableFooter()
    {
        $tableFooter = "<tfoot>";
        $tableFooter .= "<tr>";
        $tableFooter .= "<td colspan = '2'>Tärningspel 100</td>";
        $tableFooter .= "</tr>";
        $tableFooter .= "</tfoot>";

        return $tableFooter;
    }

    /**
     * Generates the user table.
     *
     * Creates the table for all users of the Rental Movies.
     * If no contact with database could be established, no content found in db or
     * no user with a specific id found, the user is noticed.
     *
     * @param  [] $res          the result from the database.
     * @param  html $pageNav    the navigation bar used at paging.
     *
     * @return html the user table for all users of the Rental Moives.
     */
    public function generateUserTable($res, $pageNav = null)
    {
        $table = "<table>";
        $table .= $this->createUserTableHead();

        if (isset($res)) {
            if (!empty($res)) {
                $table .= $this->createUserTableBody($res);
            } else {
                $table .= $this->createEmptyUserTableBodyWithMessage();
            }
        } else {
            $table .= $this->createEmptyUserTableBodyWithErrorMessage();
        }
        $table .= $this->createUserTableFooter($pageNav);
        $table .= "</table>";

        return $table;
    }

    /**
     * Helper function to create table heder for user tables.
     *
     * Creates a user table header with the possiblity to order the table
     * based on acronyms or names.
     *
     * @return html the user table header.
     */
    private function createUserTableHead()
    {
        $tableHead = "<thead>";
        $tableHead .= "<tr>";
        $tableHead .= "<th>Akronym" . $this->orderby('acronym') . "</th>";
        $tableHead .= "<th>Namn" . $this->orderby('name') . "</th>";
        $tableHead .= "<th>Info</th>";
        $tableHead .= "<th>E-post</th>";
        $tableHead .= "<th>Publicerad</th>";
        $tableHead .= "<th>Uppdaterad</th>";
        $tableHead .= "<th>Admin</th>";
        $tableHead .= "</tr>";
        $tableHead .= "</thead>";

        return $tableHead;
    }

    /**
     * Helper function to create table user body.
     *
     * Creates a table body for users with icons to be able to update or delete
     * users.
     *
     * @param  [] $res the result from the database.
     *
     *@return html the usr table body.
     */
    private function createUserTableBody($res)
    {
        $tableBody = "<tbody>";
        foreach ($res as $key => $row) {
            $tableBody .= "<tr>";
            $tableBody .= "<td>" . htmlentities($row->acronym) . "</td>";
            $tableBody .= "<td>" . htmlentities($row->name) . "</td>";
            $tableBody .= "<td>" . htmlentities($row->info) . "</td>";
            $tableBody .= "<td>" . htmlentities($row->email) . "</td>";
            $tableBody .= "<td>" . htmlentities($row->published) . "</td>";
            $tableBody .= "<td>" . htmlentities($row->updated) . "</td>";
            $tableBody .= "<td><a href='user_edit.php?id="  . htmlentities($row->id) . "'><img class='admin-icon' src='img/icons/edit.png' title='Uppdatera användare alt='Uppdatera' /></a>";
            if ($row->acronym !=='admin') {
                $tableBody .= "<a href='user_delete.php?id=" . htmlentities($row->id) . "'><img class='admin-icon' src='img/icons/delete.png' title='Ta bort användare' alt='Ta_bort' /></a></td>";
            }
            $tableBody .= "</tr>\n";
        }

        $tableBody .= "</tbody>";

        return $tableBody;
    }

    /**
     * Helper function to create a table body with an error message only for users.
     *
     * Creates an empty table body with the error message no connection with the
     * database or database has no contents.
     *
     * @return html a table body containing the error message no connction with
     *              the database or database has no content.
     */
    private function createEmptyUserTableBodyWithErrorMessage()
    {
        $tableBody = "<tbody>";
        $tableBody .= "<td colspan = '8'><span class='message'>Ingen kontakt med databas eller databas saknar innehåll</span></td>";
        $tableBody .= "</tbody>";

        return $tableBody;
    }

    private function createEmptyUserTableBodyWithMessage()
    {   $tableBody = "<tbody>";
        $tableBody .= "<td colspan = '8'><span class='message'>Inga sökresultat matchade din sökning</span></td>";
        $tableBody .= "</tbody>";

        return $tableBody;
    }

    /**
     * Helper method to creat user table footer.
     *
     * Creates table footer for the user table.
     *
     * @param  html $pageNav the navigation bar for paging.
     *
     * @return html the table footer for user table.
     */
    private function createUserTableFooter($pageNav)
    {
        $tableFooter = "<tfoot>";
        $tableFooter .= "<tr>";
        $tableFooter .= "<td colspan = '7'>{$pageNav}</td>";
        $tableFooter .= "</tr>";
        $tableFooter .= "</tfoot>";

        return $tableFooter;
    }

}
