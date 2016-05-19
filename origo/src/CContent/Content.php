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
     * @return boolean true if the reset of db was successful, false otherwise.
     */
    public function resetContent()
    {
        $this->dropContentTableIfExists();
        $res = $this->createContentTable();
        if ($res) {
            $res = $this->setContentDefaultValues();
        }

        return $res;
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
        $sql = 'DROP TABLE IF EXISTS Content;';

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
            CREATE TABLE Content
            (
                id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
                slug CHAR(80) UNIQUE,
                url CHAR(80) UNIQUE,

                type CHAR(80),
                title VARCHAR(80),
                data TEXT,
                filter CHAR(80),
                author CHAR(80),

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
            INSERT INTO Content (slug, url, type, title, data, filter, published, created) VALUES
            ('hem', 'hem', 'page', 'Hem', "Detta är min hemsida. Den är skriven i [url=http://en.wikipedia.org/wiki/BBCode]bbcode[/url] vilket innebär att man kan formattera texten till [b]bold[/b] och [i]kursiv stil[/i] samt hantera länkar.\n\nDessutom finns ett filter 'nl2br' som lägger in <br>-element istället för \\n, det är smidigt, man kan skriva texten precis som man tänker sig att den skall visas, med radbrytningar.", 'bbcode,nl2br', NOW(), NOW()),
            ('om', 'om', 'page', 'Om', "Detta är en sida om mig och min webbplats. Den är skriven i [Markdown](http://en.wikipedia.org/wiki/Markdown). Markdown innebär att du får bra kontroll över innehållet i din sida, du kan formattera och sätta rubriker, men du behöver inte bry dig om HTML.\n\nRubrik nivå 2\n-------------\n\nDu skriver enkla styrtecken för att formattera texten som **fetstil** och *kursiv*. Det finns ett speciellt sätt att länka, skapa tabeller och så vidare.\n\n###Rubrik nivå 3\n\nNär man skriver i markdown så blir det läsbart även som textfil och det är lite av tanken med markdown.", 'markdown', NOW(), NOW()),
            ('blogpost-1', NULL, 'post', 'Välkommen till min blogg!', "Detta är en bloggpost.\n\nNär det finns länkar till andra webbplatser så kommer de länkarna att bli klickbara.\n\nhttp://dbwebb.se är ett exempel på en länk som blir klickbar.", 'clickable,nl2br', NOW(), NOW()),
            ('blogpost-2', NULL, 'post', 'Nu har sommaren kommit', "Detta är en bloggpost som berättar att sommaren har kommit, ett budskap som kräver en bloggpost.", 'nl2br', NOW(), NOW()),
            ('blogpost-3', NULL, 'post', 'Nu har hösten kommit', "Detta är en bloggpost som berättar att hösten har kommit, ett budskap som kräver en bloggpost", 'nl2br', NOW(), NOW());
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

        $sql = '
            INSERT INTO Content (title, slug, url, data, type, filter, author, published, created, updated)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NULL);
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
    public function updateContent($params)
    {
        $sql = '
            UPDATE Content SET
                title   = ?,
                slug    = ?,
                url     = ?,
                data    = ?,
                type    = ?,
                filter  = ?,
                author  = ?,
                published = ?,
                updated = NOW()
            WHERE
                id = ?
        ';

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
        $sql = 'SELECT * FROM Content WHERE id = ?';
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
            UPDATE Content SET
                title   = ?,
                slug    = ?,
                url     = ?,
                data    = ?,
                type    = ?,
                filter  = ?,
                author  = ?,
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
        $sql = 'SELECT *, (published <= NOW()) AS available FROM Content';

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


}
