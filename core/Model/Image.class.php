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
     * Generates 3 different sizes of thumbnails 100px, 200px & 400px height
     *
     * @param string $image - Path to picture from which the thumbs should be created
     * @param string $path  - Folder in which the thumbnails will be saved
     *
     * @return mixed - False if the given image does not exist otherwise an array with the 3 different sizes of thumbnails:
     *               small => path, medium => path, large => path
     */
    public static function createThumbnail(string $image, string $initialPath = NULL)
    {
        $root = dirname(dirname(__DIR__) . '../');

        $imagePath = $root . ((substr($image, 0, 1) == '/' || substr($image, 0, 1) == '\\') ? '' : DIRECTORY_SEPARATOR) . $image;
        if ($initialPath !== NULL) {
            $possibleSlashStart = (substr($initialPath, 0, 1) == '/' || substr($initialPath, 0, 1) == '\\') ? '' : DIRECTORY_SEPARATOR;
            $possibleSlashEnd   = (substr($initialPath, count($initialPath) - 1) == '/' || substr($initialPath, count($initialPath) - 1) == '\\') ? '' : DIRECTORY_SEPARATOR;
            $initialPath        = $possibleSlashStart . $initialPath . $possibleSlashEnd;
            $path               = $root . $initialPath;
        } else {
            $path        = dirname($imagePath) . DIRECTORY_SEPARATOR;
            $initialPath = str_replace($root, '', $path);
        }

        if (!is_dir($path))
            mkdir($path, 0755, TRUE);
        elseif (!file_exists($imagePath))
            return false;

        $name = basename($image);

        $result = [];

        $imageInfo = getimagesize($imagePath);
        $width     = $imageInfo[0];
        $height    = $imageInfo[1];

        $new_height = [
            'small'  => 100,
            'medium' => 200,
            'large'  => 400,
        ];
        foreach ($new_height as $sizeName => $new_thumb_height) {
            $thumbname = $name . '.' . $sizeName . '.thumb.png';
            $new_width = floor($width * ($new_thumb_height / $height));

            list(, , $type) = $imageInfo;

            $type               = image_type_to_extension($type);
            $getResourceOfImage = 'imagecreatefrom' . $type;
            $getResourceOfImage = str_replace('.', '', $getResourceOfImage);
            $img                = $getResourceOfImage($imagePath);
            $tmp_img            = imagecreatetruecolor($new_width, $new_thumb_height);
            imagecopyresized($tmp_img, $img, 0, 0, 0, 0, $new_width, $new_thumb_height, $width, $height);

            imagepng($tmp_img, "{$path}{$thumbname}");

            $result[ $sizeName ] = $initialPath . $thumbname;
        }

        return $result;
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
