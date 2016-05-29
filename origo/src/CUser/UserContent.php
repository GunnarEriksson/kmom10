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
        $message = $this->checkMandatoryParameters($params);

        if (!isset($message)) {
            $message = $this->addUserToDb($params);
        }

        return $message;
    }

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
            } else {
                $message = 'Konto kunde ej skapas!<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre>';
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

    private function createPassword($acronym, $password)
    {
        $passwordParams = array($password, $acronym);
        $sql = '
            UPDATE Rm_User SET
                password = md5(concat(?, salt))
            WHERE acronym = ?
        ';

        return $this->db->ExecuteQuery($sql, $passwordParams, true);
    }

    public function updateUserInDb($params)
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
                    $message = 'Konto kunde ej uppdateras!<br><pre>' . print_r($this->db->ErrorInfo(), 1) . '</pre>';
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
