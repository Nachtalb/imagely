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
    public function getAllByGalleryId(int $galleryId)
    {
        $return = [];
        $sth    = $GLOBALS['db']->prepare('SELECT * FROM image WHERE galleryId=' . $galleryId);
        $sth->execute();
        while ($result = $sth->fetchAll()) {
            $return[] = $result;
        }

        return $return;
    }

    /**
     * Creates the reference of an image in the DB
     *
     * @param array $request - Array with the following data : galleryId, imagePath, thumbnailPath
     */
    private function referImageInDB(array $request)
    {
        $galleryId     = $request['galleryId'];
        $imagePath     = $request['imagePath'];
        $thumbnailPath = $request['thumbnailPath'];;

        $sth = $GLOBALS['db']->prepare('INSERT INTO image (galleryId, imagePath, thumbnailPath) VALUES (\'' . $galleryId . '\', \'' . $imagePath . '\', \'' . $thumbnailPath . '\')');
        $sth->execute();
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
        $sth = $GLOBALS['db']->prepare('SELECT id FROM image WHERE galleryId=' . $galleryId);
        $sth->execute();
        while ($result = $sth->fetchAll()) {
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

        $sth = $GLOBALS['db']->prepare('DELETE FROM image WHERE id=' . $imageId);
        $sth->execute();
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
        $sth = $GLOBALS['db']->prepare('SELECT * FROM image WHERE id=' . $imageID);
        $sth->execute();
        $return = $sth->fetchAll();

        return $return;
    }
}
