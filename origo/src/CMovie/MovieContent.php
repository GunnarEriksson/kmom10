<?php
/**
 * Movie Content, handles the content of movies in the database.
 *
 */
class MovieContent
{
    private $db;
    private $lastInsertedId;
    private $genreValues;

    /**
     * Constructor
     *
     * Initates the database, last inserted id and all available genres values.
     *
     * @param Database $db the database object.
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->lastInsertedId = null;
        $this->genreValues = $this->createGenreConversionArray();
    }

    /**
     * Helper function to create a genres conversion array.
     *
     * Gets all available genres from database and creates a conversion table
     * where the name is connected to the genres table id.
     *
     * @return [] the associative array where the genre is connected with the
     *            table id.
     */
    private function createGenreConversionArray()
    {
        $sql = '
            SELECT * FROM Rm_Genre;
        ';

        $res = $this->db->executeSelectQueryAndFetchAll($sql);

        $genreToNumMappingArray = array();
        foreach ($res as $key => $row) {
            $name = htmlentities($row->name);
            $id = htmlentities($row->id);
            $genreToNumMappingArray[$name] = $id;
        }

        return $genreToNumMappingArray;
    }

    /**
     * Resets the content.
     *
     * Sends two requests to the data base to reset the database to the
     * default values.
     *
     * @return string text if the reset of the db was successful or not.
     */
    public function resetContent()
    {
        if ($this->isAdminMode()) {
            $message = "Kunde EJ återställa filmdatabasen till dess grundvärden";
            $this->dropContentTablesIfExists();

            $res = $this->createMovieTable();
            if ($res) {
                $res = $this->setMovieDefaultValues();
                if ($res) {
                    $res = $this->createNovieGeneresTable();
                    if ($res) {
                        $res = $this->setMovieGeneresDefaultValues();
                        if ($res) {
                            $res = $this->createMovieToGenreTable();
                            if ($res) {
                                $res = $this->setMovieToGenreDefaultValues();
                                if ($res) {
                                    $message = "Filmdatabas återställd till dess grundvärden";
                                }
                            }
                        }
                    }
                }
            }
        } else {
            $message = "Du måste vara inloggad som admin för att kunna sätta databasen till dess grundvärden!";
        }

        return $message;
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
     * Helper function to drop the content table if exists.
     *
     * Sends a query to delete the content table if it exists.
     *
     * @return boolean true if the content table was deleted, false otherwise, which
     *                 could be that the content table is not existing.
     */
    private function dropContentTablesIfExists()
    {
        $sql = 'DROP TABLE IF EXISTS Rm_Movie2Genre;';
        $this->db->executeQuery($sql);

        $sql = 'DROP TABLE IF EXISTS Rm_Genre;';
        $this->db->executeQuery($sql);

        $sql = 'DELETE FROM Rm_Movie;';
        $this->db->executeQuery($sql);

        $sql = 'DROP TABLE IF EXISTS Rm_Movie;';
        $this->db->executeQuery($sql);
    }

    /**
     * Helper function to create a movie table in a database.
     *
     * Creates a movie table in the database and returns the result of
     * the creation of the table.
     *
     * @return boolean true if the creation of table was successful, false otherwise.
     */
    private function createMovieTable()
    {
        $sql = '
            CREATE TABLE Rm_Movie
            (
                id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
                title VARCHAR(100) NOT NULL,
                director VARCHAR(100),
                length INT DEFAULT NULL, -- Length in minutes
                year INT NOT NULL DEFAULT 1900,
                plot TEXT, -- Short intro to the movie
                image VARCHAR(100) DEFAULT NULL, -- Link to an image
                subtext CHAR(3) DEFAULT NULL, -- swe, fin, en, etc
                speech CHAR(3) DEFAULT NULL, -- swe, fin, en, etc
                quality CHAR(3) DEFAULT NULL,
                format CHAR(4) DEFAULT NULL, -- mp4, divx, etc
                price INT DEFAULT NULL,
                imdb VARCHAR(100) DEFAULT NULL,
                youtube VARCHAR(100) DEFAULT NULL,
                published DATETIME,
                rented DATETIME,
                rents INT DEFAULT NULL
            ) ENGINE INNODB CHARACTER SET utf8;
        ';

        return $this->db->executeQuery($sql);
    }

    /**
     * Helper function to add default values in the movie table.
     *
     * Adds default values to a movie table and returns the result of adding
     * default values to the table.
     *
     * @return boolean true if the adding of default values was successful, false otherwise.
     */
    private function setMovieDefaultValues()
    {
        $sql = <<<EOD
            INSERT INTO Rm_Movie (title, director, length, year, plot, image, subtext, speech, format, price, imdb, youtube, published, rented, rents) VALUES
            ('Our kind of traitor', 'Susanna White', 107, 2015, 'Ett ungt engelskt par möter en gåtfull rysk affärsman under semestern. Vad de inte vet är att han är en penningtvättare, som försöker hoppa av till den brittiska underrättelsetjänsten innan hans fiender finner och dödar honom ? och han har valt dem som sin livlina. Helt plötsligt är paret indragna i en dödlig jakt, som tar dem från Marrakech till London och Paris, och slutligen till de schweiziska alperna.', 'movie/our_kind_of_traitor.jpg', 'sve', 'eng', 'DivX', 39, 'http://www.imdb.com/title/tt1995390/', 'https://www.youtube.com/watch?v=N5k4FBGtbMs', '2016-05-20 12:35:29', '2016-05-23 11:35:29', 15),
                ('Now You See Me 2', 'Jon M. Chu', 115, 2016, 'De fyra magikerkompanjonerna (Jesse Eisenberg, Woody Harrelson, Dave Franco, Lizzy Caplan) återvänder för ett andra sinnesförvrängande äventyr, där de flyttar gränserna för illusionistkonst på scen till nya höjder runt om i världen. Ett år efter att de lurat FBI och vann allmänhetens kärlek med sina Robin Hood-aktiga konster, dyker illusionisterna upp igen för ett comeback-uppträdande i syfte att avslöja oetiska metoder som en tech-magnat ägnar sig åt.', 'movie/now_you_see_me2.jpg', 'sve', 'eng', 'DivX', 35, ': http://www.imdb.com/title/tt3110958/', 'https://www.youtube.com/watch?v=4I8rVcSQbic', '2016-05-15 16:25:21', '2016-05-23 14:35:29', 31),
                ('Neon Demon', 'Nicolas Winding Refn', 110, 2016, 'Jesse är en ung lovande modell som flyttar till Los Angeles i sitt sökande efter framgång. Där hamnar hon snart i klorna på en grupp kvinnor, vars besatthet av skönhet gör dem beredda att ta till alla medel för att komma åt hennes ungdom och vitalitet.', 'movie/neon_demon.jpg', 'sve', 'eng', 'DivX', 49, 'http://www.imdb.com/title/tt1974419/', 'https://www.youtube.com/watch?v=cipOTUO0CmU', '2016-05-10 14:35:29', '2016-05-22 22:15:49', 8),
                ('Bastille Day', 'James Watkins', 92, 2016, 'Michael Mason, en amerikansk ficktjuv bosatt i Paris, själ en väska som visar sig innehålla mer än bara en plånbok. Plötsligt har CIA tagit upp jakten på honom, men CIA-agenten Sean Briar inser snart att Michael bara är en bricka i ett mycket större spel och deras bästa tillgång för att avslöja en storskalig konspiration. Mot sina befäls order, rekryterar Briar Michael för att hjälpa till att spåra källan till korruption. Under ett gastkramande dygn upptäcker de att de båda två är måltavlor som måste våga lita på varandra för att kunna förgöra fienden.', 'movie/bastille_day.jpg', 'sve', 'eng', 'DivX', 39, 'http://www.imdb.com/title/tt2368619/', 'https://www.youtube.com/watch?v=U5R0bI8EJCQ', '2016-04-22 10:32:19', '2016-05-21 11:30:21', 78),
                ('Djungelboken', 'Jon Faveau', 105, 2016, 'En föräldralös pojke växer upp i djungeln och uppfostras av vargar, björnar och en svart panter. Efter den klassiska sagan skriven av Rudyard Kipling.', 'movie/djungelboken.jpg', 'sve', 'sve', 'DivX', 39, 'http://www.imdb.com/title/tt3040964/', 'https://www.youtube.com/watch?v=2eJImFQzti0', '2016-04-13 16:33:29', '2016-05-22 14:35:29', 56),
                ('Dottern', 'Simon Stone', 96, 2015, 'Christian återvänder hem för första gången på femton år för att hans far ska gifta sig. Väl hemma återförenas han också med sin gamla vän Oliver och spenderar en del tid med hans familj. Christians besök blir dock inte bara en kär återförening, det drar också fram flera gamla familjehemligheter i ljuset.', 'movie/dottern.jpg', 'sve', 'eng', 'DivX', 39, 'http://www.imdb.com/title/tt3922816/', 'https://www.youtube.com/watch?v=pSse2RIapEA', '2016-05-23 10:55:29', '2016-05-23 14:35:29', 5),
                ('Eye in the sky', 'Gavin Hood', 102, 2015, 'Överste Katherine Powell leder ett hemligt drönaruppdrag, från högkvarteret i England, med mål att fånga en ökänd terroristgrupp som gömmer sig i Nairobi, Kenya. När hon och hennes team upptäcker att terroristerna planerar att utföra en självmordsattack, ändras ordern snabbt från "tillfångata" till "döda". Precis när man ska inleda den dödliga attacken mot terroristernas näste upptäcker piloten en lekande nioårig flicka precis vid huset, och man ställs inför ett svårt val.', 'movie/eye_in_the_sky.jpg', 'sve', 'eng', 'DivX', 42, 'http://www.imdb.com/title/tt2057392/', 'https://www.youtube.com/watch?v=PxpX8-efsZI', '2016-02-16 19:15:39', '2016-05-20 12:35:29', 89),
                ('Flickan mamman och demonerna', 'Suzanne Osten', 90, 2016, 'I en lägenhet låser Siri, en ensamstående och psykotisk mamma, in sig själv tillsammans med sin dotter. Här är det nämligen demonerna som styr. Ti kan höra mamman när hon pratar med demonerna, hon ser mammans förändrade och slutna ansikte. Men demonerna som mamman talar med, kan Ti vare sig höra eller se. Situationen blir farlig när demonerna tar över hela Siris värld. Livsfarlig, faktiskt. Siri är inte Siri längre. Det är som att hon själv har förvandlats till en demon. Så för att överleva tar Ti till sin fantasi och beslutar sig för att besegra mammans demoner. ', 'movie/flickan_mamman_och_demonerna.jpg', 'sve', 'sve', 'DivX', 44, 'http://www.imdb.com/title/tt4841464/', 'https://www.youtube.com/watch?v=RzCpF4VSQBo', '2016-04-15 11:34:49', '2016-05-22 11:30:29', 39),
                ('Hermelinen', 'Christian Vincent', 98, 2016, 'Michael är en ökänt hård domare som sällan dömer någon till lägre straff än 10 år. En dag förändras allt när han återser Ditte som kommer in på jurytjänst. Sex år tidigare träffade Michel Ditte när han låg på sjukhuset och hon var narkosläkare. Michael förälskade sig i Ditte och hon var kanske den enda kvinna han någon förälskat sig i.', 'movie/hermelinen.jpg', 'sve', 'fre', 'DivX', 44, 'http://www.imdb.com/title/tt4216908/', 'https://www.youtube.com/watch?v=QRxyQjDnuxs', '2016-04-29 13:43:39', '2016-05-21 19:35:42', 49),
                ('Mothers day', 'Garry Marshall', 118, 2016, 'Det är bara några dagar kvar till Mors dag och Sandy, Miranda, Jesse och Bradley planerar på skilda håll hur de ska tillbringa dagen: med en ny kärlek, en förlorad kärlek - eller ingen kärlek alls. Oavsett vilket kommer detta bli en Mors Dag att minnas...', 'movie/mothers_day.jpg', 'sve', 'eng', 'DivX', 49, 'http://www.imdb.com/title/tt4824302/', 'https://www.youtube.com/watch?v=2BPr217zLps', '2016-05-06 19:32:28', '2016-05-22 16:35:11', 54),
                ('Paddington', 'Paul King', 118, 2014, 'Paddington har vuxit upp djupt i den peruanska djungeln med sin moster Lucy som, inspirerad av ett möte med en engelsk upptäcktsresande, har fått sin brorson att drömma om ett spännande liv i London. När en jordbävning förstör deras hem bestämmer sig Lucy för att smuggla sin unge brorson ombord på en båt på väg till England, på jakt efter ett bättre liv.', 'movie/paddington.jpg', 'sve', 'sve', 'DivX', 39, 'http://www.imdb.com/title/tt1109624/', 'https://www.youtube.com/watch?v=CxeBdrGGU8U', '2015-01-16 12:22:34', '2016-03-15 16:01:20', 65),
                ('The Intern', 'Nancy Mayers', 121, 2015, 'Den 70-åriga änklingen Ben har tröttnat på livet som pensionär och ger sig in i leken igen genom en praktikplats på en nätbutik för mode som drivs av karriäristan Jules.  Oscar-belönade Robert De Niro och Anne Hathaway har huvudrollerna i höstens stora romantiska komedi The Intern i regi av Oscar-nominerade Nancy Meyers.', 'movie/the_intern.jpg', 'sve', 'eng', 'DivX', 42, 'http://www.imdb.com/title/tt2361509/', 'https://www.youtube.com/watch?v=W-DEy3mylCs', '2015-10-09 11:31:36', '2016-05-18 19:35:29', 112)
            ;
EOD;
        return $this->db->executeQuery($sql);
    }

    /**
     * Helper function to generate a genres table.
     *
     * Creates a genres table and returns the result of the creation of the table.
     *
     * @return boolean true if the creation of the table was successful, false otherwise.
     */
    private function createNovieGeneresTable()
    {
        $sql = '
            CREATE TABLE Rm_Genre
            (
                id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
                name CHAR(20) NOT NULL -- crime, svenskt, college, drama, etc
            ) ENGINE INNODB CHARACTER SET utf8;
        ';

        return $this->db->executeQuery($sql);
    }

    /**
     * Helper function to set the default values in the genres table.
     *
     * Sets the parameters in the genres table to their default values and
     * returns the result.
     *
     * @return boolean true if the setting of the parameters in the genres table
     *                 was successful, false otherwise.
     */
    private function setMovieGeneresDefaultValues()
    {
        $sql = <<<EOD
            INSERT INTO Rm_Genre (name) VALUES
            ('comedy'), ('romance'), ('college'),
            ('crime'), ('drama'), ('thriller'),
            ('animation'), ('adventure'), ('family'),
            ('svenskt'), ('action'), ('horror')
        ;
EOD;
        return $this->db->executeQuery($sql);
    }

    /**
     * Helper function to create a movie to genre table.
     *
     * Creates a movie to genre table where movies can be connected to genres and
     * returns the result of the creation.
     *
     * @return boolean  true if a table was created, false otherwise.
     */
    private function createMovieToGenreTable()
    {
        $sql = '
            CREATE TABLE Rm_Movie2Genre
            (
                idMovie INT NOT NULL,
                idGenre INT NOT NULL,

                FOREIGN KEY (idMovie) REFERENCES Rm_Movie (id),
                FOREIGN KEY (idGenre) REFERENCES Rm_Genre (id),

                PRIMARY KEY (idMovie, idGenre)
            ) ENGINE INNODB;
        ';

        return $this->db->executeQuery($sql);
    }

    /**
     * Helper function to set the default values in the movie to genre table.
     *
     * Sets the parameters in the movie to genre table to their default values
     * and returns the result.
     *
     * @return boolean true if the movie to genre table is set to its default
     *                 values, false otherwise.
     */
    private function setMovieToGenreDefaultValues()
    {
        $sql =<<<EOD
            INSERT INTO Rm_Movie2Genre (idMovie, idGenre) VALUES
            (1, 6),
            (2, 1),
            (2, 6),
            (2, 11),
            (3, 6),
            (3, 12),
            (4, 5),
            (4, 11),
            (5, 5),
            (5, 9),
            (6, 5),
            (7, 5),
            (7, 6),
            (8, 5),
            (8, 6),
            (8, 10),
            (9, 1),
            (9, 4),
            (9, 5),
            (10, 1),
            (10, 5),
            (11, 1),
            (11, 7),
            (11, 9),
            (12, 1)
        ;
EOD;
        return $this->db->executeQuery($sql);
    }

    /**
     * Adds a film to the database.
     *
     * Adds a film to the database and returns a message if the film was added
     * or not.
     *
     * @param [] $params the details of the film.
     * @param [] $genres the genres connected to the movie.
     *
     * @return string the messeage if the film was added or not.
     */
    public function addNewFilmToDb($params, $genres)
    {
        $sql = '
            INSERT INTO Rm_Movie (title, director, length, year, plot, image, subtext, speech, quality, format, price, imdb, youtube, published, rented)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NULL);
        ';

        $res = $this->db->ExecuteQuery($sql, $params);

        if ($res) {
            $message = $this->connectGenresToMovie($genres);

        } else {
            $message = 'Informationen sparades EJ.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre>';
        }

        return $message;
    }

