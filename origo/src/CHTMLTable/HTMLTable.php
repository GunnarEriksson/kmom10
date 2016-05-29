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
     * @param  [] $res the result set from the database.
     * @return html the movie table.
     */
    public function generateMovieTable($res, $pageNav = null)
    {
        $table = "<table>";
        $table .= $this->createTableHead();

        if (isset($res)) {
            if (!empty($res)) {
                $table .= $this->createTableBody($res);
            } else {
                $table .= $this->createEmptyTableBodyWithMessage();
            }
        } else {
            $table .= $this->createEmptyTableBodyWithErrorMessage();
        }
        $table .= $this->createTableFooter($pageNav);
        $table .= "</table>";

        return $table;
    }

    private function createTableHead()
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
     * @return string with links to order by column.
     */
    private function orderby($column)
    {
        $nav  = "<a class='sort-button' href='" . $this->getQueryString(array('orderby'=>$column, 'order'=>'asc')) . "'>&darr;</a>";
        $nav .= "<a class='sort-button' href='" . $this->getQueryString(array('orderby'=>$column, 'order'=>'desc')) . "'>&uarr;</a>";

        return "<span class='orderby'>" . $nav . "</span>";
    }

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

    private function createTableBody($res)
    {
        $tableBody = "<tbody>";
        foreach ($res as $key => $row) {
            $tableBody .= "<tr>";
            $tableBody .= "<td>" . $this->createLink("<img src='img.php?src=" . htmlentities($row->image) . "&amp;width=71&amp;height=100&amp;sharpen' alt='" . htmlentities($row->title) . "'/>", $row->id) . "</td>";
            $tableBody .= "<td>" . $this->createLink(htmlentities($row->title), $row->id) . "</td>";
            $tableBody .= "<td>" . $this->createLink($this->getSubstring(htmlentities($row->plot), 260), $row->id) . "</td>";
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

    private function createLink($item, $itemIdInTable)
    {
        return "<a href='?id={$itemIdInTable}'>{$item}</a>";
    }

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

    private function getSpacePosInString($textString, $offset)
    {
        $pos = 0;
        if (strlen($textString) >= $offset) {
            $pos = strpos($textString, ' ', $offset);
        }

        return $pos;
    }

    private function createEmptyTableBodyWithErrorMessage()
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

    private function createTableFooter($pageNav)
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
     * @param array $options to set/change.
     * @param string $prepend this to the resulting query string
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
     * @param array $hits a list of hits-options to display.
     * @param array $current value.
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

    public function generateScoreBoardTable($res, $minNumOfRows)
    {
        $html = "<table>";
        $html .= $this->createScoreBoardTableHead();
        $html .= $this->createScoreBoardTableBody($res, $minNumOfRows);
        $html .= $this->createMovieTableFooter();
        $html .= "</table>";

        return $html;
    }

    private function createScoreBoardTableHead()
    {
        $tableHead = "<thead>";
        $tableHead .= "<tr>";
        $tableHead .= "<th>Namn</th>";
        $tableHead .= "<th>Poäng</th>";
        $tableHead .= "</thead>";

        return $tableHead;
    }

    private function createScoreBoardTableBody($res, $minNumOfRows)
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

    private function createMovieTableFooter()
    {
        $tableFooter = "<tfoot>";
        $tableFooter .= "<tr>";
        $tableFooter .= "<td colspan = '2'>Tärningspel 100</td>";
        $tableFooter .= "</tr>";
        $tableFooter .= "</tfoot>";

        return $tableFooter;
    }

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

    private function createEmptyUsrTableBodyWithErrorMessage()
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
