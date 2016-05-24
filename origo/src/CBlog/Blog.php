<?php
/**
 * Blog, handles the blog posts stored in the database.
 *
 */
class Blog
{
    private $db;
    private $acronym;
    private $numOfBlogs;
    private $parameters;
    private $sqlOrig;
    private $limit;
    private $sort;

    /**
     * Constructor
     *
     * @param Database $db the database object.
     * @param string $acronym the acronym of the user, default null.
     */
    public function __construct($db, $acronym=null)
    {
        $this->db = $db;
        $this->acronym = $acronym;
        $this->parameters = $this->createDefaultParameters();
        $this->sqlOrig = $this->createOriginalSqlQuery();
        $this->sort = " ORDER BY UNIX_TIMESTAMP(GREATEST(COALESCE(published, 0), COALESCE(updated, 0), COALESCE(created, 0), COALESCE(deleted, 0))) DESC";
        $this->numOfBlogs = null;
        $this->limit = null;
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
        $default = array (
            'slug' => null,
            'hits' => null,
            'page' => null,
            'author' => null,
            'category' => null
        );

        return $default;
    }

    private function createOriginalSqlQuery()
    {
        $sqlOrig = '
        SELECT *
        FROM Rm_Content
        ';

        return $sqlOrig;
    }

    /**
     * Get blog post from slug.
     *
     * Returns the blog post defined by the slug. If no slug is defined, all
     * available blog posts is returned.
     *
     * @param  string $slug the slug that points out the blog post.
     * @param  Textfilter $textFilter the textfilter obect for text filtering.
     *
     * @return html the blog post or all blog posts, if no slug is defined.
     */
    public function getBlogPostsFromSlug($parameters, $textFilter)
    {
        $this->parameters = array_merge($this->parameters, $parameters);

        $query = $this->prepareSqlQury();
        try {
            $blogs = $this->getBlogsFromDb($query);
            $html = $this->createBlogPosts($blogs, $textFilter);
        } catch (UnexpectedValueException $exception) {
            $html = $this->createErrorMessageBlog($exception->getMessage());
        }

        return $html;
    }

    /**
     * Helper funktion to prepare the SQL query.
     *
     * Prepares the SQL query to get all blog posts or one blog post, if a slug
     * is defined.
     *
     * @param  string $slug the slug to point out a specific blog post.
     *
     * @return SQL string the string to find blog post or all blog posts.
     */
    private function prepareSqlQury()
    {
        $sqlOrig = $this->sqlOrig;
        $query = $this->prepareQueryAndParams();
        $where = $query['where'];
        $where = $where ? " WHERE {$where}" : null;
        $sql = $sqlOrig . $where . $this->sort . $this->limit;

        return array('sql' => $sql, 'params' => $query['params']);
    }

