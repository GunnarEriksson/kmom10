<?php
/**
 * User admin form, provides user administration forms to be able to administrate
 * users in the database. Add a new user to database, edit a user in database and
 * delete a user from database.
 */
class UserAdminForm
{

    public function createAddUserToDbFrom($title, $message, $params=null)
    {
        $default = $this->createDefaultFormParameters();
        $params = array_merge($default, $params);

        return $this->createUserForm($title, $message, $params);
    }

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

    private function createUserForm($title, $message, $params, $passwordMessage=null)
    {
        $readonly = null;
        if ($this->isAdminMode()) {
            $readonly = "readonly";
        }

        $output = <<<EOD
        <form method=post>
            <fieldset>
                <legend>{$title}</legend>
                <input type='hidden' name='id' value="{$params['id']}"/>
                <p><label>Användarnamn:<br/><input type='text' name='acronym' value="{$params['acronym']}" {$readonly}/></label></p>
                <p><label>Namn:<br/><input type='text' name='name' value="{$params['name']}"/></label></p>
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

    private function getParameterFromCreateUserForm($formParams)
    {
        $params = null;
        if (isset($formParams) && !empty($formParams)) {
            $param = $res[0];
            $params = array(
                'id' => htmlentities($param->id, null, 'UTF-8'),
                'acronym' => htmlentities($param->acronym, null, 'UTF-8'),
                'name' => htmlentities($param->name, null, 'UTF-8'),
                'info' => htmlentities($param->info, null, 'UTF-8'),
                'email' => htmlentities($param->email, null, 'UTF-8')
            );
        }

        return $params;
    }

    public function createEditUserInDbFrom($title, $res, $message)
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

    public function createDeleteUserInDbFrom($title, $res, $message)
    {
        $params = $this->getUserProfileParametersFromDb($res);
        if ($this->isAdminMode()) {
            if (isset($params)) {
                $output = $this->createDeleteUserForm($title, $message, $params);
            } else {
                $output = "<p>Felaktigt id! Det finns inget konto med sådant id i databasen!</p>";
            }
        } else {
                $output = "<p>Du har inte rättigheter att radera kontot!</p>";
        }

        return $output;
    }

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
