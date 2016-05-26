<?php

/**
 * @copyright   Nick Espig <nicku8.com>
 * @author      Nick Espig <info@nicku8.com>
 * @package     imagely
 * @version     1.0
 * @subpackage  core
 */
class Gallery
{

    /**
     * @return array
     */
    function getAll()
    {
        $return       = [];
        $returned_set = $GLOBALS['db']->query('SELECT * FROM gallery ORDER BY creationDate DESC');
        while ($result = $returned_set->fetch_array()) {
            $return[] = $result;
        }

        return $return;
    }

    /**
     * @param $author
     *
     * @return array
     */
    function getAllByAuthor($author)
    {
        $return       = [];
        $returned_set = $GLOBALS['db']->query('SELECT * FROM gallery WHERE author=' . $author . ' ORDER BY creationDate DESC');
        while ($result = $returned_set->fetch_array()) {
            $return[] = $result;
        }

        return $return;
    }

    /**
     * @param $id
     *
     * @return String
     */
    function getAuthorNameById($id)
    {
        $user = new User;

        return $user->getNameById($id);
    }

    /**
     * @return array
     */
    function getAvailableGalleries()
    {
        $return       = [];
        $returned_set = $GLOBALS['db']->query('SELECT id FROM gallery');
        while ($result = $returned_set->fetch_array()) {
            $return[] = $result['id'];
        }

        return $return;
    }

    /**
     * @param $request
     */
    function createGallery($request)
    {
        $author       = $request['author'];
        $status       = htmlentities($request['status']);
        $name         = htmlentities($request['name']);
        $description  = htmlentities($request['description']);
        $creationDate = $request['creationDate'];
        $modifiedDate = $request['modifiedDate'];
        $GLOBALS['db']->exec('INSERT INTO gallery (author, status, name, description, creationDate, modifiedDate) VALUES (\'' . $author . '\', \'' . $status . '\', \'' . $name . '\', \'' . $description . '\', \'' . $creationDate . '\', \'' . $modifiedDate . '\')');
    }

    /**
     * @param $request
     */
    function editGallery($request)
    {
        $id           = htmlentities($request['id']);
        $status       = htmlentities($request['status']);
        $name         = htmlentities($request['name']);
        $description  = htmlentities($request['description']);
        $modifiedDate = $request['modifiedDate'];
        $GLOBALS['db']->exec('UPDATE gallery SET status=\'' . $status . '\', name=\'' . $name . '\', description=\'' . $description . '\', modifiedDate=\'' . $modifiedDate . '\' WHERE id=' . $id);
    }

    /**
     * @param $userId
     */
    function deleteGalleryByUser($userId)
    {
        $returned_set = $GLOBALS['db']->query('SELECT id FROM gallery WHERE author=' . $userId);
        while ($result = $returned_set->fetch_array()) {
            Gallery::deleteGalleryById($result['id']);
        }
    }

    /**
     * @param $id
     */
    function deleteGalleryById($id)
    {
        $GLOBALS['db']->exec('DELETE FROM gallery WHERE id=' . $id);
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    function getGalleryById($id)
    {
        $returned_set          = $GLOBALS['db']->query('SELECT * FROM gallery WHERE id=' . $id);
        $return                = $returned_set->fetch_array();
        $return['description'] = html_entity_decode($return['description']);

        return $return;
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    function getImageByGalleryId($id)
    {
        $returned_set = $GLOBALS['db']->query('SELECT * FROM image WHERE galleryId=' . $id);
        $return       = $returned_set->fetch_array();

        return $return;
    }

    /**
     * @param $userId
     * @param $id
     * @param $redirect
     *
     * @return bool
     */
    function checkIfOwnGalleryRedirect($userId, $id, $redirect)
    {
        $return       = [];
        $returned_set = $GLOBALS['db']->query('SELECT id FROM gallery WHERE author=' . $userId);
        while ($result = $returned_set->fetch_array()) {
            $return[] = (string)$result['id'];
        }
        if (in_array($id, $return, TRUE) || User::isAdmin($userId) == 1) {
            return TRUE;
        }
        header($redirect);
    }

}
