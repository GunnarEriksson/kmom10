<?php
/**
 * User admin form, provides user administration forms to be able to administrate
 * users in the database. Add a new user to database, edit a user in database and
 * delete a user from database.
 */
class UserAdminForm
{

    /**
     * Creates add user to db form.
     *
     * Creates a form to add new users to the database. Parameters that are not
     * set are set to null.
     *
     * @param  string $title    the title of the form.
     * @param  string $message  the message to a user.
     * @param  [] $params       parameters for administration of users.
     *
     * @return html the form to add new users to the database.
     */
    public function createAddUserToDbFrom($title, $message, $params=null)
    {
        if (isset($params)) {
            $default = $this->createDefaultFormParameters();
            $params = array_merge($default, $params);
        }

        return $this->createUserForm($title, $message, $params);
    }

    /**
     * Helper function to create default parameters.
     *
     * Creates default parameters, which values are set to null.
     *
     * @return [] the array of default parameters.
     */
    private function createDefaultFormParameters()
    {
        $default = array(
            'id' => null,
            'published' => null,
            'acronym' => null,
            'name' => null,
            'info' => null,
            'email' => null,
            'password' => null
        );

        return $default;
    }

    /**
     * Helper function to create a user form.
     *
     * Creates a user form to add and update users. Protection is added to prevent
     * acronym and name of the administrator to be changed.
     *
     * @param  string $title            the title of the form.
     * @param  string $message          the message to a user.
     * @param  [] $params               values for the fields in the form.
     * @param  string $passwordMessage  notice to only add password if the password
     *                                  should be changed.
     *
     * @return html the form to add or edit users.
     */
    private function createUserForm($title, $message, $params, $passwordMessage=null)
    {
        if ($this->isAdminMode()) {
            $readonly = $this->preventAdminChangeAdminAcronym($params);
        } elseif ($this->isUserMode()) {
            $readonly = $this->preventUserChangeUserAcronym($params);
        } else {
            $readonly = null;
        }


        $output = <<<EOD
        <form method=post>
            <fieldset>
                <legend>{$title}</legend>
                <input type='hidden' name='id' value="{$params['id']}"/>
                <p><label>Användarnamn:<br/><input type='text' name='acronym' value="{$params['acronym']}" {$readonly}/></label></p>
                <p><label>Namn:<br/><input type='text' name='name' value="{$params['name']}" /></label></p>
                <p><label>Information:<br/><textarea name='info'>{$params['info']}</textarea></label></p>
                <p><label>E-post:<br/><input type='text' name='email' value="{$params['email']}"/></label></p>
                <p><label>Lösenord {$passwordMessage}:<br/><input type='password' name='password' value="{$params['password']}"/></label></p>
                <p><input type='submit' name='save' value='Spara'/></p>
                <output>Meddelande: {$message}</output>
            </fieldset>
        </form>
EOD;

        return $output;
    }

    private function preventAdminChangeAdminAcronym($params)
    {
        $readonly = null;
        if ($this->isAdminMode() && strcmp($params['acronym'] , 'admin') === 0) {
            $readonly = "readonly";
        }

        return $readonly;
    }

    private function preventUserChangeUserAcronym($params)
    {
        $readonly = null;
        if ($this->isUserMode() && strcmp($params['acronym'] , 'admin') !== 0) {
            $readonly = "readonly";
        }

        return $readonly;
    }

    /**
     * Creates a edit user form.
     *
     * Creates a form to edit user information for a user. If the edit of an
     * user fails, the user is informed.
     *
     * @param  string $title    the title of the form.
     * @param  [] $res          the result of users from the database.
     *
     * @param  string $message information to the user.
     *
     * @return html the form to edit a user.
     */
    public function createEditUserInDbForm($title, $res, $message)
    {
        $params = $this->getUserProfileParametersFromDb($res);
        if ($this->isUserMode($params['acronym'])) {
            if (isset($params)) {
                $passwordMessage = "(*Fyll i endast om du vill byta lösenord!)";
                $output = $this->createUserForm($title, $message, $params, $passwordMessage);
            } else {
                $output = "<p>Felaktigt id! Det finns inget konto med sådant id i databasen!</p>";
            }
        } else {
                $output = "<p>Du har inte rättigheter att ändra innehållet på kontot!</p>";
        }

        return $output;
    }