    /**
     * Helper function to connect genres to a movie.
     *
     * Connects genres to a movie and returns the result of the operation.
     *
     * @param  [] $genres       the genres that should be connected to a movie.
     * @param  integer $id      the id of the movie.
     *
     * @return string           the result of the operation if genres could be
     *                          connected to a move or not.
     */
    private function connectGenresToMovie($genres, $id=null)
    {
        if (isset($id)) {
            $this->lastInsertedId = $id;
        } else {
            $this->lastInsertedId = $this->db->lastInsertId();
        }

        if (isset($this->lastInsertedId)) {
            foreach ($genres as $key => $genre) {
                $genreValue = $this->getGenreValue($genre);
                $res = $this->addGenreToMovie($this->lastInsertedId, $genreValue);
            }

            if ($res) {
                $message = 'Informationen sparades.';
            } else {
                $message = 'Informationen sparades EJ, kunde binda genre till filmen';
            }

        } else {
            $message = 'Informationen sparades EJ, kunde inte hämta film id från databas.';
        }

        return $message;
    }

    /**
     * Helper function to get the value of the genre.
     *
     * Gets the value of the genre. The value is the genres id in the genre table.
     *
     * @param  [] $genre    the array of genres with associated id.
     *
     * @return integer      the id of the genre.
     */
    private function getGenreValue($genre)
    {
        return $this->genreValues[$genre];
    }

