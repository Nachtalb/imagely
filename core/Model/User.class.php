<?php

/**
 * @copyright   Nick Espig <nicku8.com>
 * @author      Nick Espig <info@nicku8.com>
 * @package     imagely
 * @version     1.0
 * @subpackage  core
 */
class User
{

    /** Get All users from database
     *
     * @return array : array with all users
     * @throws Exception
     */
    function getAll()
    {
        try {
            $return       = [];
            $returned_set = $GLOBALS['db']->query('SELECT * FROM user');
            while ($result = $returned_set->fetch_array()) {
                $return[] = $result;
            }

            return $return;

        } catch (\Exception $e) {
            throw new Exception("Error  : " . $e->getMessage());
        }
    }

    /**
     * get all user id's
     *
     * @return array : array with all user id's
     * @throws Exception
     */
    function getAvailableUsersId()
    {
        try {
            $return       = [];
            $returned_set = $GLOBALS['db']->query('SELECT id FROM user');
            while ($result = $returned_set->fetch_array()) {
                $return[] = (int)$result['id'];
            }

            return $return;
        } catch (\Exception $e) {
            throw new Exception("Error  : " . $e->getMessage());
        }
    }

    /**
     * get User by id
     *
     * @param int $id : Id of the user
     *
     * @return array : full array from this specific user
     * @throws Exception
     */
    function getUserById($id)
    {
        try {
            $returned_set = $GLOBALS['db']->query('SELECT * FROM user WHERE id=' . $id);
            $return       = $returned_set->fetch_array();

            return $return;

        } catch (\Exception $e) {
            throw new Exception("Error  : " . $e->getMessage());
        }
    }

    /**
     * get Username by user id
     *
     * @param int $id : user id
     *
     * @return String : Username
     * @throws Exception
     */
    function getNameById($id)
    {
        try {
            $returned_set = $GLOBALS['db']->query('SELECT name FROM user WHERE id=' . $id);
            $result       = $returned_set->fetch_array();

            return $result['name'];

        } catch (\Exception $e) {
            throw new Exception("Error  : " . $e->getMessage());
        }
    }

    /**
     * Get user id by username
     *
     * @param String $name : username
     *
     * @return string user id
     * @throws Exception
     */
    function getIdByName($name)
    {
        try {
            $returned_set = $GLOBALS['db']->query('SELECT id FROM user WHERE name=' . $name);
            $result       = $returned_set->fetch_array();

            return ($result !== NULL) ? (int)$result['id'] : FALSE;

        } catch (\Exception $e) {
            throw new Exception("Error  : " . $e->getMessage());
        }
    }

    /**
     * creates a user
     *
     * @param array $request : array with the values username and password
     *
     * @throws Exception
     */
    function createUser($request)
    {
        try {

            $name     = $request['username'];
            $password = password_hash($request['password'], PASSWORD_DEFAULT);
            //fixme: something is not right here
            $query = $GLOBALS['db']->query('INSERT INTO user (name, password) VALUES (\'' . $name . '\', \'' . $password . '\')');
            $query::execute();

        } catch (\Exception $e) {
            throw new Exception("Error  : " . $e->getMessage());
        }
    }

    /**
     * updates an existing user
     *
     * @param array $data : array with the new password
     *
     * @throws Exception
     */
    function editUserById($data)
    {
        //todo : make this work
        try {
            $password = password_hash($data['password'], PASSWORD_DEFAULT);
            $isAdmin  = 0;
            $query    = $GLOBALS['db']->query('UPDATE user SET (password=\'' . $password . '\', isAdmin=\'' . $isAdmin . '\') WHERE ');
            $query::execute();

        } catch (\Exception $e) {
            throw new Exception("Error  : " . $e->getMessage());
        }
    }

    /**
     * get the password hash from a user
     *
     * @param int $id : id of the specific user
     *
     * @return String : the password hash as string
     * @throws Exception
     */
    function getHashById($id)
    {
        try {
            $returned_set = $GLOBALS['db']->query('SELECT password FROM user WHERE id=' . $id);
            $result       = $returned_set->fetch_array();

            return $result['password'];
        } catch (\Exception $e) {
            throw new Exception("Error  : " . $e->getMessage());
        }

    }

    /**
     * deletes a user by it's id
     *
     * @param int $id : user id
     *
     * @throws Exception
     */
    function deleteUserById($id)
    {
        try {
            Gallery::deleteGalleryByUser($id);
            $query = $GLOBALS['db']->query('DELETE FROM user WHERE id=' . $id);
            $query::execute();
        } catch (\Exception $e) {
            throw new Exception("Error  : " . $e->getMessage());
        }
    }

    /**
     *
     * @param $userId
     * @param $id
     * @param $redirect
     *
     * @return bool
     */
    function checkIfOwnAccountRedirect($userId, $id, $redirect)
    {
        if ($id === $_SESSION['userId'] || User::isAdmin($userId) === 1) {
            return TRUE;
        }
        header($redirect);

        return FALSE;
    }

    /**
     * checks if user is admin
     *
     * @param int $id : user id
     *
     * @return boolean : returns true or false if the user is admin or not
     */
    function isAdmin($id)
    {
        $returned_set = $GLOBALS['db']->query('SELECT isAdmin FROM user WHERE id=' . $id);
        $result       = ($returned_set->fetch_array()['isAdmin'] == '1');

        return $result;
    }

}
