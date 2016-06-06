<?php

/**
 * @copyright   Nick Espig <nickespig.xyz>
 * @author      Nick Espig <info@nickespig.xyz>
 * @package     imagely
 * @version     1.0
 * @subpackage  core
 */
class User
{

    /**
     * Get All users from database
     *
     * @return array - Array of all Users with the following data: id, name, password as php password hash and 1 or 0
     *               as isAdmin
     * @throws Exception - If SQL statement isn't working
     */
    public function getAll()
    {
        try {
            $sth = $GLOBALS['db']->prepare('SELECT * FROM user');
            $sth->execute();
            $return = json_decode(json_encode($sth->fetchAll()), TRUE);

            return $return;

        } catch (\Exception $e) {
            throw new Exception("Error  : " . $e->getMessage());
        }
    }

    /**
     * Get ID's of all users
     *
     * @return array - Array with all user ID's
     * @throws Exception - If SQL statement isn't working
     */
    public function getAvailableUsersId()
    {
        try {
            $return = [];
            $sth    = $GLOBALS['db']->prepare('SELECT id FROM user');
            $sth->execute();
            while ($result = $sth->fetchALL()) {
                $return[] = (int)$result['id'];
            }

            return $return;
        } catch (\Exception $e) {
            throw new Exception("Error  : " . $e->getMessage());
        }
    }

    /**
     * Get User by ID
     *
     * @param int $userID - ID of the specific user
     *
     * @return array - Full array from this specific user, with the following data: id, name, password as php password
     *               hash and 1 or as isAdmin
     * @throws Exception - If SQL statement isn't working
     */
    public function getUserById(int $userID)
    {
        try {
            $sth = $GLOBALS['db']->prepare('SELECT * FROM user WHERE id=' . $userID);
            $sth->execute();
            $return = json_decode(json_encode($sth->fetch()), TRUE);

            return $return;

        } catch (\Exception $e) {
            throw new Exception("Error  : " . $e->getMessage());
        }
    }

    /**
     * Get Username by user ID
     *
     * @param int $userID - User ID
     *
     * @return String - Username
     * @throws Exception - If SQL statement isn't working
     */
    public function getNameById(int $userID)
    {
        try {
            $sth = $GLOBALS['db']->prepare('SELECT name FROM user WHERE id=' . $userID);
            $sth->execute();
            $result = json_decode(json_encode($sth->fetch()), TRUE);

            return $result['name'];

        } catch (\Exception $e) {
            throw new Exception("Error  : " . $e->getMessage());
        }
    }

    /**
     * Get user ID by username
     *
     * @param String $username - Username
     *
     * @return string - userID
     * @throws Exception - If SQL statement isn't working
     */
    public function getIdByName(string $username)
    {
        try {
            $sth = $GLOBALS['db']->prepare('SELECT id FROM user WHERE name=' . $username);
            $sth->execute();

            $result = json_decode(json_encode($sth->fetch()), TRUE);

            return ($result !== NULL) ? (int)$result['id'] : FALSE;

        } catch (\Exception $e) {
            throw new Exception("Error  : " . $e->getMessage());
        }
    }

    /**
     * Creates a user
     *
     * @param array $data - Array with the values username and password
     *
     * @throws Exception - If SQL statement isn't working
     */
    public function createUser(array $data)
    {
        try {

            $name     = $data['username'];
            $password = password_hash($data['password'], PASSWORD_DEFAULT);
            //fixme: something is not right here
            $sth = $GLOBALS['db']->prepare('INSERT INTO user (name, password) VALUES (\'' . $name . '\', \'' . $password . '\')');
            $sth::execute();

        } catch (\Exception $e) {
            throw new Exception("Error  : " . $e->getMessage());
        }
    }

    /**
     * Updates an existing user
     *
     * @param int    $userID   - Useres ID
     * @param string $password - Password
     * @param mixed  $isAdmin  - Can be boolean or integer
     *
     * @throws Exception - If SQL statement isn't working
     *
     */
    public function editUserByID(int $userID, string $password, bool $isAdmin)
    {
        try {
            $password = password_hash($password, PASSWORD_DEFAULT);
            if (isset($isAdmin)) {
                $isAdmin = ($isAdmin === TRUE || $isAdmin === 1 || $isAdmin === '1') ? 1 : 0;
                $sth     = $GLOBALS['db']->prepare('UPDATE user SET password=\'' . $password . '\', isAdmin=\'' . $isAdmin . '\' WHERE id=' . $userID);

            } else {
                $sth = $GLOBALS['db']->prepare('UPDATE user SET password=\'' . $password . '\' WHERE id=' . $userID);

            }
            $sth->execute();

        } catch (\Exception $e) {
            throw new Exception("Error  : " . $e->getMessage());
        }
    }

    /**
     * Get the password hash from a user
     *
     * @param int $userID - ID of the specific user
     *
     * @return String - The password hash as string
     * @throws Exception - If SQL statement isn't working
     */
    public function getHashById(int $userID)
    {
        try {
            $sth = $GLOBALS['db']->prepare('SELECT password FROM user WHERE id=' . $userID);
            $sth->execute();
            $result = json_decode(json_encode($sth->fetch()), TRUE);

            return $result['password'];
        } catch (\Exception $e) {
            throw new Exception("Error  : " . $e->getMessage());
        }

    }

    /**
     * Deletes a user by it's id
     *
     * @param int $userId - UserID
     *
     * @throws Exception - If SQL statement isn't working
     */
    public function deleteUserById(int $userId)
    {
        try {
            Gallery::deleteGalleryByUser($userId);
            $sth = $GLOBALS['db']->prepare('DELETE FROM user WHERE id=' . $userId);
            $sth::execute();
        } catch (\Exception $e) {
            throw new Exception("Error  : " . $e->getMessage());
        }
    }

    /**
     * Check if given user id matches with the given id or if the user is an admin, otherwise it will forward the user
     * to the given url
     *
     * @param int $userID      - The users ID
     * @param int $id          - ID you want to compare
     * @param int $redirectURL - URL to redirect to
     *
     * @return bool
     */
    public function checkIfOwnAccountOrRedirect(int $userId, int $id, string $redirectURL)
    {
        if ($id === $_SESSION['userId'] || User::isAdmin($userId) === 1) {
            return TRUE;
        }
        header($redirectURL);

        return FALSE;
    }

    /**
     * Checks if user is an admin
     *
     * @param int $id - UserID
     *
     * @return boolean - If he is an admin as boolean
     */
    public static function isAdmin(int $id)
    {
        $sth = $GLOBALS['db']->prepare('SELECT isAdmin FROM user WHERE id=' . $id);
        $sth->execute();
        $result = json_decode(json_encode($sth->fetch()), TRUE);

        $result = ($result['isAdmin'] == '1');

        return $result;
    }

}
