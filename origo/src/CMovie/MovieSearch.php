<?php
/**
 * Movie seach, provides a form for searching movies and preparing data base requests.
 *
 */
class MovieSearch
{
    private $db;
    private $parameters;
    private $sqlOrig;
    private $groupby;
    private $sort;
    private $limit;
    private $numOfRows;


    /**
     * Constructor creating a movie seach form object.
     *
     * @param Database the database object.
     * @param [] $parameters Array of parameters such as title, hits, page, year1,
     *                       year2, orderby, order and genre.
     */
    public function __construct($db, $parameters)
    {
        $this->db = $db;
        $default = $this->createDefaultParameters();
        $this->parameters = array_merge($default, $parameters);
        $this->sqlOrig = $this->createOriginalSqlQuery();
        $this->groupby = ' GROUP BY M.id';
        $orderby = $this->parameters['orderby'];
        $order = $this->parameters['order'];
        $this->sort = " ORDER BY $orderby $order";
        $this->limit = null;
        $this->numOfRows = null;
    }

    /**
     * Helper function to create an array of default parameters.
     *
     * Creates an array of default values for the arguments to the class.
     * Is used to replace values of missing parameters.
     *
     * @return [] the array of default values for argument parameters.
     */
    private function createDefaultParameters()
    {
        $default = array(
            'id' => null,
            'title' => null,
            'hits' => null,
            'page' => null,
            'year1' => null,
            'year2' => null,
            'orderby' => null,
            'order' => null,
            'genre' => null,
        );

        return $default;
    }

    /**
     * Helper function to create the original SQL string.
     *
     * Creats the original SQL string than can be modified if necessary.
     *
     * @return SQL the original SQL string.
     */
    private function createOriginalSqlQuery()
    {
        $sqlOrig = '
          SELECT
            M.*,
            GROUP_CONCAT(G.name) AS genre
          FROM Rm_Movie AS M
            LEFT OUTER JOIN Rm_Movie2Genre AS M2G
              ON M.id = M2G.idMovie
            INNER JOIN Rm_Genre AS G
              ON M2G.idGenre = G.id
        ';

        return $sqlOrig;
    }

    /**
     * Creates the movie search form.
     *
     * Creates the movie search form. Parameters that can be uses for searching
     * for films is title, from year and to year. The form has also the possiblity
     * to show all films in the database.
     *
     * @return html the movie search form.
     */
    public function getMovieSearchForm()
    {
        $html = '<form class="movie-search-form">';
        $html .= '<fieldset>';
        $html .= '<legend>Sök</legend>';
        $html .= '<input type=hidden name=hits value="' . htmlentities($this->parameters['hits']) . '"/>';
        $html .= '<input type=hidden name=page value="1"/>';
        $html .= '<input type=hidden name=genre value="' . $this->parameters['genre'] . '"/>';
        $title = htmlentities($this->parameters['title']);
        $html .= '<p><label>Titel: <input type="search" name="title" value="' . $title . '"/> (delsträng, använd % som *)</label></p>';
        $year1 = htmlentities($this->parameters['year1']);
        $html .= '<p><label>Årtal: <input type="text" name="year1" value="'. $year1 . '"/></label>';
        $html .= ' - ';
        $year2 = htmlentities($this->parameters['year2']);
        $html .= '<label><input type="text" name="year2" value="'. $year2 . '"/></label></p>';
        $html .= '<p><label>Genre: </label></p>';
        $html .= "<ul class='genres'><li><a href='?title={$title}&year1={$year1}&year2={$year2}&genre='>Alla</a></li>";
        $res = $this->getAllGenres();
        foreach ($res as $key => $row) {
            $genre = htmlentities($row->name);
            if (strcmp($this->parameters['genre'], $genre) === 0) {
                $html .= "<li><span class=selected>{$genre}</span></li>";
            } else {
                $html .= "<li><a href='?title={$title}&year1={$year1}&year2={$year2}&genre={$genre}'>{$genre}</a></li>";
            }
        }
        $html .= '</ul>';
        $html .= '<p><input type="submit" name="submit" value="Sök"/></p>';
        $html .= '<p><a href="?">Visa alla filmer</a></p>';
        $html .= '</fieldset>';
        $html .= '</form>';

        return $html;
    }

    /**
     * Gets all connected genres for the movies.
     *
     * Gets only the genres that are connected to the movies, which can be a
     * part of all available genres if all available genres is not used by
     * the movies in the database.
     *
     * @return [] All genres used by the movies in database.
     */
    public function getAllGenres()
    {
        $sql = '
            SELECT DISTINCT G.name
            FROM Rm_Genre AS G
                INNER JOIN Rm_Movie2Genre AS M2G
                    ON G.id = M2G.idGenre;
        ';

        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);