    /**
     * Helper function to get user profile parameters from database.
     *
     * Gets the user profile parameters and cleans the parameters with the
     * function htmlentities before stored in array.
     *
     * @param  [] $res the result of users from the database.
     *
     * @return [] the array with cleaned user profile parameters.
     */
    private function getUserProfileParametersFromDb($res)
    {
        $params = null;
        if (isset($res) && !empty($res)) {
            $param = $res[0];
            $params = array(
                'id' => htmlentities($param->id, null, 'UTF-8'),
                'acronym' => htmlentities($param->acronym, null, 'UTF-8'),
                'name' => htmlentities($param->name, null, 'UTF-8'),
                'info' => htmlentities($param->info, null, 'UTF-8'),
                'email' => htmlentities($param->email, null, 'UTF-8'),
                'password' => null
            );
        }

        return $params;
    }

    /**
     * Helper method to check if the user has user rights.
     *
     * Checks if the user has user rights. Admin has full rights and other users
     * has only rights if the profile has their own acronym. Can only edit their
     * own profile.
     *
     * @param string  $user other user than admin.
     *
     * @return boolean true if the user has user rights, false otherwise.
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
     * Generates user form to for admin to create a new user.
     *
     * Generates a form for admin to create a new user profile to the database.
     *
     * @return html the form for a admin to create new users.
     */
    public function generateUserAdminForm()
    {
        $form = null;
        if ($this->isAdminMode()) {
            $form .= <<<EOD
            <form class='user-admin-form'>
                <fieldset>
                    <legend>Skapa nytt konto</legend>
                    <button type="button" onClick="parent.location='user_create.php'">Skapa nytt konto</button>
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
     * Creates a delete user form.
     *
     * Creates a form for admin to delete users from the databasen. Returns a
     * message of the result.
     *
     * @param  string $title    the title of the form.
     * @param  [] $res          the result of users from the databasen.
     * @param  string $message  the information to a admin.
     *
     * @return html the form at success, otherwise a message about the problem.
     */
    public function createDeleteUserInDbFrom($title, $res, $message)
    {
        $params = $this->getUserProfileParametersFromDb($res);

        if ($this->isAdminMode()) {
            $output = $this->createDeleteUserForm($title, $message, $params);
        } else {
                $output = "<p>Du har inte rättigheter att radera kontot!</p>";
        }

        return $output;
    }

    /**
     * Helper  function to create a delete user form.
     *
     * Creates a form to delete users from the database.
     *
     * @param  string $title    the title of the form.
     * @param  string $message  the information to a admin.
     * @param  [] $params       the values for the fields in the form.
     *
     * @return html the form to delete users in the database.
     */
    private function createDeleteUserForm($title, $message, $params)
    {
        $output = <<<EOD
        <form method=post>
            <fieldset>
                <legend>{$title}</legend>
                <input type='hidden' name='id' value="{$params['id']}"/>
                <p><label>Användarnamn:<br/><input type='text' name='acronym' value="{$params['acronym']}" readonly/></label></p>
                <p><label>Namn:<br/><input type='text' name='name' value="{$params['name']}" readonly/></label></p>
                <p><label>Information:<br/><textarea name='info' readonly>{$params['info']}</textarea></label></p>
                <p><label>E-post:<br/><input type='text' name='email' value="{$params['email']}" readonly/></label></p>
                <p><input type='submit' name='delete' value='Radera'/></p>
                <output>Meddelande: {$message}</output>
            </fieldset>
        </form>
EOD;

        return $output;
    }
}
