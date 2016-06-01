<?php
/**
 * Movie admin form, provides movie administration forms to be able to administrate
 * movies in the database. Supports reset database to a default value, add a
 * new film to database, edit a film in database and delete a film from
 * database.
 *
 */
class MovieAdminForm
{
    private $db;

    /**
     * Constructor
     *
     * Initiates the datbase.
     *
     * @param Database $db the database object.
     */
    public function __construct($db=null)
    {
        $this->db = $db;
    }

    /**
     * Generates the movie administration form.
     *
     * Creates a movie administration form to be able to add a new film to the
     * database and to reset the film database to the default value.
     *
     * @return html the movie administration form.
     */
    public function generateMovieAdminForm()
    {
        $form = null;
        if ($this->isAdminMode()) {
            $form .= <<<EOD
            <form class='movie-admin-form'>
                <fieldset>
                    <legend>Administrera filmer</legend>
                    <button type="button" onClick="parent.location='movie_create.php'">Lägg in ny film</button>
                    <button type="button" onClick="parent.location='movie_reset.php'">Återställ databas</button>
                </fieldset>
            </form>
EOD;
        }

        return $form;
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
     * Creates a reset database form.
     *
     * Creates a form to reset the database. The form contains a reset button
     * and a message area if reset of database was successful or not.
     *
     * @param string $title   the title in the frame of the form.
     * @param string $message the message if the reset of the database was successful
     *                        or not.
     *
     * @return html the form to reset the database or a message that you must be
     *              checked in as admin to reset the database..
     */
    public function createResetMovieDbForm($title, $message=null)
    {
        if ($this->isAdminMode()) {
            $output = <<<EOD
            <form method=post>
                <fieldset>
                    <legend>$title</legend>
                    <p>Vill du återställa filmdatabasen till dess grundvärden?<p>
                    <p>All övrig data vill bli förlorad!<p>
                    <p><input type='submit' name='reset' value='Återställ'/></p>
                    <output>{$message}</output>
                </fieldset>
            </form>
EOD;
        } else {
            $output = "<p>Du måste vara inloggad som admin för att kunna sätta databasen till dess grundvärden!</p>";
        }

        return $output;
    }

    /**
     * Creates a form to add a new film to database.
     *
     * Checks if a user is logged in as admin to create the form to be able to
     * add a new film to the database. If the user is not logged in as admin, a
     * message that only admin is allowed to add new film to database is returned.
     *
     * @param  string $title   the title of the legend for the form.
     * @param  string $message the result of the adding a new film to the database.
     * @param  [] $params      the values in the form, default null.
     *
     * @return html            the add movie to database form or a message that
     *                         you must be logged in as admin to add new films
     *                         to database.
     */
    public function createAddMovieToDbForm($title, $message, $params=null)
    {
        if ($this->isAdminMode()) {
            $output = $this->createMovieForm($title, $message);
        } else {
            $output = "<p>Du måste vara inloggad som admin för lägga till filmer i databasen!</p>";
        }

        return $output;
    }

    /**
     * Helper function to create a movie form.
     *
     * Creates a movie form with possibillity to add values to the form.
     *
     * @param  string $title   the title of the legend of the form.
     * @param  string $message the result of the action.
     * @param  [] $params      the values to be put in the form, default null.
     *
     * @return html            the movie form.
     */
    private function createMovieForm($title, $message, $params=null)
    {
        $output = <<<EOD
        <form method=post>
            <fieldset>
                <legend>{$title}</legend>
                <input type='hidden' name='id' value="{$params['id']}"/>
                <input type='hidden' name='published' value="{$params['published']}"/>
                <input type='hidden' name='rented' value="{$params['rented']}"/>
                <input type='hidden' name='rents' value="{$params['rents']}"/>
                <p><label>Titel:<br/><input type='text' name='title' value="{$params['title']}"/></label></p>
                <p><label>Regissör:<br/><input type='text' name='director' value="{$params['director']}"/></label></p>
                <p><label>Längd:<br/><input type='text' name='length' value="{$params['length']}"/></label></p>
                <p><label>År:<br/><input type='text' name='year' value="{$params['year']}"/></label></p>
                <p><label>Bildlänk:<br/><input type='text' name='image' value="{$params['image']}"/></label></p>
                <p><label>Text:<br/><input type='text' name='subtext' value="{$params['subtext']}"/></label></p>
                <p><label>Språk:<br/><input type='text' name='speech' value="{$params['speech']}"/></label></p>
                <p><label>Kvantitet:<br/><input type='text' name='quality' value="{$params['quality']}"/></label></p>
                <p><label>Format:<br/><input type='text' name='format' value="{$params['format']}"/></label></p>
                <p><label>Pris:<br/><input type='text' name='price' value="{$params['price']}"/></label></p>
                <p><label>Länk Imdb:<br/><input type='text' name='imdb' value="{$params['imdb']}"/></label></p>
                <p><label>Länk Youtube:<br/><input type='text' name='youtube' value="{$params['youtube']}"/></label></p>
                <p><label>Handling:<br/><textarea name='plot'>{$params['plot']}</textarea></label></p>
                <p><label>Genre (obligatorisk):<br/>
                    {$this->generateCheckGenresCheckBoxes($params['genre'])}
                </p>
                <p><input type='submit' name='save' value='Spara'/></p>
                <output>{$message}</output>
            </fieldset>
        </form>
EOD;

        return $output;
    }

    /**
     * Helper function to generate check boxes for film genres.
     *
     * Creates check boxes for all available film genres found in database.
     * Has function to generate check boxes, where the check box is already
     * checked, for all genres connected to the film.
     *
     * @param  string $movieGenre the string of all genres connected to the film.
     *
     * @return html               the check box.
     */
    private function generateCheckGenresCheckBoxes($movieGenre=null)
    {
        $checkBox = null;
        $genres = $this->fetchGenres();
        foreach ($genres as $key => $genre) {
            if ($this->shouldSetBoxBeSet($movieGenre, $genre)) {
                $checkBox .= "<input type='checkbox' name='genre[]' value='{$genre}' checked='checked' />{$genre} ";
            } else {
                $checkBox .= "<input type='checkbox' name='genre[]' value='{$genre}' />{$genre} ";
            }
        }

        return $checkBox;
    }

    /**
     * Helper function to get all available genres from database.
     *
     * Searches for all avaiable genres in the genre table in the database.
     *
     * @return [] All avaiable genres in the database.
     */
    private function fetchGenres()
    {
        $sql = '
            SELECT * FROM Rm_Genre;
        ';

        $res = $this->db->executeSelectQueryAndFetchAll($sql);

        $genresArray = array();
        foreach ($res as $key => $row) {
            $name = htmlentities($row->name);
            $genresArray[] = $name;
        }

        return $genresArray;
    }

    /**
     * Helper function to check if a check box should be checked or not.
     *
     * Compare all avaiable genres against the string of genres, which are releated
     * to the film, if the check box should be set or not.
     *
     * @param  string $movieGenre the string of all genres connected to the film.
     * @param  string $genre      the genre to check if it should be checked or not.
     *
     * @return boolean            true if the box should be checked, false otherwise.
     */
    private function shouldSetBoxBeSet($movieGenre, $genre)
    {
        $shouldBeSet = false;
        if (isset($movieGenre)) {
            str_replace(","," ",$movieGenre);
            if (strpos($movieGenre, $genre) !== FALSE)
            {
                return true;
            }
        }

        return $shouldBeSet;
    }

    /**
     * Creates a form to edit films.
     *
     * Creates a form to edit films in the database. If the film is not found or
     * the user is not checked in as admin, the function returns a error message.
     *
     * @param  string $title   the title of the form legend.
     * @param  [] $res         the result from the database.
     * @param  string $message the result of edit the film.
     *
     * @return html            the movie form to edit films in database or a
     *                         error message if the film was not found or the
     *                         user is not logged in as admin.
     */
    public function createEditMovieInDbForm($title, $res, $message)
    {
        if ($this->isAdminMode()) {
            $params = $this->getParameterFromFilmWithIdFromDb($res);
            if (isset($params)) {
                $output = $this->createMovieForm($title, $message, $params);
            } else {
                $output = "<p>Felaktigt id! Det finns inget film med sådant id i databasen!</p>";
            }

        } else {
            $output = "<p>Du måste vara inloggad som admin för att ändra innehållet i filmdatabasen!</p>";
        }

        return $output;
    }

    /**
     * Helper function to get all parameters from a result array from database.
     *
     * Gets all parameters from the result array from database and creates a
     * new array where the parameters have been cleaned with the function
     * htmlentities.
     *
     * @param  [] $res   the result set from the database.
     *
     * @return []        the array with cleaned parameters.
     */
    private function getParameterFromFilmWithIdFromDb($res)
    {
        $params = null;
        if (isset($res) && !empty($res)) {
            $param = $res[0];
            $params = array(
                'id' => htmlentities($param->id, null, 'UTF-8'),
                'title' => htmlentities($param->title, null, 'UTF-8'),
                'director' => htmlentities($param->director, null, 'UTF-8'),
                'length' => htmlentities($param->length, null, 'UTF-8'),
                'year' => htmlentities($param->year, null, 'UTF-8'),
                'plot' => htmlentities($param->plot, null, 'UTF-8'),
                'image' => htmlentities($param->image, null, 'UTF-8'),
                'subtext' => htmlentities($param->subtext, null, 'UTF-8'),
                'speech' => htmlentities($param->speech, null, 'UTF-8'),
                'quality' => htmlentities($param->quality, null, 'UTF-8'),
                'format' => htmlentities($param->format, null, 'UTF-8'),
                'price' => htmlentities($param->price, null, 'UTF-8'),
                'imdb' => htmlentities($param->imdb, null, 'UTF-8'),
                'youtube' => htmlentities($param->youtube, null, 'UTF-8'),
                'published' => htmlentities($param->published, null, 'UTF-8'),
                'rented' => htmlentities($param->rented, null, 'UTF-8'),
                'rents' => htmlentities($param->rents, null, 'UTF-8'),
                'genre' => htmlentities($param->genre, null, 'UTF-8')
            );
        }

        return $params;
    }

    /**
     * Creates a form to delete film form database.
     *
     * Creates form to be able to delete film from database. Only data shown
     * in film table is shown in the form. The values in the form could not
     * be changed.
     *
     * @param  string $title   the title of the form legend-
     * @param  string $message the result of deleting a film.
     * @param  [] $res         the result set with parameters from database.
     *
     * @return html            the form to delete film from datbase.
     */
    public function createDeleteMovieForm($title, $message, $res)
    {
        $params = $this->getParameterFromFilmWithIdFromDb($res);

        $output = <<<EOD
        <form method=post>
            <fieldset>
                <legend>{$title}</legend>
                <input type='hidden' name='id' value="{$params['id']}"/>
                <p><label>Titel:<br/><input type='text' name='title' value="{$params['title']}" readonly /></label></p>
                <p><label>År:<br/><input type='text' name='year' value="{$params['year']}" readonly/></label></p>
                <p><label>Pris:<br/><input type='text' name='price' value="{$params['price']}" readonly/></label></p>
                <p><label>Handling:<br/><textarea name='plot' readonly>{$params['plot']}</textarea></label></p>
                <p><label>Genre:<br/>
                    {$this->generateCheckGenresCheckBoxes($params['genre'])}
                </p>
                <p><input type='submit' name='delete' value='Ta bort'/></p>
                <output>{$message}</output>
            </fieldset>
        </form>
EOD;

        return $output;
    }

    /**
     * Creates a rent movie form.
     *
     * Creates a rent movie form for users to able to rent a movie.
     *
     * @param  [] $res the result from the database.
     * @param  boolean $result the result of the rent.
     *
     * @return html the form to rent a movie.
     */
    public function createRentMovieForm($res, $result)
    {
        $params = $this->getParameterFromFilmWithIdFromDb($res);

        $output = null;
        if (isset($params)) {
            $message = $this->createMessage($result);
            $output = $this->createRentForm($params, $message);
        }

        return $output;
    }

    /**
     * Helper function to create a message of result to rent a Movie.
     *
     * Creates a message depending if the rent of a movie was successful or not.
     *
     * @param  boolean $result the result of renting a movie.
     *
     * @return string a message if the rent was successful or not.
     */
    private function createMessage($result)
    {
        $message = null;
        if (isset($result)) {
            if ($result) {
                $message = "Tack för du använder Rental Movies, filmen är nu tillgänglig att se.";
            } else {
                $message = "Det uppstod ett problem när du försökte hyra filmen. Var vänlig försök igen!";
            }
        }


        return $message;
    }

    /**
     * Helper function to create a rent a movie form.
     *
     * Creates a form to be able to rent a movie.
     *
     * @param  [] $params  contains number of rents and the id of the movie.
     * @param  string $message the result of the rent.
     *
     * @return html the rent a movie form.
     */ 
    private function createRentForm($params, $message)
    {
        $rents = $params['rents'] + 1;
        $output = <<<EOD
        <form class='rent-button' action='rent-process.php' method=post>
            <input type='hidden' name='id' value="{$params['id']}"/>
            <input type='hidden' name='rents' value="{$rents}"/>
            <input type='submit' name='save' value='Hyr'/>
            <output>{$message}</output>
        </form>
EOD;

        return $output;
    }
}
