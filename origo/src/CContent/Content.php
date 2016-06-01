<?php
/**
 * Content, handles the content of pages and blogposts
 *
 */
class Content
{
    private $db;

    /**
     * Constructor
     *
     * @param Database $db the database object.
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Resets the content.
     *
     * Sends two requests to the data base to reset the database to the
     * default values.
     *
     * @return string the result of setting the database to the default values.
     */
    public function resetContentInDb()
    {
        if ($this->isAdminMode()) {
            $message = "Databasen kunde EJ återställas till dess grundvärden";

            $this->dropContentTableIfExists();
            $res = $this->createContentTable();
            if ($res) {
                $res = $this->setContentDefaultValues();
                if ($res) {
                    $message = "Databas återställd till dess grundvärden";
                }
            }
        } else {
            $message = "Du måste vara inloggad som admin för att kunna sätta databasen till dess grundvärden!";
        }

        return $message;
    }

    /**
     * Helper function to check if the status is admin mode.
     *
     * Checks if the user has checked in as admin.
     *
     * @return boolean true if as user is checked in as admin, false otherwise.
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
     * Helper function to drop the content table if exists.
     *
     * Sends a query to delete the content table if it exists.
     *
     * @return boolean true if the content table was deleted, false otherwise, which
     *                 could be that the content table is not existing.
     */
    private function dropContentTableIfExists()
    {
        $sql = 'DROP TABLE IF EXISTS Rm_Content;';

        $this->db->executeQuery($sql);
    }

    /**
     * Helper function to create a content tabble.
     *
     * Sends a query to the database to create the content table.
     *
     * @return boolean true if the content table was created, false otherwise.
     */
    private function createContentTable()
    {
        $sql = '
            CREATE TABLE Rm_Content
            (
                id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
                slug CHAR(80) UNIQUE,
                url CHAR(80) UNIQUE,

                type CHAR(80),
                title VARCHAR(80),
                data TEXT,
                filter CHAR(80),
                author CHAR(80),
                category CHAR(20),

                published DATETIME,
                created DATETIME,
                updated DATETIME,
                deleted DATETIME

            ) ENGINE INNODB CHARACTER SET utf8;
        ';

        return $this->db->executeQuery($sql);
    }

    /**
     * Helper function to create fill the content table with default values.
     *
     * Sends a query to the database to fill the content table with default
     * values.
     *
     * @return boolean true if the content table was filled with default values, false otherwise.
     */
    private function setContentDefaultValues()
    {
        $sql = <<<EOD
            INSERT INTO Rm_Content (slug, url, type, title, data, filter, author, category, published, created) VALUES
                ('blogpost-1', NULL, 'post', 'Ny hemsida klar', "Vi är glada att kunna meddela att Rental Movies nya hemsida är klar.\n\nVi hoppas att ni finner den bättre än vår gamla hemsida och gör det enklare för er att hitta de filmer som ni vill se. Nytt är också en nyhetssida där ni kan ta del av olika nyheter, erbjudanden och övrig information.\n\nÄr ni medlem hos oss, så kan ni själva skriva egna meddelanden under nyheter.\n\nVälkommen till oss önskar personalen på Rental Movies.", 'nl2br', 'admin', 'information', '2016-05-16 14:35:29', '2016-05-16 14:35:29'),
                ('blogpost-2', NULL, 'post', 'Vi firar Rental Movies nya hemsida', "För att fira vår nya hemsida så kommer vi att erbjuda alla medlemmar 20% rabatt på alla filmer under två veckor. Vi kommer också under de här två veckorna komma ut med ett specialerbjudande varje dag. Vi rekommenderar därför att du tittar in på vår hemsida för att ta del av dessa erbjudanden\n\nVänliga hälsningar\nPersonalen på Rental Movies", 'nl2br', 'admin', 'erbjudande', '2016-05-16 15:25:20', '2016-05-16 15:25:20'),
                ('blogpost-3', NULL, 'post', 'Djungelboken är äntligen här', "Nu kan du se Djungelboken hos oss på Rental Movies.\n\nFilmen är baserad på boken av Rudyard Kipling och handlar om en föräldralös pojke växer upp i djungeln och uppfostras av vargar, björnar och en svart panter.\n\nFilmen är inspelad med den senaste tekniken där man blandar riktiga skådespelare med datoranimerad grafik och får det att se naturligt ut, vilket har varit ett problem förr. Så missa inte den här filmen som är både för små som stora filmälskare.\n\nVänliga hälsningar\nPersonalen på Rental Movies", 'nl2br', 'admin', 'nyhet', '2016-05-17 11:05:19', '2016-05-17 11:05:19'),
                ('blogpost-4', NULL, 'post', 'Sommarerbjudande', "Vi önskar alla medlemmar en riktigt skön sommar, men tyvärr kan vi inte bestämma över vädret. Vi vill därför på Rental Movies vill erbjuda alla medlemmar 15% rabatt på alla filmer under juli månad. Förhoppningsvis kan en film göra väntan på solen lite kortare. Erbjudandet gäller naturligtvis även om det är dagar under juli med strålande solsken. Film kan man titta på oavsett vädret.\n\nSköna sommarhälsningar\n\Personalen på Rental Movies", 'nl2br', 'admin', 'erbjudande', '2016-05-17 13:35:41', '2016-05-17 13:35:41'),
                ('blogpost-5', NULL, 'post', 'Filmkalender', "Vi vill meddela att på Rental Movies hemsida finns nu en filmkalender.\n\nVarje månad har en månadens film som vi erbjuder 15% rabatt om du vill se filmen under den månaden. Kalendern hittar du under rubriken kalender i våran meny.\n\nVänliga hälsningar\nPersonalen på Rental Movies", 'nl2br', 'admin', 'information', '2016-05-19 12:43:37', '2016-05-19 12:43:37'),
                ('blogpost-6', NULL, 'post', 'Testar nyhetsbloggen', "Vill bara testa nyhetsbloggen där alla medlemmar kan lägga in meddelanden. Nu när jag ändå är här så kan jag rekommendera en film, nämligen Eye in the sky. En grymt spännande film för er som gillar drama och thriller. Det blir inte sämre att Helen Mirren är med i filmen, som jag tycker är en grymt bra skådespelare.\n\nThomas", 'nl2br', 'tompa', 'övrigt', '2016-05-20 14:15:22', '2016-05-20 14:15:22'),
                ('blogpost-7', NULL, 'post', 'Spela tärning och möjlighet till gratis film', "Tycker du om att spela spel? Då kan vårt tärningsspel vara någonting för dig. Tärningsspelet heter tärningsspel 100 och den medlem som har högstpoäng vid månadens slut får se en film gratis hos oss på Rental Movies. Tabellen rensas därefter och så kan ett nytt spel börja.\n\nVänliga hälsningar\nPersonalen på Rental Movies", 'nl2br', 'admin', 'nyhet', '2016-05-20 15:11:59', '2016-05-20 15:11:59')
            ;
EOD;

        return $this->db->executeQuery($sql);
    }

