<?php

/**
 * @copyright   Nick Espig <nickespig.xyz>
 * @author      Nick Espig <info@nickespig.xyz>
 * @package     imagely
 * @version     1.0
 * @subpackage  core
 */
class Image
{

    /**
     * Get all images from one gallery by it's id
     *
     * @param int $galleryId - ID of the gallery
     *
     * @return array - Array of all images from this gallery
     */
    function getAllByGalleryId($galleryId)
    {
        $return       = [];
        $returned_set = $GLOBALS['db']->query('SELECT * FROM image WHERE galleryId=' . $galleryId);
        while ($result = $returned_set->fetch_array()) {
            $return[] = $result;
        }

        return $return;
    }

    /**
     * Creates the reference of an image in the DB
     *
     * @param array $request - Array with the following data : galleryId, imagePath, thumbnailPath
     */
    function referImageInDB($request)
    {
        $galleryId     = $request['galleryId'];
        $imagePath     = $request['imagePath'];
        $thumbnailPath = $request['thumbnailPath'];;

        $GLOBALS['db']->exec('INSERT INTO image (galleryId, imagePath, thumbnailPath) VALUES (\'' . $galleryId . '\', \'' . $imagePath . '\', \'' . $thumbnailPath . '\')');
    }

    /**
     * generates 3 different sizes of thumbnails
     *
     * @param string $imagePath - path to to original image
     */
    function createThumbnail($imagePath)
    {
        //TODO write this function
    }

    /**
     * Delete all images in one gallery by it's id
     *
     * @param int $galleryId - Gallery ID
     */
    function deleteImageByGalleryId($galleryId)
    {
        $returned_set = $GLOBALS['db']->query('SELECT id FROM image WHERE galleryId=' . $galleryId);
        while ($result = $returned_set->fetch_array()) {
            Image::deleteImageById($result['id']);
        }
    }

    /**
     * Delete an image by its ID
     *
     * @param int $imageId - Image ID
     */
    function deleteImageById($imageId)
    {
        $entry = Image::getImageByID($imageId);

        //Unlink (delete) outdated image
        $unlinkImagePath      = $entry['imagePath'];
        $unlinkThumbnailPath1 = $entry['thumbnailPath'];
        $unlinkThumbnailPath2 = $entry['thumbnailPath2'];
        $unlinkThumbnailPath3 = $entry['thumbnailPath3'];
        unlink(DOCUMENT_ROOT . $unlinkImagePath);
        unlink(DOCUMENT_ROOT . $unlinkThumbnailPath1);
        unlink(DOCUMENT_ROOT . $unlinkThumbnailPath2);
        unlink(DOCUMENT_ROOT . $unlinkThumbnailPath3);

        $GLOBALS['db']->exec('DELETE FROM image WHERE id=' . $imageId);
    }

    /**
     * Get every information of an image, by its ID
     *
     * @param int $imageID - Image ID
     *
     * @return mixed - Array of the image information with these keys: id, galleryId, imagePath, thumbnailPath,
     *               thumbnailPath2 and thumbnailPath3
     */
    function getImageByID($imageID)
    {
        $returned_set = $GLOBALS['db']->query('SELECT * FROM image WHERE id=' . $imageID);
        $return       = $returned_set->fetch_array();

        return $return;
    }
}