    /**
     * Helper function to add a genre to a movie.
     *
     * Connects a movie to a genre in the movie to genre table and returns the
     * result of the operation.
     *
     * @param integer $id       the id of the movie.
     * @param integer $genre    the id of the genre.
     *
     * @return boolean          true if the connection of genre to a move was
     *                          was successful, false otherwise.
     */
    private function addGenreToMovie($id, $genre)
    {
        $sql = '
            INSERT INTO Rm_Movie2Genre (idMovie, idGenre)
                VALUES (?, ?);
        ';

        $params = array($id, $genre);
        $res = $this->db->ExecuteQuery($sql, $params);

        return $res;
    }

    /**
     * Edits a film in the database.
     *
     * Edits a film in the database and returns a message if the operation was
     * successful or not.
     *
     * @param  [] $params   the details of the movie.
     * @param  [] $genres   the genres that should be connected to the movie.
     *
     * @return string       the result of the operation was successful or not.
     */
    public function editFilmInDb($params, $genres)
    {
        $sql = '
            UPDATE Rm_Movie SET
                title       = ?,
                director    = ?,
                length      = ?,
                year        = ?,
                plot        = ?,
                image       = ?,
                subtext     = ?,
                speech      = ?,
                quality     = ?,
                format      = ?,
                price       = ?,
                imdb        = ?,
                youtube     = ?,
                published   = ?,
                rented      = ?,
                rents       = ?
            WHERE
                id = ?
        ';

        $res = $this->db->ExecuteQuery($sql, $params);

        if ($res) {
            $output = $this->editGenres($params[13], $genres);
        } else {
            $output = 'Informationen om filmen uppdaterades EJ.<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre>';
        }

        return $output;
    }

