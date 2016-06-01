<?php
/**
 * User seach, provides a form for searching users and preparing data base requests.
 *
 */
class UserSearch
{
    private $db;
    private $parameters;
    private $sqlOrig;
    private $groupby;
    private $sort;
    private $limit;
    private $numOfRows;

    /**
     * Constructor creating a user seach form object.
     *
     */
    public function __construct($db, $parameters)
    {
        $this->db = $db;
        $default = $this->createDefaultParameters();
        $this->parameters = array_merge($default, $parameters);
        $this->sqlOrig = $this->createOriginalSqlQuery();
        $orderby = $this->parameters['orderby'];
        $order = $this->parameters['order'];
        $this->sort = " ORDER BY $orderby $order";
        $this->limit = null;
        $this->numOfRows = null;
    }

    /**
     * Helper method to create default parameters.
     *
     * Creates an array of user parameters and the parameters are set to null.
     *
     * @return [] the array of default parameters for user search.
     */
    private function createDefaultParameters()
    {
        $default = array(
            'id' => null,
            'acronym' => null,
            'name' => null,
            'hits' => null,
            'page' => null,
            'orderby' => 'id',
            'order' => null
        );

        return $default;
    }

    /**
     * Helper method to create an original SQL query for user search.
     *
     * Creates a SQL string to us a base when searching for users.
     *
     * @return SQL string the original SQL string to search for users.
     */
    private function createOriginalSqlQuery()
    {
        $sqlOrig = '
        SELECT *
        FROM Rm_User
        ';

        return $sqlOrig;
    }

    /**
     * Generates a user search form to search for users.
     *
     * Creates a form to search for users in the database.
     *
     * @return html the form to search for users in the database.
     */
    public function generateUserSearchForm()
    {
        $html = '<form class="user-search-form">';
        $html .= '<fieldset>';
        $html .= '<legend>Sök</legend>';
        $html .= '<input type=hidden name=hits value="' . htmlentities($this->parameters['hits']) . '"/>';
        $html .= '<input type=hidden name=page value="1"/>';
        $acronym = htmlentities($this->parameters['acronym']);
        $html .= '<p><label>Användarnamn:<br/><input type="search" name="acronym" value="' . $acronym . '"/> (delsträng, använd % som *)</label></p>';
        $name = htmlentities($this->parameters['name']);
        $html .= '<p><label>Namn:<br/> <input type="search" name="name" value="'. $name . '"/></label></p>';
        $html .= '<p><input type="submit" name="submit" value="Sök"/></p>';
        $html .= '<p><a href="?">Visa alla konton</a></p>';
        $html .= '</fieldset>';
        $html .= '</form>';

        return $html;
    }

    /**
     * Searches for user(s) in the database.
     *
     * Search user(s) in the database. Send also a request to get the number of
     * rows. Can be used at paging.
     *
     * @return [] the user(s) result from the database.
     */
    public function searchUser()
    {
        $query = $this->prepareSearchUserQuery();
        $userSearchRes = $this->db->ExecuteSelectQueryAndFetchAll($query['sql'], $query['params']);

        $query = $this->prepareNumberOfRowsQuery();
        $res = $this->db->ExecuteSelectQueryAndFetchAll($query['sql'], $query['params']);


        if ($res && !empty($res)) {
            $this->numOfRows = $res[0]->rows;
        } else {
            $this->numOfRows = 0;
            $userSearchRes = null;
        }

        return $userSearchRes;
    }

    /**
     * Helper function to prepare the search for user(s).
     *
     * Adds SQL parameters to the original SQL search string. Add parameters
     * related to the added SQL parameters.
     *
     * @return [] an array with the SQL string and related parameters.
     */
    private function prepareSearchUserQuery()
    {
        $sqlOrig = $this->sqlOrig;
        $query = $this->prepareQueryAndParams();
        $where = $query['where'];
        $where = $where ? " WHERE 1 {$where}" : null;
        $sql = $sqlOrig . $where . $this->sort . $this->limit;

        return array('sql' => $sql, 'params' => $query['params']);
    }

    /**
     * Helper function to prepare additional query and related parameters.
     *
     * Creates additional SQL parameters with related parameters to create
     * a specfied query for user to the database.
     *
     * @return [] the additional SQL parameters and the related parameters.
     */
    private function prepareQueryAndParams()
    {
        $where = null;
        $sqlParameters = array();

        // Select by id
        if($this->parameters['id']) {
          $where .= ' AND id = ?';
          $sqlParameters[] = $this->parameters['id'];
        }

        // Select by acronym
        if($this->parameters['acronym']) {
          $where .= ' AND acronym LIKE ?';
          $sqlParameters[] = $this->parameters['acronym'];
        }

        // Select by name
        if($this->parameters['name']) {
          $where .= ' AND name LIKE ?';
          $sqlParameters[] = $this->parameters['name'];
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
     * Helper function to prepare number of rows query.
     *
     * Creates a query that returns the number of rows of the result. The query
     * is wrapped around the query to search for user(s) in the database.
     *
     * @return [] the SQL parameters and the related parameters to get the number
     *            of rows.
     */
    private function prepareNumberOfRowsQuery()
    {
        $query = $this->prepareQueryAndParams();
        $where = $query['where'] ? " WHERE 1 {$query['where']}" : null;

        $sql = "
          SELECT
            COUNT(id) AS rows
          FROM
          (
            $this->sqlOrig $where
          ) AS Rm_User
        ";

        return array('sql' => $sql, 'params' => $query['params']);
    }

    /**
     * Get the number of rows for the user search.
     *
     * Returns the number of rows(hits) for the user search.
     *
     * @return integer the number of rows(hits) for the user search.
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
     * Cleans the user profile parameters.
     *
     * Uses the function htmlentities to clean the user profile parameters.
     *
     * @param  [] $res the result of the user search from the database.
     *
     * @return [] the cleaned user profile parameters.
     */
    public function cleanProfileParameters($res)
    {
        $params = null;
        if (isset($res) && !empty($res)) {
            $param = $res[0];
            $params = array(
                'id' => htmlentities($param->id, null, 'UTF-8'),
                'acronym' => htmlentities($param->acronym, null, 'UTF-8'),
                'name' => htmlentities($param->name, null, 'UTF-8'),
                'info' => htmlentities($param->info, null, 'UTF-8'),
                'email' => htmlentities($param->email, null, 'UTF-8'),
                'published' => htmlentities($param->published, null, 'UTF-8'),
                'updated' => htmlentities($param->updated, null, 'UTF-8')
            );
        }

        return $params;
    }
}
