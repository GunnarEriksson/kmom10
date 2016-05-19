<?php
/**
 * Database wrapper, provides a database API for the framework but hides details of implementation.
 *
 */
class Database
{
    private $options;                   // Options used when creating the PDO object
    private $db   = null;               // The PDO object
    private $stmt = null;               // The latest statement used to execute a query
    private static $numQueries = 0;     // Count all queries made
    private static $queries = array();  // Save all queries for debugging purpose
    private static $params = array();   // Save all parameters for debugging purpose

    /**
    * Constructor creating a PDO object connecting to a choosen database.
    *
    * Takes an array of option as argument. If options is missing, the options
    * is replaced with default values. Reads debug information from session if any.
    *
    * @param array $options containing details for connecting to the database.
    *
    */
    public function __construct($options)
    {
        $default = array(
            'dsn' => null,
            'username' => null,
            'password' => null,
            'driver_options' => null,
            'fetch_style' => PDO::FETCH_OBJ,
        );

        $this->options = array_merge($default, $options);
        try {
            $this->db = new PDO($this->options['dsn'], $this->options['username'], $this->options['password'], $this->options['driver_options']);
        }
        catch(Exception $e) {
            //throw $e; // For debug purpose, shows all connection details
            throw new PDOException('Could not connect to database, hiding connection details.'); // Hide connection details.
        }

        $this->db->SetAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, $this->options['fetch_style']);

        // Get debug information from session if any.
        if(isset($_SESSION['Database'])) {
            self::$numQueries = $_SESSION['Database']['numQueries'];
            self::$queries    = $_SESSION['Database']['queries'];
            self::$params     = $_SESSION['Database']['params'];
            unset($_SESSION['Database']);
        }
    }

    /**
     * Get number of queries made towards the database.
     *
     * Returns the number of queries that are done against the database.
     *
     * @return integer the number of queries against the database.
     */
    public function getNumQueries() { return self::$numQueries; }

    /**
     * Get all queries made towards the database.
     *
     * Returns all queries that are made towards the database. Can be used
     * for debugging purpose.
     *
     * @return [] the queries made towards the database.
     */
    public function getQueries() { return self::$queries; }

    /**
    * Get a html representation of all queries made, for debugging and analysing purpose.
    *
    * @return string with html.
    */
    public function dump()
    {
        $html  = '<p><i>You have made ' . self::$numQueries . ' database queries.</i></p><pre>';
        foreach(self::$queries as $key => $val) {
            $params = empty(self::$params[$key]) ? null : htmlentities(print_r(self::$params[$key], 1), null, 'UTF-8') . '<br/><br/>';
            $html .= htmlentities($val, null, 'UTF-8') . '<br/><br/>' . "Params: $params";
        }

        return $html . '</pre>';
    }

    /**
    * Save debug information in session, useful as a flashmemory when redirecting to another page.
    *
    * @param string $debug enables to save some extra debug information.
    */
    public function saveDebug($debug=null)
    {
        if($debug) {
            self::$queries[] = $debug;
            self::$params[] = null;
        }
        self::$queries[] = 'Saved debuginformation to session.';
        self::$params[] = null;
        $_SESSION['Database']['numQueries'] = self::$numQueries;
        $_SESSION['Database']['queries']    = self::$queries;
        $_SESSION['Database']['params']     = self::$params;
    }

    /**
    * Execute a select-query with arguments and return the resultset.
    *
    * @param string $query the SQL query with ?.
    * @param array $params array which contains the argument to replace ?.
    * @param boolean $debug defaults to false, set to true to print out the sql query before executing it.
    * @param int $fetchStyle can be changed by sending in arguments.
    * @return array with resultset.
    */
    public function executeSelectQueryAndFetchAll($query, $params=null, $debug=false, $fetchStyle=null)
    {
        // Make the query
        $this->stmt = $this->db->prepare($query);
        $this->stmt->execute($params);
        $res = $this->stmt->fetchAll($fetchStyle);
        

        // Log details on the query
        $rows = count($res);
        $logQuery = $query . "\n\nResultset has $rows rows.";
        self::$queries[] = $logQuery;
        self::$params[]  = $params;
        self::$numQueries++;

        // Debug if set
        if($debug) {
            echo "<p>Query = <br/><pre>{$logQuery}</pre></p><p>Num query = " . self::$numQueries . "</p><p><pre>".print_r($params, 1)."</pre></p>";
        }

        return $res;
    }

    /**
    * Execute a SQL-query and ignore the resultset.
    *
    * @param string $query the SQL query with ?.
    * @param array $params array which contains the argument to replace ?.
    * @param boolean $debug defaults to false, set to true to print out the sql query before executing it.
    * @return boolean returns TRUE on success or FALSE on failure.
    */
    public function executeQuery($query, $params = array(), $debug=false)
    {
        // Make the query
        $this->stmt = $this->db->prepare($query);
        $res = $this->stmt->execute($params);

        // Log details on the query
        $error = $res ? null : "\n\nError in executing query: " . $this->errorCode() . " " . print_r($this->errorInfo(), 1);
        $logQuery = $query . $error;
        self::$queries[] = $logQuery;
        self::$params[]  = $params;
        self::$numQueries++;

        // Debug if set
        if($debug) {
            echo "<p>Query = <br/><pre>".htmlentities($logQuery)."</pre></p><p>Num query = " . self::$numQueries . "</p><p><pre>".htmlentities(print_r($params, 1))."</pre></p>";
        }

        return $res;
    }

    /**
    * Return last insert id, see PDO::LastInsertId().
    *
    * @return string representation of id of last inserted row.
    */
    public function lastInsertId()
    {
        return $this->db->lastInsertid();
    }

    /**
    * Return rows affected of last INSERT, UPDATE, DELETE, see PDOStatment::rowCount().
    *
    * @return int number of affected rows of last statement.
    */
    public function rowCount()
    {
        return is_null($this->stmt) ? 0 : $this->stmt->rowCount();
    }

    /**
    * Return error code of last unsuccessful statement, see PDO::errorCode().
    *
    * @return mixed null or the error code.
    */
    public function errorCode()
    {
        return $this->stmt->errorCode();
    }

    /**
    * Return textual representation of last error, see PDO::errorInfo().
    *
    * @return array with information on the error.
    */
    public function errorInfo()
    {
        return $this->stmt->errorInfo();
    }
}
