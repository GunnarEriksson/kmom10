<?php
/**
 * Front page, handles the movie information for the front page of the website.
 *
 */
class FrontPage
{
    private $db;

    /**
     * Constructor
     *
     * Initiates the database.
     *
     * @param Database $db the database object.
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Generates the movie section for movies.
     *
     * Search for movies and presents the movies as image tags with a newline
     * in between.
     *
     * @param  [] $parameters   search parameters to search for movies.
     * @param  [] $imageSpec    the image specifications (height, width etc)
     * @param  string $class    the class to connect the section with CSS.
     *
     * @return html the movie section.
     */
    public function generateMovieSections($parameters, $imageSpec=null, $class=null)
    {
        $res = $this->getMovies($parameters);

        return $this->createMovieSections($res, $imageSpec, $class);
    }

    /**
     * Helper function to get movies from database.
     *
     * Searches for movies in the database.
     *
     * @param [] $parameters   search parameters to search for movies.
     *
     * @return [] the result from database.
     */
    private function getMovies($parameters)
    {
        $movieSearch = new MovieSearch($this->db, $parameters);

        return $movieSearch->searchMovie();
    }

    /**
     * Creates a movie section.
     *
     * Creates a movie section of movie images and the movies title. Reference is
     * included.
     *
     * @param  [] $res          the result of movies from the database.
     * @param  [] $imageSpec    the image specifications (height, width etc)
     * @param  string $class    the class to connect the section with CSS.
     *
     * @return html the movie section.
     */
    private function createMovieSections($res, $imageSpec, $class)
    {
        $html = null;
        foreach ($res as $key => $row) {
            $html .= "<a href='movie.php?id={$row->id}'>";
            $html .= "<div class='{$class}'>";
            $imgSpec = $this->setImageSpecifications($imageSpec);
            $html .= "<img src='img.php?src=" . htmlentities($row->image) . "{$imgSpec}' alt='" . htmlentities($row->title) . "'/>";
            $html .= "<br/>" . htmlentities($row->title);
            $html .= "</div>";
            $html .= "</a>";
        }

        return $html;
    }

    /**
     * Helper function to set the image specifications.
     *
     * Sets the image specifications for the img.php side controller. The parameters
     * makes it possible to specify the image height, width and if the image should
     * be sharpen or not.
     *
     * @param [] $parameters the image specifications.
     */
    private function setImageSpecifications($parameters)
    {
        $imgSpec = null;
        if (isset($parameters['width'])) {
            $imgSpec .= "&amp;width={$parameters['width']}";
        }

        if (isset($parameters['height'])) {
            $imgSpec .= "&amp;height={$parameters['height']}";
        }

        if (isset($parameters['sharpen']) && $parameters['sharpen']) {
            $imgSpec .= "&amp;sharpen";
        }

        return $imgSpec;
    }

    /**
     * Generates a genres list.
     *
     * Gets all valid genres, for the movies, from the database and generates
     * a list.
     *
     * @return html the list of all valid genres for the movies.
     */
    public function generateGenresList()
    {
        $movieSearch = new MovieSearch($this->db, array());
        $res = $movieSearch->getAllGenres();

        return $this->createGenresList($res);
    }

    /**
     * Helper function to generate a list of movie genres.
     *
     * @param  [] $res the result of movies from the database.
     *
     * @return html the movie genres list.
     */
    private function createGenresList($res)
    {
        $html = null;
        $html .= "<ul class='genres'>";
        foreach ($res as $key => $row) {
            $genre = htmlentities($row->name);
            $html .= "<li><a href='movie.php?genre={$genre}'>{$genre}</a></li>";
        }
        $html .= '</ul>';

        return $html;
    }

}
