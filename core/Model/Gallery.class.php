<?php

/**
 * @copyright   Nick Espig <nickespig.xyz>
 * @author      Nick Espig <info@nickespig.xyz>
 * @package     imagely
 * @version     1.0
 * @subpackage  core
 */
class Gallery
{

    /**
     * Get all information from all galleries sorted by creationDate
     *
     * @return array - Array of all galleries with these information: [n] - id, author, status, name, description,
     *               createDate and modifiedData
     */
    function getAll()
    {
        $sth = $GLOBALS['db']->prepare('SELECT * FROM gallery ORDER BY creationDate DESC');
        $sth->execute();
        $result = $sth->fetchAll();
        $return = json_decode(json_encode($result), TRUE);

        return $return;
    }

    /**
     * Get all galleries from a specific user by it's id sorted by creationDate
     *
     * @param int $userID - Authors ID
     *
     * @return array - Array of all galleries from the given userID with the following data: [n] - id, author, status,
     *               name, description, createDate and modifiedData
     */
    function getAllByAuthor($userID)
    {
        $sth = $GLOBALS['db']->prepare('SELECT * FROM gallery WHERE author=' . $userID . ' ORDER BY creationDate DESC');
        $sth->execute();
        $result = $sth->fetchAll();
        $return = json_decode(json_encode($result), TRUE);

        return $return;
    }

    /**
     * Gets all ID's of all galleries
     *
     * @return array - Array with all galleryID's
     */
    function getAvailableGalleries()
    {
        $sth = $GLOBALS['db']->prepare('SELECT id FROM gallery');
        $sth->execute();
        $result = $sth->fetchAll();
        $return = json_decode(json_encode($result), TRUE);

        return $return;
    }

    /**
     * Create a gallery
     *
     * @param array $data - Data of the gallery must contain these keys: author : int - the authors userID, status :
     *                    boolean - the status of the gallery, name : string - Name of the gallery, description :
     *                    string - description of the gallery , creationDate : string and modifiedDate : string - the
     *                    las two as formatted date (this will be changed to timestamp)
     */
    function createGallery($data)
    {
        $author       = $data['author'];
        $status       = htmlentities($data['status']);
        $name         = htmlentities($data['name']);
        $description  = htmlentities($data['description']);
        $creationDate = $data['creationDate'];
        $modifiedDate = $data['modifiedDate'];
        $sth          = $GLOBALS['db']->prepare('INSERT INTO gallery (author, status, name, description, creationDate, modifiedDate) VALUES (\'' . $author . '\', \'' . $status . '\', \'' . $name . '\', \'' . $description . '\', \'' . $creationDate . '\', \'' . $modifiedDate . '\')');
        $sth->execute();
    }

    /**
     * Updates a gallery
     *
     * @param array $request - New gallery data  must contain: id : int, status : boolean, name : string, description :
     *                       string and modifiedDate : string (will be change to timestamp in the future)
     */
    function editGallery($request)
    {
        $id           = htmlentities($request['id']);
        $status       = htmlentities($request['status']);
        $name         = htmlentities($request['name']);
        $description  = htmlentities($request['description']);
        $modifiedDate = $request['modifiedDate'];
        $sth          = $GLOBALS['db']->prepare('UPDATE gallery SET status=\'' . $status . '\', name=\'' . $name . '\', description=\'' . $description . '\', modifiedDate=\'' . $modifiedDate . '\' WHERE id=' . $id);
        $sth->execute();
    }

    /**
     * Deletes all galleries from a user
     *
     * @param int $userID - Users ID
     */
    function deleteGalleryByUser($userID)
    {
        $sth = $GLOBALS['db']->prepare('SELECT id FROM gallery WHERE author=' . $userID);
        $sth->execute();
        while ($result = json_decode(json_encode($sth->fetchAll()), TRUE)) {
            Gallery::deleteGalleryById($result['id']);
        }
    }

    /**
     * Deletes Gallery by it's ID
     *
     * @param int $id - The Gallery's ID
     */
    function deleteGalleryById($id)
    {
        $sth = $GLOBALS['db']->prepare('DELETE FROM gallery WHERE id=' . $id);
        $sth->execute();
    }

    /**
     * Gets all information from a specific gallery by it's ID
     *
     * @param int $id - Gallery ID
     *
     * @return mixed - An Array with this data: id, author - ID of the user, status, name, description, creationDate,
     *               modifiedDate
     */
    function getGalleryById($id)
    {
        $sth = $GLOBALS['db']->prepare('SELECT * FROM gallery WHERE id=' . $id);
        $sth->execute();
        $return                = json_decode(json_encode($sth->fetch()), TRUE);
        $return['description'] = html_entity_decode($return['description']);

        return $return;
    }

    /**
     * Gets all images from a gallery
     *
     * @param int $galleryID - the galleries ID
     *
     * @return mixed - An Array with the following data: [n] - id, galleryId, imagePath, thumbnailPath, thumbnailPath2
     *               and thumbnailPath3
     */
    function getImageByGalleryId($galleryID)
    {
        $sth = $GLOBALS['db']->prepare('SELECT * FROM image WHERE galleryId=' . $galleryID);
        $sth->execute();
        $return = json_decode(json_encode($sth->fetchAll()), TRUE);

        return $return;
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
    function checkIfOwnAccountOrRedirect($userID, $id, $redirectURL)
    {
        $return = [];
        $sth    = $GLOBALS['db']->prepare('SELECT id FROM gallery WHERE author=' . $userID);
        $sth->execute();
        while ($result = json_decode(json_encode($sth->fetch()), TRUE)) {
            $return[] = (string)$result['id'];
        }
        if (in_array($id, $return, TRUE) || User::isAdmin($userID) == 1) {
            return TRUE;
        }
        header($redirectURL);

        return FALSE;
    }

}
