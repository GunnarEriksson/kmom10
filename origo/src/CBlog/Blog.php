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

    /**
     * Helper function to create basic search query.
     *
     * Creates an basic search query, which can be modified for specific
     * purposes.
     *
     * @return sql the basic search query.
     */
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
     * @param  string $slug             the slug that points out the blog post.
     * @param  Textfilter $textFilter   the textfilter obect for text filtering.
     * @param  string                   the category for the news blog post.
     *
     * @return html the blog post or all blog posts, if no slug is defined.
     */
    public function getBlogPostsFromSlug($parameters, $textFilter, $category = null)
    {
        $this->parameters = array_merge($this->parameters, $parameters);

        $query = $this->prepareSqlQury();
        try {
            $blogs = $this->getBlogsFromDb($query);
            $html = $this->createBlogPosts($blogs, $textFilter, $category);
        } catch (UnexpectedValueException $exception) {
            $html = $this->createErrorMessageBlog($exception->getMessage());
        }

        return $html;
    }

    /**
     * Helper funktion to prepare a query to search for news blog posts.
     *
     * Prepares the query to search for news blog posts depending on the
     * parameters.
     *
     * @return [] the SQL string and parameters to search for news blog posts.
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

    /**
     * Helper function to prepare the query and parameters.
     *
     * Prepares the query depending on the parameters sent to the parent function.
     * The parameters specifies the search for new blog posts in db.
     *
     * @return [] the WHERE SQL parameters and their values.
     */
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
        if (!isset($this->acronym)) {
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
     * is defined. Sends a request to check the number of blogs and sets the
     * parameter number of blogs. If no result is found, an unexpected value
     * exceptions is thrown.
     *
     * @param  []  $query the query with the SQL string and values for the search.
     *
     * @return [] the result from the database.
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
     * blog post if a slug is defined. Adds posibility to update and delete
     * blog posts depending of the rights of the logged in user.
     *
     * @param  [] $blogs                array of blog post(s) information
     * @param  Textfilter $textFilter   the textfilter obect for text filtering.
     * @param string $category          the category of the blog post(s).
     *
     * @return html the section with blog post(s) information.
     */
    private function createBlogPosts($blogs, $textFilter, $category)
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
                $data .= "<p>" . $this->createLink('Läs mer >>', $blog->slug, $this->parameters['category']) . "</p>";
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
                        <h2>{$this->createLink($title, $blog->slug, $this->parameters['category'])}{$removeButton}{$editButton}</h2>
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
     * Helper function to create a link.
     *
     * Creates a link (reference). Checks if the category should be added to
     * the reference.
     *
     * @param  var $item   the item to make a link of.
     * @param  string $slug the slug which is used as id of the blog posts.
     * @param  string $category the category the blogpost belongs to.
     *
     * @return html the item surrounded with a link.
     */
    private function createLink($item, $slug, $category)
    {
        $ref = null;

        if (isset($slug)) {
            $ref .= "news_blog.php?slug={$slug}";
            if (isset($category)) {
                $ref .= "&category={$category}";
            }
        } else if (isset($category)) {
            $ref .= "news_blog.php?category={$category}";
        }

        return "<a href='{$ref}'>{$item}</a>";
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
     * Helper function to check if the user has admin rights.
     *
     * Checks if the user has admin rights. If the user is not admin, the user
     * must be the author of the blog post(s).
     *
     * @param  string  $author the author of the blog post(s).
     *
     * @return boolean true if the user has admin rights for the blog post(s).
     */
    private function hasAdminRights($author)
    {
        $isAdminMode = false;
        if (isset($this->acronym)) {
            if (strcmp ($this->acronym , 'admin') === 0 || strcmp ($this->acronym , $author) === 0) {
                $isAdminMode = true;
            }
        }

        return $isAdminMode;
    }

    /**
     * Helper function to get the name for the acronym.
     *
     * Contacts the database to get the name for the acronym.
     *
     * @param  string $acronym the acronym to get the name of.
     * @return string the name for the acronym. The name is cleaned with the
     *                htmlentities function.
     */
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

    /**
     * Creates a news blog category form.
     *
     * Gets all categories from the database and creates a list of the categories.
     *
     * @return html A list of all blog post categories.
     */
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

    /**
     * Helper function to fetch all categories from the database.
     *
     * Gets all categories from the database.
     *
     * @return [] the array of all categories.
     */
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

    /**
     * Gets the title for a slug.
     *
     * Sends a request to the database to get the title of the blog post for
     * the slug.
     *
     * @param  string $slug the slug to find the title for.
     *
     * @return string the title for the slug if found, otherwise null.
     */
    public function getTitleBySlug($slug)
    {
        $sql = 'SELECT title FROM Rm_Content WHERE slug = ?';

        if($slug && !empty($slug)) {
          $sqlParameters[] = $slug;
        }

        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, $sqlParameters);

        if ($res && !empty($res)) {
            $title = $res[0]->title;
        } else {
            $title = null;
        }

        return $title;
    }
}