    /**
     * Creates new content.
     *
     * Sends a request to the database to create new content.
     *
     * @param  [] $params the array of content values.
     *
     * @return string the result of creating a new content.
     */
    public function createContent($params)
    {
        // Set slug to a slugified title
        $params[1] = $this->slugify($params[0]);

        if (strcmp($params[4], "post") === 0) {
            $params[2] = null; // Set url to null for blog posts.
        }

        // Convert filters from array to string
        if (!empty($params[5])) {
            $params[5] = implode(",", $params[5]);
        }

        $params[8] = $this->addTimeStampToDate($params[8]);

        $sql = '
            INSERT INTO Rm_Content (title, slug, url, data, type, filter, author, category, published, created, updated)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NULL);
        ';

        $res = $this->db->ExecuteQuery($sql, $params);

        if ($res) {
            $output = 'Informationen sparades.';
        } else {
            $output = 'Informationen sparades EJ.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre>';
        }

        return $output;
    }

    /**
     * Helper function to add time stamp to a date.
     *
     * Adds actual time stamp to a date. The time zone is Europe / Stockholm.
     *
     * @param datetime $date the date and time (Europe / Stockholm)
     */
    private function addTimeStampToDate($date)
    {
        if (strlen($date) > 0 && strlen($date) < 11) {
            $date = substr($date, 0, 10); // Get date.
            $date .= " ";
            date_default_timezone_set('Europe/Stockholm');
            $date .= date("H:i:s");
        }

        return $date;
    }

    /**
     * Helper function to create a slug of a string, to be used as url.
     *
     * @param  string $str the string to format as slug.
     *
     * @return str the formatted slug.
     */
    private function slugify($str)
    {
        $str = mb_strtolower(trim($str));
        $str = str_replace(array('å','ä','ö'), array('a','a','o'), $str);
        $str = preg_replace('/[^a-z0-9-]/', '-', $str);
        $str = trim(preg_replace('/-+/', '-', $str), '-');

        return $str;
    }

    /**
     * Updates the content.
     *
     * Sends a query to update the content for specific id in the content table.
     *
     * @param  [] $params the array of content values.
     * @return string the result of updating a specific content.
     */
    public function updateContentInDb($params)
    {
        $sql = '
            UPDATE Rm_Content SET
                title   = ?,
                slug    = ?,
                url     = ?,
                data    = ?,
                type    = ?,
                filter  = ?,
                author  = ?,
                category = ?,
                published = ?,
                updated = NOW(),
                deleted = ?
            WHERE
                id = ?
        ';

        // Slugify title
        $params[1] = $this->slugify($params[0]);

        // Convert filters from array to string
        if (!empty($params[5])) {
            $params[5] = implode(",", $params[5]);
        }

        // Add time stamp to published if missing.
        // Set deleted to null if the deleted post is published again.
        if (isset($params[8])) {
            $params[8] = $this->addTimeStampToDate($params[8]);
            $params[9] = null;
        }

        $res = $this->db->ExecuteQuery($sql, $params);

        if ($res) {
            $output = 'Informationen har uppdaterats.';
        } else {
            $output = 'Informationen uppdaterades EJ.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre>';
        }

        return $output;
    }

    /**
     * Gets the content for a specific id.
     *
     * Sends a query to get the content for specific id.
     *
     * @param  integer $id the id for the content.
     *
     * @return [] the array of content for a specific id.
     */
    public function selectContent($id)
    {
        $sql = 'SELECT * FROM Rm_Content WHERE id = ?';
        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, $id);

        return $res[0];
    }

    /**
     * Deletes the content.
     *
     * Sends a query to delete the content for specific id in the content table.
     * The content is not deleted from the table, instead the parameter deleted
     * is gets the current time stamp and the parameter published is set to null.
     * The content can be recreated by setting the parameter published to a current
     * time stamp.
     *
     * @param  [] $params the array of content values.
     * @return string the result of deleting a specific content.
     */
    public function deleteContent($params)
    {
        $sql = '
            UPDATE Rm_Content SET
                title   = ?,
                slug    = ?,
                url     = ?,
                data    = ?,
                type    = ?,
                filter  = ?,
                author  = ?,
                category = ?,
                published = NULL,
                updated = ?,
                deleted = NOW()
            WHERE
                id = ?
        ';

        $res = $this->db->ExecuteQuery($sql, $params);

        if ($res) {
            $output = 'Innehållet är borttaget.';
        } else {
            $output = 'Innehållet kunde EJ tas bort.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre>';
        }

        return $output;
    }

    /**
     * Gets the content list page.
     *
     * Creates an HTML page presenting all contents in the database as a list.
     *
     * @param  string $title the heading of the page.
     *
     * @return html the page presenting all contents in the database as a list.
     */
    public function getContentListPage($title)
    {
        $sql = $this->prepareContentListSqlQury();
        $content = $this->getContentListFromDb($sql);
        $contentList = $this->createContentList($content);
        $html = $this->createContentListPage($title, $contentList);

        return $html;
    }

    /**
     * Helper function to prepare the query for all published contents.
     *
     * Creates a SQL query to get all published contents from the db.
     *
     * @return SQL string the string to get all published contents from db.
     */
    private function prepareContentListSqlQury()
    {
        $sql = 'SELECT *, (published <= NOW()) AS available FROM Rm_Content';

        return $sql;
    }

    /**
     * Helper function to get content list from db.
     *
     * Sends a query to the db to get data for a content list.
     *
     * @param  SQL string $sql the string for the query.
     * @return [] the array of content list information.
     */
    private function getContentListFromDb($sql)
    {
        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql);

        return $res;
    }

    /**
     * Helper function to create a content list.
     *
     * Creates an HTML list of contents.
     *
     * @param  [] $content the array of content list information.
     *
     * @return html a list of contents.
     */
    private function createContentList($content)
    {
        $html = null;
        if (!empty($content)) {
            $html = '<ul>';
            foreach ($content as $key => $row) {
                $status = (!$row->available ? 'inte ' : null) . 'publicerad';
                $title = htmlentities($row->title, null, 'UTF-8');
                $url = $this->getUrlToContent($row);
                $html .= "<li>{$row->type} ({$status}): {$title} (<a href='{$url}'>visa</a> <a href='content_edit.php?id={$row->id}'>editera</a> <a href='content_delete.php?id={$row->id}'>radera</a>)</li>\n";
            }

            $html .= '</ul>';
        }

        return $html;
    }

    /**
     * Helper function to create a link to the content, based on its type.
     *
     * @param object $content to link to.
     * @return string with url to display content.
     */
    private function getUrlToContent($content) {
        switch($content->type) {
            case 'page': return "content_page.php?url={$content->url}"; break;
            case 'post': return "content_blog.php?slug={$content->slug}"; break;
            default: return null; break;
        }
    }

    /**
     * Helper function to create a page with a list of contents.
     *
     * @param  string $title the heading of the page.
     * @param  html $contentList the list of contents.
     *
     * @return html the page presenting the content list.
     */
    private function createContentListPage($title, $contentList)
    {
        $html = <<<EOD
        <h1>{$title}</h1>
        <p>Här är en lista på allt innehåll i databasen.</p>
        {$contentList}
        <p><a href='content_blog.php'>Visa alla bloggposter.</a></p>
EOD;

        return $html;
    }

    /**
     * Deletes content in the database.
     *
     * Deletes content in database using the id as a key.
     *
     * @param  [] $params the id for the content to delete.
     *
     * @return string a message with the result of deleteing the content.
     */
    public function eraseContent($params)
    {
        $sql = '
            DELETE FROM Rm_Content WHERE id = ?;
        ';

        $res = $this->db->ExecuteQuery($sql, $params);

        if ($res) {
            $message = 'Nyheten är bortagen från databas och kan ej återskapas!';
        } else {
            $message = 'Nyheten kunde EJ tas bort från databas!';
        }

        return $message;
    }


}
