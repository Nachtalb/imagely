<?php

/**
 * @copyright   Nick Espig <nicku8.com>
 * @author      Nick Espig <info@nicku8.com>
 * @package     imagely
 * @version     1.0
 * @subpackage  core
 */
class Image
{

    function getAllByGalleryId($galleryId)
    {
        $return       = [];
        $returned_set = $GLOBALS['db']->query('SELECT * FROM image WHERE galleryId=' . $galleryId);
        while ($result = $returned_set->fetch_array()) {
            $return[] = $result;
        }

        return $return;
    }

    function createImage($request)
    {
        $galleryId     = $request['galleryId'];
        $imagePath     = $request['imagePath'];
        $thumbnailPath = $request['thumbnailPath'];;

        $GLOBALS['db']->exec('INSERT INTO image (galleryId, imagePath, thumbnailPath) VALUES (\'' . $galleryId . '\', \'' . $imagePath . '\', \'' . $thumbnailPath . '\')');
    }

    function createThumbnail($imagePath)
    {
        //TODO write this function
    }

    function deleteImageByGalleryId($galleryId)
    {
        $returned_set = $GLOBALS['db']->query('SELECT id FROM image WHERE galleryId=' . $galleryId);
        while ($result = $returned_set->fetch_array()) {
            Image::deleteImageById($result['id']);
        }
    }

    function deleteImageById($id)
    {
        $entry = Image::getEntryById($id);

        //Unlink (delete) outdated image
        $unlinkImagePath     = $entry['imagePath'];
        $unlinkThumbnailPath = $entry['thumbnailPath'];
        unlink(DOCUMENT_ROOT . $unlinkImagePath);
        unlink(DOCUMENT_ROOT . $unlinkThumbnailPath);

        $GLOBALS['db']->exec('DELETE FROM image WHERE id=' . $id);
    }

    function getEntryById($id)
    {
        $returned_set = $GLOBALS['db']->query('SELECT * FROM image WHERE id=' . $id);
        $return       = $returned_set->fetch_array();

        return $return;
    }

    function generateRandomString($length = 10)
    {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    }

}