    /**
     * Helper function to edit a movies genres.
     *
     * Compare the existing genres for a movie with the new ones. Redundant genres
     * are removed and new ones are added. New genres for a move that exists in
     * the database, are not updated. Existing genres are fetched from the
     * database.
     *
     * @param  integer $id  the id of the movie.
     * @param  [] $genres   new and existing genres for a movie.
     *
     * @return string       the message if the edit of the genres was successful
     *                      or not.
     */
    private function editGenres($id, $genres)
    {
        $message = 'Informationen om filmen har uppdaterats.';
        $newGenres = $this->convertGenresToValues($genres);
        $oldMovieGenres = $this->getGenresForMovie($id);

        $removeGenres = array_diff($oldMovieGenres, $newGenres);
        $res = $this->removeGenresForMovie($id, $removeGenres);
        if (!$res) {
            $message = 'Gamla genres för filmen kunde inte tas bort!';
        }

        $addGenres = array_diff($newGenres, $oldMovieGenres);
        $res = $this->addGenresToMovie($id, $addGenres);
        if (!$res) {
            $message = 'Nya genres för filmen kunde inte läggas till!';
        }

        return $message;

    }

    /**
     * Helper functin to convert an array of genres to an array of genres values.
     *
     * Converts an array of genres to an array of values based on the genres id.
     *
     * @param  [] $genres   the array of genre names.
     *
     * @return []           the array of genre ids.
     */
    private function convertGenresToValues($genres)
    {
        $genresAsValue = array();
        foreach ($genres as $key => $genre) {
            $genresAsValue[] = $this->getGenreValue($genre);
        }

        return $genresAsValue;
    }

