<?php
/**
 * Blog admin form, provides news blogs administration forms to be able to administrate
 * news blogs in the database. Supports reset database to a default value, add a
 * new news blog to database, edit a news blog in database and delete a news blog
 * from database.
 *
 */
class BlogAdminForm
{
    private $db;

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
    public function generateNewsBlogsAdminForm()
    {
        $form = null;
        if ($this->isUserMode()) {

            $resetDbButton = null;
            if ($this->isAdminMode()) {
                $resetDbButton =<<<EOD
                    <button type="button" onClick="parent.location='content_reset.php'">Återställ databas</button>
EOD;
            }

            $form .= <<<EOD
            <form class='news-blogs-admin-form'>
                <fieldset>
                    <legend>Administrera nyheter</legend>
                    <button type="button" onClick="parent.location='content_create.php'">Lägg in ny nyhet</button>
                    {$resetDbButton}
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
     * Helper function to check if the status is user mode.
     *
     * Checks if the user has checked in as user.
     *
     * @return boolean true if as user is checked in as user, false otherwise.
     */
    private function isUserMode($user=null)
    {
        $isUserMode = false;
        $acronym = isset($_SESSION['user']) ? $_SESSION['user']->acronym : null;
        if (isset($acronym)) {
            if (isset($user)) {
                if ((strcmp ($acronym , 'admin') === 0) || (strcmp ($acronym , $user) === 0)) {
                    $isUserMode = true;
                }
            } else {
                $isUserMode = true;
            }
        }

        return $isUserMode;
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
     *              checked in as admin to reset the database.
     */
    public function createResetNewsBlogsDbForm($title, $message=null)
    {
        if ($this->isAdminMode()) {
            $output = <<<EOD
            <form method=post>
                <fieldset>
                    <legend>$title</legend>
                    <p>Vill du återställa nyhetsdatabasen till dess grundvärden?<p>
                    <p>All övrig data vill bli förlorad!<p>
                    <p><input type='submit' name='reset' value='Återställ'/></p>
                    <output>Meddelande: {$message}</output>
                </fieldset>
            </form>
EOD;
        } else {
            $output = "<p>Du måste vara inloggad som admin för att kunna sätta databasen till dess grundvärden!</p>";
        }

        return $output;
    }

    public function createNewsBlogToDbForm($title, $message, $params=null)
    {
        if ($this->isUserMode()) {
            $output = $this->createNewsBlogForm($title, $message);
        } else {
            $output = "<p>Du måste vara inloggad för lägga till nyheter i databasen!</p>";
        }

        return $output;
    }

    private function createNewsBlogForm($title, $message, $params=null)
    {
        $output = <<<EOD
        <form method=post>
            <fieldset>
                <legend>{$title}</legend>
                <input type='hidden' name='id' value="{$params['id']}"/>
                <input type='hidden' name='type' value='post'/>
                <input type='hidden' name='author' value="{$params['author']}"/>
                <input type='hidden' name='deleted' value="{$params['deleted']}"/>
                <p><label>Titel:<br/><input type='text' name='title' value="{$params['title']}"/></label></p>
                <p><label>Text:<br/><textarea name='data'>{$params['data']}</textarea></label></p>
                <p><label>Kategori:<br/>
                    {$this->generateCategoryRadioButtons($params['category'])}
                </p>
                <p><label>Filter:<br/>
                    {$this->generateFilterTypeCheckBoxes($params['filter'])}
                </p>
                <p><label>Publiceringsdatum<br/>(åååå-mm-dd):<br/>
                    <input type='text' name='published' value="{$params['published']}"/></label></p>
                <p><input type='submit' name='save' value='Spara'/></p>
                <output>Meddelande: {$message}</output>
            </fieldset>
        </form>
EOD;
        return $output;
    }

    /**
     * Helper function to generate radio buttons for news blogs categories.
     *
     * Creates radio buttons for all available news blogs categories found in database.
     * Has function to generate radio buttons, where the radio button is already
     * set, for all categories connected to the news blog.
     *
     * @param  string $category the the category of the news blog.
     *
     * @return html             the radio button.
     */
    private function generateCategoryRadioButtons($newsBlogCategory=null)
    {
        $radioButtons = null;
        $categories = $this->fetchAllCategories();
        if (!isset($newsBlogCategory)) {
            $newsBlogCategory = end($categories);
        }

        foreach ($categories as $key => $category) {
            if (strcmp($category, $newsBlogCategory) === 0) {
                $radioButtons .= "<input type='radio' name='category' value='{$category}' checked='checked' />{$category} ";
            } else {
                $radioButtons .= "<input type='radio' name='category' value='{$category}' />{$category} ";
            }
        }

        return $radioButtons;
    }

    private function fetchAllCategories()
    {
        $sql = '
            SELECT * FROM Rm_Category;
        ';

        $res = $this->db->executeSelectQueryAndFetchAll($sql);

        $categoriesArray = array();
        foreach ($res as $key => $row) {
            $name = htmlentities($row->name);
            $categoriesArray[] = $name;
        }

        return $categoriesArray;
    }

    /**
     * Helper function to generate check boxes for filter types.
     *
     * Creates check boxes for all available filter types found in database.
     * Has function to generate check boxes, where the check box is already
     * checked, for all filter types connected to the news blog.
     *
     * @param  string $newsBlogFilterType the string of all filter types connected
     *                                    to the news blog.
     *
     * @return html                       the check box.
     */
    private function generateFilterTypeCheckBoxes($newsBlogFilterType=null)
    {
        $checkBox = null;
        $filterTypes = $this->fetchFilterTypes();
        foreach ($filterTypes as $key => $filterType) {
            if ($this->shouldSetBoxBeSet($newsBlogFilterType, $filterType)) {
                $checkBox .= "<input type='checkbox' name='filter[]' value='{$filterType}' checked='checked' />{$filterType} ";
            } else {
                $checkBox .= "<input type='checkbox' name='filter[]' value='{$filterType}' />{$filterType} ";
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
    private function fetchFilterTypes()
    {
        $sql = '
            SELECT * FROM Rm_Filters;
        ';

        $res = $this->db->executeSelectQueryAndFetchAll($sql);

        $filterTypesArray = array();
        foreach ($res as $key => $row) {
            $name = htmlentities($row->name);
            $filterTypesArray[] = $name;
        }

        return $filterTypesArray;
    }

    /**
     * Helper function to check if a check box should be checked or not.
     *
     * Compare all avaiable genres against the string of genres, which are releated
     * to the film, if the check box should be set or not.
     *
     * @param  string $filterTypes  the string of all filter types connected to the news blog.
     * @param  string $filterType   the filter type to check if it should be checked or not.
     *
     * @return boolean            true if the box should be checked, false otherwise.
     */
    private function shouldSetBoxBeSet($filterTypes, $filterType)
    {
        $shouldBeSet = false;
        if (isset($filterTypes)) {
            str_replace(","," ",$filterTypes);
            if (strpos($filterTypes, $filterType) !== FALSE)
            {
                return true;
            }
        }

        return $shouldBeSet;
    }

    public function createEditNewsBlogInDbForm($title, $res, $message=null)
    {
        $params = $this->getParameterFromNewsBlogWithIdFromDb($res);
        if (isset($params)) {
            if ($this->isUserMode($params['author'])) {
                $output = $this->createNewsBlogForm($title, $message, $params);
            } else {
                $output = "<p>Du måste vara inloggad som admin eller skapat nyheten för att ändra innehållet!</p>";
            }
        } else {
            $output = "<p>Felaktigt id! Det finns inget nyhet med sådant id i databasen!</p>";
        }

        return $output;
    }

    private function getParameterFromNewsBlogWithIdFromDb($res)
    {
        $params = null;
        if (isset($res) && !empty($res)) {
            $params = array(
                'id' => htmlentities($res->id, null, 'UTF-8'),
                'slug' => htmlentities($res->slug, null, 'UTF-8'),
                'url' => htmlentities($res->url, null, 'UTF-8'),
                'type' => htmlentities($res->type, null, 'UTF-8'),
                'title' => htmlentities($res->title, null, 'UTF-8'),
                'data' => htmlentities($res->data, null, 'UTF-8'),
                'filter' => htmlentities($res->filter, null, 'UTF-8'),
                'author' => htmlentities($res->author, null, 'UTF-8'),
                'category' => htmlentities($res->category, null, 'UTF-8'),
                'published' => htmlentities($res->published, null, 'UTF-8'),
                'deleted' => htmlentities($res->deleted, null, 'UTF-8')
            );
        }

        return $params;
    }

    public function createDeleteNewsBlogInDbForm($title, $res, $message)
    {
        $params = $this->getParameterFromNewsBlogWithIdFromDb($res);
        if (isset($params)) {
            if ($this->isUserMode($params['author'])) {
                $output = $this->createDeleteNewsBlogForm($title, $message, $params);
            } else {
                $output = "<p>Du måste vara inloggad som admin eller skapat nyheten för att ta bort nyheten!</p>";
            }
        } else {
            $output = "<p>Felaktigt id! Det finns inget nyhet med sådant id i databasen!</p>";
        }

        return $output;
    }

    private function createDeleteNewsBlogForm($title, $message, $params=null)
    {
        $output = <<<EOD
        <form method=post>
            <fieldset>
                <legend>{$title}</legend>
                <input type='hidden' name='id' value="{$params['id']}"/>
                <input type='hidden' name='type' value='post'/>
                <input type='hidden' name='author' value="{$params['author']}"/>
                <input type='hidden' name='filter' value="{$params['filter']}"/>
                <p><label>Titel:<br/><input type='text' name='title' value="{$params['title']}" readonly/></label></p>
                <p><label>Text:<br/><textarea name='data' readonly>{$params['data']}</textarea></label></p>
                <p><label>Publiseringsdatum:<br/><input type='Publiseringsdatum' name='published' value="{$params['published']}" readonly/></label></p>
                <p><input type='submit' name='delete' value='Ta bort'/> <input type='submit' name='erase' value='Radera'/></p>
                <p>Ta bort: nyheten finns kvar i databasen och kan återskapas. Radera: nyheten tas bort permanent från databas.</p>
                <output>Meddelande: {$message}</output>
            </fieldset>
        </form>
EOD;
        return $output;
    }
}
