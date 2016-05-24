<?php
/**
 * Front page, handles the information for the front page of the website.
 *
 */
class FrontPage
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function generateMovieSections($parameters, $image=null, $class=null)
    {
        $res = $this->getMovies($parameters);

        return $this->createMovieSections($res, $image, $class);
    }

    private function getMovies($parameters)
    {
        $movieSearch = new MovieSearch($this->db, $parameters);

        return $movieSearch->searchMovie();
    }

    private function createMovieSections($res, $image, $class)
    {
        $html = null;
        foreach ($res as $key => $row) {
            $html .= "<a href='movie.php?id={$row->id}'>";
            $html .= "<div class='{$class}'>";
            $imgSpec = $this->setImageSpecifications();
            $html .= "<img src='img.php?src=" . htmlentities($row->image) . "{$imgSpec}' alt='" . htmlentities($row->title) . "'/>";
            $html .= "<br/>" . htmlentities($row->title);
            $html .= "</div>";
            $html .= "</a>";
        }

        return $html;
    }

    private function setImageSpecifications()
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

    public function generateGenresList()
    {
        $movieSearch = new MovieSearch($this->db, array());
        $res = $movieSearch->getAllGenres();

        return $this->createGenresList($res);
    }

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