    /**
     * Helper function to get genres for a movie.
     *
     * Fetch genres for a movie from the database.
     *
     * @param  integer $id  the id of the movie.
     *
     * @return []           array of genres connected to the movie.
     */
    private function getGenresForMovie($id)
    {
        $sql = '
            SELECT idGenre FROM Rm_Movie2Genre WHERE idMovie = ?;
        ';

        $params = array($id);

        $res = $this->db->ExecuteSelectQueryAndFetchAll($sql, $params);
        $genres = array();
        if (isset($res) && !empty($res)) {
            foreach ($res as $key => $row) {
                $genres[] = $row->idGenre;
            }
        }

        return $genres;
    }

    /**
     * Helper function to remove genres connected to a movie.
     *
     * Removes the connection for genres in the movie to genre table for a movie.
     *
     * @param  integer $id     the id of the movie.
     * @param  [] $genres   the genres for a movie that should be removed.
     *
     * @return boolean      true if all genres was removed for a movie, false otherwise.
     */
    private function removeGenresForMovie($id, $genres)
    {
        $isAllGenresRemoved = true;
        foreach ($genres as $key => $genre) {
            $res = $this->removeGenreForMovie($id, $genre);
            if (!$res) {
                $isAllGenresRemoved = false;
            }
        }

        return $isAllGenresRemoved;
    }

