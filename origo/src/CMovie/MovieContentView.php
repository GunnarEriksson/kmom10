<?php
/**
 * Movie content, handles the function to show one film with more detailed
 * information.
 *
 */
class MovieContentView
{
    private $parameters;

    /**
     * Constructor
     *
     * Initiates default parameters for a film.
     */
    public function __construct()
    {
        $this->parameters = $this->createDefaultParameters();
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
            'id' => null,
            'title' => null,
            'director' => null,
            'length' => null,
            'year' => null,
            'plot' => null,
            'image' => null,
            'subtext' => null,
            'speech' => null,
            'quality' => null,
            'format' => null,
            'price' => null,
            'imdb' => null,
            'youtube' => null,
            'genre' => null,
        );

        return $default;
    }

    /**
     * Generates the movie content view.
     *
     * Creates an article of details of the movie.
     *
     * @param  [] $res  the result set of parameters, from database, for a movie.
     * @return html     the article for the movie.
     */
    public function generateMovieContentView($res, $rentButton)
    {
        $dbParams =  (array) $res[0];
        $this->parameters = array_merge($this->parameters, $dbParams);
        $MovieContentView = "<article>";
        $MovieContentView .= "<div class='movie-info-container'>";
        $MovieContentView .= "<h2>" . htmlentities($this->parameters['title']);

        if ($this->isAdminMode()) {
            $MovieContentView .= "<a href='movie_delete.php?id=" . htmlentities($this->parameters['id']) . "'><img class='movie-admin-button' src='img/icons/delete.png' title='Ta bort film' alt='Ta_bort' /></a>";
            $MovieContentView .= "<a href='movie_edit.php?id=" . htmlentities($this->parameters['id']) . "'><img class='movie-admin-button' src='img/icons/edit.png' title='Uppdatera film' alt='Uppdatera' /></a>";
        }

        $MovieContentView .= "</h2>";
        $MovieContentView .= "<img class='movie-info-img' src='img.php?src=" . htmlentities($this->parameters['image']) . "' alt='" . htmlentities($this->parameters['title']) . "'/>";
        $MovieContentView .= "<span class='movie-plot'>" . htmlentities($this->parameters['plot']) . "</span>";
        $MovieContentView .= "</div>";
        $MovieContentView .= $this->createMovieInfoList();
        $MovieContentView .= "<p><b>Generes: </b>" . htmlentities($this->parameters['genre']) . "</p>";

        if ($this->isUserMode()) {
            $MovieContentView .= $rentButton;
        }

        $MovieContentView .= "</article>";

        return $MovieContentView;
    }

    /**
     * Helper function to check if the user has the rights of an administrator.
     *
     * Checks if the user has logged in as admin.
     *
     * @return boolean true if the user has logged in as admin, false otherwise.
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
     * Helper function to check if the user has user rights.
     *
     * Checks if the user has logged in.
     *
     * @return boolean true if the user has user rights, false otherwise.
     */
    private function isUserMode()
    {
        $isAdminMode = false;
        $acronym = isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;
        if (isset($acronym)) {
            $isAdminMode = true;
        }

        return $isAdminMode;
    }

    /**
     * Helper function to create a movie information list.
     *
     * Creates a detailed list for a movie.
     *
     * @return html the detailed list for a movie.
     */
    private function createMovieInfoList()
    {
        $table = "<table class='movie-info-table'>";
        $table .= "<thead>";
        $table .= "<tr>";
        $table .= '<th>Imdb</th>';
        $table .= "<th>År</th>";
        $table .= "<th>Regissör</th>";
        $table .= "<th>Längd</th>";
        $table .= "<th>Tal</th>";
        $table .= "<th>Text</th>";
        $table .= "<th>Format</th>";
        $table .= "<th>Pris</th>";
        $table .= "<th>Youtube</th>";
        $table .= "</tr>";
        $table .= "</thead>";
        $table .= "<tbody>";

        $table .= "<tr>";
        $table .= "<td>" . $this->createLink("<img src='img.php?src=icons/imdb.png&amp;height=50&amp;sharpen' alt='Bild på imdb icon'/>", htmlentities($this->parameters['imdb'])) . "</td>";
        $table .= "<td>" . htmlentities($this->parameters['year']) . "</td>";
        $table .= "<td>" . htmlentities($this->parameters['director']) . "</td>";
        $table .= "<td>" . htmlentities($this->parameters['length']) . "</td>";
        $table .= "<td>" . htmlentities($this->parameters['speech']) . "</td>";
        $table .= "<td>" . htmlentities($this->parameters['subtext']) . "</td>";
        $table .= "<td>" . htmlentities($this->parameters['format']) . "</td>";
        $table .= "<td>" . htmlentities($this->parameters['price']) . "</td>";
        $table .= "<td>" . $this->createLink("<img src='img.php?src=icons/youtube.png&amp;height=50&amp;sharpen' alt='Bild på imdb icon'/>", htmlentities($this->parameters['youtube'])) . "</td>";

        $table .= "</tr>\n";

        $table .= "</tbody>";
        $table .= "</table>";

        return $table;
    }

    /**
     * Helper function to create a link.
     *
     * Wrappes an item with a link.
     *
     * @param  miexed $item     the item to wrap a link around
     * @param  string $link     the link.
     *
     * @return html             the link item.
     */
    private function createLink($item, $link)
    {
        return "<a target='_blank' href='{$link}'>{$item}</a>";
    }
}