        return $res;
    }

    /**
     * Search in database for films.
     *
     * Searches in the database for films. Makes a second request to get the
     * number of hits(number of rows). The value can be fetch with the function
     * getNumberOfRows().
     *
     * @return [] the result set from the database containing information about
     *            the movies, such as row, id, link to image, title, year and genre.
     */
    public function searchMovie()
    {
        $query = $this->prepareSearchMovieQuery();
        $movieSearchRes = $this->db->ExecuteSelectQueryAndFetchAll($query['sql'], $query['params']);

        $query = $this->prepareNumberOfRowsQuery();
        $res = $this->db->ExecuteSelectQueryAndFetchAll($query['sql'], $query['params']);

        if ($res && !empty($res)) {
            $this->numOfRows = $res[0]->rows;
        } else {
            $this->numOfRows = 0;
            $movieSearchRes = null;
        }

        return $movieSearchRes;
    }

    /**
     * Helper function to prepare an SQL string to search for movies in database.
     *
     * Prepares an SQL string for searching in the database for movies. Includes
     * the possiblity to group, sort and limit the result. The SQL string is a
     * combination of an original SQL string and a optional where statement.
     *
     * @return [] the array containing the SQL string and parameters, if included.
     */
    private function prepareSearchMovieQuery()
    {
        $sqlOrig = $this->sqlOrig;
        $query = $this->prepareQueryAndParams();
        $where = $query['where'];
        $where = $where ? " WHERE 1 {$where}" : null;
        $sql = $sqlOrig . $where . $this->groupby . $this->sort . $this->limit;

        return array('sql' => $sql, 'params' => $query['params']);
    }

    /**
     * Helper function to prepare an SQL where statement.
     *
     * Prepares an SQL where statement depending of the purpose of the search.
     * Supports multiple searches and parameters.
     *
     * @return [] the array with where statements and parameters, if included.
     */
    private function prepareQueryAndParams()
    {
        $where = null;
        $sqlParameters = array();

        // Select by id
        if($this->parameters['id']) {
          $where .= ' AND M.id = ?';
          $sqlParameters[] = $this->parameters['id'];
        }

        // Select by title
        if($this->parameters['title']) {
          $where .= ' AND title LIKE ?';
          $sqlParameters[] = $this->parameters['title'];
        }

        // Select by year
        if($this->parameters['year1']) {
          $where .= ' AND year >= ?';
          $sqlParameters[] = $this->parameters['year1'];
        }

        if($this->parameters['year2']) {
          $where .= ' AND year <= ?';
          $sqlParameters[] = $this->parameters['year2'];
        }

        if($this->parameters['genre']) {
          $where .= ' AND G.name = ?';
          $sqlParameters[] = $this->parameters['genre'];
        }

        // Pagination
        if($this->parameters['hits'] && $this->parameters['page']) {
          $this->limit = " LIMIT {$this->parameters['hits']} OFFSET " . (($this->parameters['page'] - 1) * $this->parameters['hits']);
        }

        if (empty($sqlParameters)) {
            $query = array('where' => $where, 'params' => null);
        } else {
            $query = array('where' => $where, 'params' => $sqlParameters);
        }

        return $query;
    }

    /**
     * Helper method to prepare number of rows query.
     *
     * Prepares the SQL string to get the number of rows(hits) for the movie
     * search in the database.
     *
     * @return [] the array containing the SQL string and parameters, if included,
     *            to get number of rows(hits).
     */
    private function prepareNumberOfRowsQuery()
    {
        $query = $this->prepareQueryAndParams();
        $where = $query['where'];

        $sql = "
          SELECT
            COUNT(id) AS rows
          FROM
          (
            $this->sqlOrig $where $this->groupby
          ) AS Rm_Movie
        ";

        return array('sql' => $sql, 'params' => $query['params']);
    }

    /**
     * Get the number of rows for the movie search.
     *
     * Returns the number of rows(hits) for the movie search.
     *
     * @return integer the number of rows(hits) for the movie search.
     */
    public function getNumberOfRows()
    {
        return $this->numOfRows;
    }

    /**
     * Gets the maximum number of pages.
     *
     * Returns the maximum number of pages depending how many rows that should
     * be shown in the table. Is used for paging.
     *
     * @return integer the maximum number of pages depending how many rows
     *                 that should be shown in the movie table.
     */
    public function getMaxNumPages()
    {
        return ceil($this->numOfRows / $this->parameters['hits']);
    }

    /**
     * Get title by id.
     *
     * Gets the title of the movie for a specific id.
     *
     * @return string the movie title for the movie with the specified id.
     */
    public function getTitleById()
    {
        $sql = 'SELECT title FROM Rm_Movie WHERE id = ?';

        if($this->parameters['id']) {
          $sqlParameters[] = $this->parameters['id'];
        }

        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, $sqlParameters);

        if ($res && !empty($res)) {
            $title = $res[0]->title;
        } else {
            $title = null;
        }

        return $title;
    }

    /**
     * Get the id for the first movie in the result from database.
     *
     * Returns the id for the first id from the result from the database.
     *
     * @param  [] $res the result from the database.
     *
     * @return int the id for the first movie in the result from database, null
     *             otherwise.
     */
    public function getIdForFirstMovie($res)
    {
        $id = null;

        if (isset($res) && !empty($res)) {
            $id = $res[0]->id;
        }

        return $id;
    }
}