    /**
     * Helper function to remove one genre connected to movie.
     *
     * Removes one genre connected to a movie and returns the result.
     *
     * @param  integer $id    the id of the movie.
     * @param  integer $genre   the id of the genre to be removed.
     *
     * @return boolean          true if the connection to the genre was remvoed,
     *                          false otherwise.
     */
    private function removeGenreForMovie($id, $genre)
    {
        $sql = '
            DELETE FROM Rm_Movie2Genre WHERE idMovie = ? AND idGenre = ?;
        ';

        $params = array($id, $genre);

        $res = $this->db->ExecuteQuery($sql, $params);

        return $res;
    }

    /**
     * Helper function to add genres to a movie.
     *
     * Adds genres to a movie and returns the result.
     *
     * @param integer $id   the id of the movie.
     * @param [] $genres    the genres that should be added to a movie.
     *
     * @return boolean      true if the genres was added to the movie, false otherwise.
     */
    private function addGenresToMovie($id, $genres)
    {
        $isAllGenresAdded = true;
        foreach ($genres as $key => $genre) {
            $res = $this->addGenreToMovie($id, $genre);
            if (!$res) {
                $isAllGenresAdded = false;
            }
        }

        return $isAllGenresAdded;
    }

    /**
     * Removes a movie in the database.
     *
     * Based on the id, the movie and the related genres are removed from the
     * database. Returns a message of the result of the operation.
     *
     * @param  [] $params   the array containing the id of the movie.
     * @param  [] $genres   the genres connected to the movie.
     *
     *
     * @return string       the result if the movie and genres could be removed
     *                      from the database or not.
     */
    public function removeFilmInDb($params, $genres)
    {
        $genres = $this->convertGenresToValues($genres);
        $res = $this->removeGenresForMovie($params[0], $genres);

        if ($res) {
            $sql = '
                DELETE FROM Rm_Movie WHERE id = ?;
            ';

            $res = $this->db->ExecuteQuery($sql, $params);

            if ($res) {
                $output = 'Filmen är bortagen';
            } else {
                $output = 'Filmen kunde EJ tas bort!';
            }
        } else {
            $output = 'Genres kunde EJ tas bort!';
        }

        return $output;
    }

}
