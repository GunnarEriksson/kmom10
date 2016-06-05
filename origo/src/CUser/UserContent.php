<?php
/**
 * Movie Content, handles the content of movies in the database.
 *
 */
class UserContent
{
    const SQLSTATE = '23000';
    const ERROR_DUPLICATE_KEY = 1062;

    private $db;
    private $isContentCreatedSuccessfully;

    /**
     * Constructor
     *
     * Initates the database.
     *
     * @param Database $db the database object.
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->isContentCreatedSuccessfully = false;
    }

    /**
     * Adds a user to the database.
     *
     * Adds a user to the database and returns a message if the user was added
     * or not. The user is not allowed to chose an already existing acronym
     *
     * @param [] $params the details of the user.
     *
     * @return string the messeage if the user was added or not.
     */
    public function addNewUserToDb($params)
    {
        $this->isContentCreatedSuccessfully = false;
        $message = $this->checkMandatoryParameters($params);

        if (!isset($message)) {
            $message = $this->addUserToDb($params);
        }

        return $message;
    }

    /**
     * Helper functio to check mandatory parameters.
     *
     * Checks if acronym, name and password is missing. If one or more mandatory
     * parameters are missing, a message is returned about the problem. Otherwise
     * null is returned.
     *
     * @param  [] $params user profile parameters.
     *
     * @return string at success, null is returned. Otherwise a message with the
     *                problem is returned.
     */
    private function checkMandatoryParameters($params)
    {
        if (empty($params[0])) {
            $message = 'Användarnamn saknas!';
        } else if (empty($params[1])) {
            $message = 'Namn saknas!';
        } else if (empty($params[4])) {
            $message = 'Lösenord saknas!';
        } else {
            $message = null;
        }

        return $message;
    }

    /**
     * Helper function to add a user to the database.
     *
     * Adds a user to the database and returns a message that the user is welcome
     * to Rental Movies. The function handles account could not be created, acronym
     * already exists and password could not be created. At failure, a message is
     * returned.
     *
     * @param [] $params user profile parameters.
     *
     * @return a message if an account could be created or not.
     */
    private function addUserToDb($params)
    {
        $sql = '
            INSERT INTO Rm_User (acronym, name, info, email, published, updated, salt)
                VALUES (?, ?, ?, ?, NOW(), NULL, UNIX_TIMESTAMP());
        ';

        $acronym = $params[0];
        $password = array_pop($params);

        $res = $this->db->ExecuteQuery($sql, $params);

        if ($res) {
            $resPassword = $this->createPassword($acronym, $password);
            if ($resPassword) {
                $message = 'Välkommen till Rental Movies. Du kan nu logga in med ditt id och lösenord.';
                $this->isContentCreatedSuccessfully = true;
            } else {
                $message = 'Konto kunde ej skapas. Lösenord kunde inte skapas!<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre>';
            }


        } else {
            $errorCode = $this->db->ErrorInfo();
            if ($errorCode[0] === UserContent::SQLSTATE && $errorCode[1] === UserContent::ERROR_DUPLICATE_KEY) {
                $message = 'Akronymen finns redan. Välj en annan akronym!';
            } else {
                $message = 'Konto kunde ej skapas!<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre>';
            }
        }

        return $message;
    }

    /**
     * Helper function to create a password.
     *
     * Updates the database with a password for a user. Encrypted with salt.
     *
     * @param  string $acronym  the acronym of the user.
     * @param  string $password the password the user has chosen.
     *
     * @return boolean true if the passord was created successfully, false otherwise.
     */
    private function createPassword($acronym, $password)
    {
        $passwordParams = array($password, $acronym);
        $sql = '
            UPDATE Rm_User SET
                password = md5(concat(?, salt))
            WHERE acronym = ?
        ';

        return $this->db->ExecuteQuery($sql, $passwordParams);
    }

    /**
     * Checks if the content has been created in the database.
     *
     * @return boolean true if content has been created in db, false otherwise;
     */
    public function isContentCreated()
    {
        return $this->isContentCreatedSuccessfully;
    }

    /**
     * Update user profile in database.
     *
     * Edits a user in the database and returns a message that the user profile
     * has been updated. The function handles account could not be updated,
     * name of user is missing, password could not be updated and acronym
     * already exists.
     *
     * @param [] $params user profile parameters.
     *
     * @return a message if an account could be updated or not.
     */
    public function updateUserInDb($params)
    {
        $message = $this->checkMandatoryUpdateUserParameters($params);

        if (!isset($message)) {
            $message = $this->updateUser($params);
        }

        return $message;
    }

    /**
     * Helper function to check mandatory parameters when updating a user..
     *
     * Checks if acronym, name and password is missing. If one or more mandatory
     * parameters are missing, a message is returned about the problem. Otherwise
     * null is returned.
     *
     * @param  [] $params user profile parameters.
     *
     * @return string at success, null is returned. Otherwise a message with the
     *                problem is returned.
     */
    private function checkMandatoryUpdateUserParameters($params)
    {
        if (empty($params[0])) {
            $message = 'Användarnamn saknas!';
        } else if (empty($params[1])) {
            $message = 'Namn saknas!';
        } else {
            $message = null;
        }

        return $message;
    }

    /**
     * Helper function to update user profile in database.
     *
     * Edits a user in the database and returns a message that the user profile
     * has been updated. The function handles account could not be updated, password
     * could not be updated and acronym already exists.
     *
     * @param [] $params user profile parameters.
     *
     * @return a message if an account could be updated or not.
     */
    private function updateUser($params)
    {
        // Should password be changed.
        $salt = isset($params[4]) ? ' , salt = UNIX_TIMESTAMP()' : null ;

        $sql = '
            UPDATE Rm_User SET
                acronym     = ?,
                name        = ?,
                info        = ?,
                email       = ?,
                updated     = NOW()';

        $sql .=  $salt . ' WHERE id = ?';

        $acronym = $params[0];
        $password = $params[4];
        array_splice($params, 4, -1);

        $res = $this->db->ExecuteQuery($sql, $params);

        if ($res) {
            if (isset($salt)) {
                $resPassword = $this->createPassword($acronym, $password);
                if ($resPassword) {
                    $message = 'Kontot har uppdaterats och nytt lösenord har skapats';
                } else {
                    $message = 'Lösenord kunde ej uppdateras!<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre>';
                }
            } else {
                $message = 'Kontot har uppdaterats';
            }
        } else {
            $errorCode = $this->db->ErrorInfo();
            if ($errorCode[0] === UserContent::SQLSTATE && $errorCode[1] === UserContent::ERROR_DUPLICATE_KEY) {
                $message = 'Akronymen finns redan. Välj en annan akronym!';
            } else {
                $message = 'Konto kunde ej uppdateras!<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre>';
            }
        }

        return $message;
    }

    /**
     * Deletes an user in the database.
     *
     * Deletes an user in the database based on the id for the user.
     *
     * @param  [] $params the id for the user to delete.
     *
     * @return [] the message if the user could be deleted or not.
     */
    public function deleteUserInDb($params)
    {
        $sql = 'DELETE FROM Rm_User WHERE id = ?';

        $res = $this->db->ExecuteQuery($sql, $params);

        if ($res) {
            $output = 'Användarkontot raderat';
        } else {
            $output = 'Användarkontot kunde EJ raderas';
        }

        return $output;
    }
}
