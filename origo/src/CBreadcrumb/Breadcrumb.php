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

    public function __construct($db, $galleryPath, $pathParams, $menu)
    {
        $this->db = $db;
        $this->menu = $menu;
        $this->baseFileName = $this->getFileNameFromPagePath($galleryPath);
        $default = $this->createDefaultParameters();
        $this->pathParameters = array_merge($default, $pathParams);
    }

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

    private function getFileNameFromPagePath($path)
    {
        $pos = strpos($path, self::BASE_FOLDER);
        $name = substr($path, $pos + strlen(self::BASE_FOLDER) + 1);

        return $name;
    }

    /**
     * Create a breadcrumb of the gallery query path.
     *
     * @return string html with ul/li to display the thumbnail.
     */
    public function createMovieBreadcrumb()
    {
        $baseFileName = $this->getPageTitleFromFile($this->baseFileName);
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

    private function getMovieTitleFromId($id)
    {
        $parameters = array('id' => $id);
        $movieSearch = new MovieSearch($this->db, $parameters);

        return $movieSearch->getTitleById();
    }

    private function getPageTitleFromFile($fileName) {

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

    private function removeFileNameExtensions($fileName) {
        if (strpos($fileName, '.php') !== false) {
            $fileName = substr($fileName, 0, -4);
        }
        return $fileName;
    }

    public function createNewsBlogBreadcrumb()
    {
        $baseFileName = $this->getPageTitleFromFile($this->baseFileName);
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

    private function getNewsTitleFromSlug($slug)
    {
        $blog = new Blog($this->db);

        return $blog->getTitleBySlug($slug);
    }
}
