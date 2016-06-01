<?php
/**
 * Provides breadcrumb for navigating.
 */
class Breadcrumb
{
    const BASE_FOLDER = 'webroot';

    private $db;
    private $title;
    private $menu;
    private $baseFileName;
    private $pathParameters;

    /**
     * Constructor
     *
     * @param Database $db the database object.
     * @param string $galleryPath the path to page controller.
     * @param string $pathParams  the path parameters to the image or blog posts.
     * @param [] $menu  the menu with menu items.
     */
    public function __construct($db, $galleryPath, $pathParams, $menu)
    {
        $this->db = $db;
        $this->menu = $menu;
        $this->baseFileName = $this->getFileNameFromPagePath($galleryPath);
        $default = $this->createDefaultParameters();
        $this->pathParameters = array_merge($default, $pathParams);
    }

    /**
     * Helper function to create default parameters.
     *
     * Creates an array of parmeters set to null.
     *
     * @return [] parameters set to null as default.
     */
    private function createDefaultParameters()
    {
        $default = array(
            'id' => null,
            'genre' => null,
            'slug' => null,
            'category' => null
        );

        return $default;
    }

    /**
     * Helper function to get file name from path.
     *
     * Gets the file name from the sidecontroller that is uses as base.
     *
     * @param  string $path path to the sidecontroller used as base.
     *
     * @return string the name of the sidecontroller used as base.
     */
    private function getFileNameFromPagePath($path)
    {
        $pos = strpos($path, self::BASE_FOLDER);
        $name = substr($path, $pos + strlen(self::BASE_FOLDER) + 1);

        return $name;
    }

    /**
     * Create a breadcrumb of the moive path.
     *
     * Creates a list of items decsribing the path. The list contains name
     * and reference.
     *
     * @return string html with ul/li to display the thumbnail.
     */
    public function createMovieBreadcrumb()
    {
        $baseFileName = $this->getPageTitleFromFileName($this->baseFileName);
        $breadcrumb = "<ul class='breadcrumb'>\n<li><a href='$this->baseFileName.php'>$baseFileName</a> »</li>\n";

        $path = null;
        $ref = null;
        if (isset($this->pathParameters['genre'])) {
            $path .= $this->pathParameters['genre'];
            $ref .= "?genre=" . $this->pathParameters['genre'];
            $breadcrumb .= "<li><a href='$ref'>$path</a> » </li>\n";
        }

        if (isset($this->pathParameters['id'])) {
            $title = $this->getMovieTitleFromId($this->pathParameters['id']);
            if (isset($path) && isset($ref)) {
                $path = "$title";
                $ref .= "&id=" . $this->pathParameters['id'];
                $breadcrumb .= "<li><a href='$ref'>$path</a> » </li>\n";
            } else {
                $path .= $title;
                $ref .= "?id=" . $this->pathParameters['id'];
                $breadcrumb .= "<li><a href='$ref'>$path</a> » </li>\n";
            }
        }

        $breadcrumb .= "</ul>\n";

        return $breadcrumb;
    }

    /**
     * Helper function to get the title from the movies id.
     *
     * Uses the id of the movie to get the title.
     *
     * @param  int $id  the id of the movie.
     *
     * @return string   the title of the movie.
     */
    private function getMovieTitleFromId($id)
    {
        $parameters = array('id' => $id);
        $movieSearch = new MovieSearch($this->db, $parameters);

        return $movieSearch->getTitleById();
    }

    /**
     * Helper function to get the page title from file name.
     *
     * Uses the menu from the navigation bar to map file name to name
     * in the menu.
     *
     * @param  string $fileName the name of the file
     * @return string the files title in the menu bar.
     */
    private function getPageTitleFromFileName($fileName) {

        $menuTitle = $this->removeFileNameExtensions($fileName);
        $menuItems = $this->menu['items'];
        foreach ($menuItems as $key => $menuItem) {
            $itemFileName = $this->removeFileNameExtensions($menuItem['url']);
            if (strcmp($itemFileName , $menuTitle) === 0) {
                $menuTitle = $menuItem['title'];
            }
        }

        return $menuTitle;
    }

    /**
     * Helper function to remove file name extension.
     *
     * Removes the extension .php from the filename.
     *
     * @param  string $fileName the name of the file.
     *
     * @return the file name without extension.
     */
    private function removeFileNameExtensions($fileName) {
        if (strpos($fileName, '.php') !== false) {
            $fileName = substr($fileName, 0, -4);
        }
        return $fileName;
    }

    /**
     * Create a breadcrumb of the news blog post path.
     *
     * Creates a list of items decsribing the path. The list contains name
     * and reference.
     *
     * @return string html with ul/li to display the thumbnail.
     */
    public function createNewsBlogBreadcrumb()
    {
        $baseFileName = $this->getPageTitleFromFileName($this->baseFileName);
        $breadcrumb = "<ul class='breadcrumb'>\n<li><a href='$this->baseFileName.php'>$baseFileName</a> »</li>\n";

        $path = null;
        $ref = null;
        if (isset($this->pathParameters['category'])) {
            $path .= $this->pathParameters['category'];
            $ref .= "?category=" . $this->pathParameters['category'];
            $breadcrumb .= "<li><a href='$ref'>$path</a> » </li>\n";
        }

        if (isset($this->pathParameters['slug'])) {
            $title = $this->getNewsTitleFromSlug($this->pathParameters['slug']);
            if (isset($path) && isset($ref)) {
                $path = "$title";
                $ref .= "&slug=" . $this->pathParameters['slug'];
                $breadcrumb .= "<li><a href='$ref'>$path</a> » </li>\n";
            } else {
                $path .= $title;
                $ref .= "?slug=" . $this->pathParameters['slug'];
                $breadcrumb .= "<li><a href='$ref'>$path</a> » </li>\n";
            }
        }

        $breadcrumb .= "</ul>\n";

        return $breadcrumb;
    }

    /**
     * Helper function to get the news title from the slug.
     *
     * Gets the title of the blog post by using the slug as key.
     *
     * @param  string $slug the slug of the blog post.
     *
     * @return string the title of the blog post.
     */
    private function getNewsTitleFromSlug($slug)
    {
        $blog = new Blog($this->db);

        return $blog->getTitleBySlug($slug);
    }
}