    private function prepareQueryAndParams()
    {
        $where = null;
        $sqlParameters = array();

        $where .= "type = 'post'";

        if ($this->parameters['slug']) {
            $where .= ' AND slug = ?';
            $sqlParameters[] = $this->parameters['slug'];
        } else {
            $where .= ' AND 1';
        }

        // Show created and deleted posts for logged in users.
        $acronym = isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;
        if (!isset($acronym)) {
            $where .= ' AND published <= NOW()';
        }

        // Lägg in created and deleted om man är inloggad.

        if($this->parameters['category']) {
          $where .= ' AND category = ?';
          $sqlParameters[] = $this->parameters['category'];
        }

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
     * Helper function to get blog post(s) from database.
     *
     * Sends a query to get all blog post or one specfic blog post, if a slug
     * is defined.
     *
     * @param  SQL string $sql the string to search for blog post(s).
     * @param  string $slug the slug to point out a specific blog post.
     *
     * @return [] array which includes information about the blog post(s).
     */
    private function getBlogsFromDb($query)
    {
        $res = $this->db->ExecuteSelectQueryAndFetchAll($query['sql'], $query['params']);

        if (isset($res[0])) {
            $numOfBlogsQuery = $this->prepareNumberOfBlogsQuery(array($this->parameters['slug']));
            $numOfBlogRes = $this->db->ExecuteSelectQueryAndFetchAll($numOfBlogsQuery['sql'], $numOfBlogsQuery['params']);
            $this->setNumberOfBlogs($numOfBlogRes);

            return $res;
        } else {
            if ($this->parameters['slug']) {
                throw new UnexpectedValueException('Det fanns inte en sådan bloggpost!');
            } else {
                throw new UnexpectedValueException('Det fanns inga bloggposter!');
            }
        }
    }

    /**
     * Helper function set number of blogs.
     *
     * Sets the number of blogs. If no result is found, the number is set to
     * zero.
     *
     * @param [] $res the result set from db containing number of blogs.
     */
    private function setNumberOfBlogs($res)
    {
        $this->numOfBlogs = 0;
        if (isset($res) && !empty($res)) {
            $this->numOfBlogs = $res[0]->rows;
        }
    }

    /**
     * Helper function to create the blog post(s).
     *
     * Creates an HTML section wich presents all the blog posts or one specific
     * blog post if a slug is defined.
     *
     * @param  [] $blogs array of blog post(s) information
     * @param  Textfilter $textFilter the textfilter obect for text filtering.
     *
     * @return html the section with blog post(s) information.
     */
    private function createBlogPosts($blogs, $textFilter)
    {
        $html = null;
        foreach($blogs as $blog) {
            $title  = htmlentities($blog->title, null, 'UTF-8');
            $author = htmlentities($blog->author, null, 'UTF-8');
            $category = htmlentities($blog->category, null, 'UTF-8');
            if (empty($author)) {
                $author = "anonym";
            }

            if (isset($blog->published) && isset($blog->updated)) {
                $updated = htmlentities($blog->updated, null, 'UTF-8');
                $status = "Uppdaterad: {$updated}";
            } else if (isset($blog->published)) {
                $published = htmlentities($blog->published, null, 'UTF-8');
                $status = "Publicerad: {$published}";
            } else if (isset($blog->deleted)) {
                $deleted = $published = htmlentities($blog->deleted, null, 'UTF-8');
                $status = "Borttagen: {$deleted}";
            } else {
                $created = $published = htmlentities($blog->created, null, 'UTF-8');
                $status = "Skapad: {$created}";
            }

            $data   = $textFilter->doFilter(htmlentities($blog->data, null, 'UTF-8'), $blog->filter);

            if (!$this->parameters['slug']) {
                $data = $this->getSubstring($data, 200);
                $data .= "<p><a href='news_blog.php?slug={$blog->slug}'>Läs mer >></a></p>";
            }

            $removeButton = null;
            $editButton = null;
            if ($this->hasAdminRights($author)) {
                $removeButton= "<a href='content_delete.php?id=" . htmlentities($blog->id) . "'><img class='news-blog-admin-button' src='img/icons/delete.png' title='Ta bort nyhet' alt='Ta_bort' /></a>";
                $editButton .= "<a href='content_edit.php?id=" . htmlentities($blog->id) . "'><img class='news-blog-admin-button' src='img/icons/edit.png' title='Uppdatera nyhet' alt='Uppdatera' /></a>";
            }

            $name = $this->getNameFromAcronym($author);



            $html .= <<<EOD
                <article class="blogpost">
                    <header>
                        <h2><a href='news_blog.php?slug={$blog->slug}'>{$title}</a>{$removeButton}{$editButton}</h2>
                    </header>
                    <p>{$data}</p>
                    <footer>
                        <p>
                            <span class='blog-info float-left'>Kategori: {$category}</span>
                            <span class='blog-info float-right'>{$status} av {$name}</span>
                        </p>
                    </footer>
                </article>
EOD;
        }

        return $html;
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

    private function hasAdminRights($author)
    {
        $isAdminMode = false;
        $acronym = isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;
        if (isset($acronym)) {
            if (strcmp ($acronym , 'admin') === 0 || strcmp ($acronym , $author) === 0) {
                $isAdminMode = true;
            }
        }

        return $isAdminMode;
    }

    private function getNameFromAcronym($acronym)
    {
        $sql = ' SELECT name FROM Rm_User WHERE acronym = ?';

        $params = array($acronym);
        $res = $this->db->executeSelectQueryAndFetchAll($sql, $params);

        $name = 'Anonym';
        if (isset($res) && count($res) > 0) {
            $name = htmlentities($res[0]->name, null, 'UTF-8');
        }

        return $name;
    }

    /**
     * Helper function to create an error message blog.
     *
     * Creats an blog message to inform than an error has occured.
     *
     * @param  string $errorMessage the error message to be displayed at the page.
     *
     * @return html the article presenting the error.
     */
    private function createErrorMessageBlog($errorMessage)
    {
        date_default_timezone_set('UTC');
        $dateTime = date("Y-m-d H:i:s");

        $this->numOfBlogs = 1;

        $html = <<<EOD
            <article class="blogpost">
                <header>
                    <h2><a href='#'>Meddelande</a></h2>
                </header>
                <p>{$errorMessage}</p>
                <footer>
                    <p>
                        <span class='blog-info float-left'>Kategori: meddelanden</span>
                        <span class='blog-info float-right'>Publicerad: {$dateTime} av system</span>
                    </p>
                </footer>
            </article>
EOD;

        return $html;
    }

    /**
     * Helper method to prepare number of blogs query.
     *
     * Prepares the SQL string to get the number of blogs(hits) for the blog
     * search in the database.
     *
     * @return [] the array containing the SQL string and parameters, if included,
     *            to get number of blogs(hits).
     */
    private function prepareNumberOfBlogsQuery()
    {
        $query = $this->prepareQueryAndParams();
        $where =  $query['where'] ? " WHERE  {$query['where']}" : null;

        $sql = "
          SELECT
            COUNT(id) AS rows
          FROM
          (
            $this->sqlOrig $where
          ) AS Rm_Content
        ";

        return array('sql' => $sql, 'params' => $query['params']);
    }

    /**
     * Get the number of blogs.
     *
     * Returns the number of rows(hits) for the movie search.
     *
     * @return integer the number of rows(hits) for the movie search.
     */
    public function getNumberOfBlogs()
    {
        return $this->numOfBlogs;
    }

    /**
     * Gets the maximum number of pages.
     *
     * Returns the maximum number of pages depending how many rows that should
     * be shown in the blog. Is used for paging.
     *
     * @return integer the maximum number of pages depending how many rows
     *                 that should be shown in the blog page.
     */
    public function getMaxNumPages()
    {
        return ceil($this->numOfBlogs / $this->parameters['hits']);
    }

    public function createNewsBlogCategoryForm()
    {
        $html = '<form class="news-blog-category-form">';
        $html .= '<fieldset>';
        $html .= '<legend>Kategorier</legend>';
        $html .= '<input type=hidden name=hits value="' . htmlentities($this->parameters['hits']) . '"/>';
        $html .= '<input type=hidden name=page value="1"/>';
        $html .= "<ul class='categories'><li><a href='?genre='>alla</a></li>";
        $categories = $this->fetchAllCategories();
        foreach ($categories as $key => $category) {
            if (strcasecmp($this->parameters['category'], $category) === 0) {
                $category = htmlentities($category, null, 'UTF-8');
                $html .= "<li><span class=selected>{$category}</span></li>";
            } else {
                $category = htmlentities($category, null, 'UTF-8');
                $html .= "<li><a href='?category={$category}'>{$category}</a></li>";
            }
        }
        $html .= '</ul>';
        $html .= '</fieldset>';
        $html .= '</form>';

        return $html;
    }

    private function fetchAllCategories()
    {
        $sql = '
            SELECT * FROM Rm_Category;
        ';

        $res = $this->db->executeSelectQueryAndFetchAll($sql);

        $categoriesArray = array();
        foreach ($res as $key => $row) {
            $categoriesArray[] = $row->name;
        }

        return $categoriesArray;
    }
}
