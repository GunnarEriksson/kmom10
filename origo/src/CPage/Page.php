<?php
/**
 * Content, handles the pages stored in the database.
 *
 */
class Page
{
    private $db;
    private $acronym;
    private $pageTitle;

    /**
     * Constructor
     *
     * @param Database $db the database object.
     * @param string $acronym the acronym of the user.
     */
    public function __construct($db, $acronym=null)
    {
        $this->db = $db;
        $this->acronym = $acronym;
        $this->pageTitle = null;
    }

    /**
     * Get page from url.
     *
     * Creates the specific page pointed out by the url.
     *
     * @param  string $url the url for the specific page.
     * @param  Textfilter $textFilter the textfilter obect for text filtering.
     *
     * @return html the page.
     */
    public function getPageFromUrl($url, $textFilter)
    {
        $sql = $this->prepareSqlQury();
        try {
            $page = $this->getPageFromDb($sql, $url);
            $html = $this->createPage($page, $textFilter);
        } catch (UnexpectedValueException $exception) {
            $html = $this->createErrorMessagePage($exception->getMessage());
        }

        return $html;
    }

    /**
     * Helper function to create a query for a specific page pointed out by the url.
     *
     * @return SQL string the string to get information for a specfic page.
     */
    private function prepareSqlQury()
    {
        $sql = "
        SELECT *
        FROM Content
        WHERE
          type = 'page' AND
          url = ? AND
          published <= NOW();
        ";

        return $sql;
    }

    /**
     * Helper function to get information about a page from db.
     *
     * @param  string $sql the SQL string to send to the db.
     * @param  string $url the url for the specfic page.
     *
     * @return [] the array of information for the specfic page.
     */
    private function getPageFromDb($sql, $url)
    {
        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, array($url));
        if (isset($res[0])) {
            return $res[0];
        } else {
            throw new UnexpectedValueException('Det finns inget innehåll med sådant id!');
        }
    }

    /**
     * Helper function to create a page.
     *
     * @param  [] $page the array of information for the page.
     * @param  Textfilter $textFilter the textfilter obect for text filtering.
     * @return html the page.
     */
    private function createPage($page, $textFilter)
    {
        $title  = htmlentities($page->title, null, 'UTF-8');
        $this->pageTitle = $title;
        $author = htmlentities($page->author, null, 'UTF-8');
        if (empty($author)) {
            $author = "Anonym";
        }

        $published = htmlentities($page->published, null, 'UTF-8');
        $data   = $textFilter->doFilter(htmlentities($page->data, null, 'UTF-8'), $page->filter);
        $editLink = $this->acronym ? "<a href='content_edit.php?id={$page->id}'>Uppdatera sidan</a>" : null;

        $html = <<<EOD

        <article>
            <header>
                <h1>{$title}</h1>
            </header>
            <p class="font-small-italic">Av {$author}</p>
            <p class="font-small-italic">{$published}</p>
            {$data}
            <footer>
                <p>{$editLink}</p>
            </footer>
        </article>
EOD;

        return $html;
    }

    /**
     * Helper function to create an error message page.
     *
     * Creats an HTML arcticle to inform than an error has occured.
     *
     * @param  string $errorMessage the error message to be displayed at the page.
     *
     * @return html the article presenting the error.
     */
    private function createErrorMessagePage($errorMessage)
    {
        $this->pageTitle = "Fel har uppstått!";

        $html = <<<EOD
        
        <article>
            <header>
                <h1>Fel har uppstått</h1>
                </header>
                <p>{$errorMessage}</p>
        </article>
EOD;

        return $html;
    }

    /**
     * Get the page title.
     *
     * Returns the title of the page.
     *
     * @return string the title of the page.
     */
    public function getPageTitle()
    {
        return $this->pageTitle;
    }
}
