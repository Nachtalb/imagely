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
        $sth    = $GLOBALS['db']->prepare('SELECT * FROM `image` WHERE `galleryId`=' . $galleryId);
        $sth->execute();

        $result = $sth->fetchAll();
        $return = json_decode(json_encode($result), TRUE);


        return $return;
    }

    /**
     * Controls the process to add images to a gallery
     *
     * @param array $files     - $files which should be added
     * @param int   $galleryID - GalleryID
     */
    public function addImagesToGallery(array $files, int $galleryID)
    {
        $userID = (isset($_SESSION['userId'])) ? (int)$_SESSION['userId'] : NULL;

        if (Gallery::getGalleryById($galleryID) !== FALSE && Gallery::checkIfOwnerOrAdmin($userID, $galleryID)) {
            foreach ($files as $file) {
                self::checkImage($file);
            }

            $newImages = [];

            foreach ($files as $file) {
                $path = DOCUMENT_ROOT . '/data/media/gallery/' . $_SESSION['userId'] .
                    DIRECTORY_SEPARATOR . $galleryID . DIRECTORY_SEPARATOR;

                $image['imagePath'] = $this->createPngFromImage($file, $path);
                $image['thumbnail'] = self::createThumbnail($image['imagePath']);

                $image['imagePath'] = str_replace(DOCUMENT_ROOT, '', $image['imagePath']);

                array_push($newImages, $image);
            }

            $this->referImageInDB($newImages, $galleryID);

            return TRUE;

        } else {
            return FALSE;
        }

    }

    /**
     * Creates the reference of an image in the DB
     *
     * @param array $images    - Array with the following data : imagePath, thumbnailPath => small, medium, large
     * @param int   $galleryID - ID of the gallery
     */
    private function referImageInDB(array $images, int $galleryID)
    {
        $query = 'INSERT INTO `image` (`galleryId`, `imagePath`, `thumbnailPath1`, `thumbnailPath2`, `thumbnailPath3`) VALUES ';

        foreach ($images as $image) {
            $imagePath      = $image['imagePath'];
            $thumbnailPath1 = $image['thumbnail']['small'];;
            $thumbnailPath2 = $image['thumbnail']['medium'];;
            $thumbnailPath3 = $image['thumbnail']['large'];;
            $query .= ' (' .
                '' . $galleryID . ', ' .
                '\'' . $imagePath . '\', ' .
                '\'' . $thumbnailPath1 . '\', ' .
                '\'' . $thumbnailPath2 . '\', ' .
                '\'' . $thumbnailPath3 . '\'),';
        }

        $query = str_lreplace(',', ';', $query);

        $sth = $GLOBALS['db']->prepare($query);
        $sth->execute();
    }

    /**
     * Generates 3 different sizes of thumbnails 100px, 200px & 400px height
     *
     * @param string $image - Path to picture from which the thumbs should be created
     * @param string $path  - Folder in which the thumbnails will be saved
     *
     * @return mixed - False if the given image does not exist otherwise an array with the 3 different sizes of
     *               thumbnails: small => path, medium => path, large => path
     */
    public static function createThumbnail(string $image, string $initialPath = NULL)
    {
        $root = dirname(dirname(__DIR__) . '../');

        $image     = str_replace($root, '', $image);
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
            return FALSE;

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
    public static function deleteImageByGalleryId(int $galleryId)
    {
        $sth = $GLOBALS['db']->prepare('SELECT `id` FROM `image` WHERE `galleryId`=' . $galleryId);
        $sth->execute();
        $result = $sth->fetchAll();
        $return = json_decode(json_encode($result), TRUE);
        foreach ($return as $gallery) {
            Image::deleteImageById((int) $gallery['id']);
        }
    }

    /**
     * Delete an image by its ID
     *
     * @param int $imageId - Image ID
     */
    public static function deleteImageById(int $imageId)
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

        $sth = $GLOBALS['db']->prepare('DELETE FROM `image` WHERE `id`=' . $imageId);
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
    public function getImageByID(int $imageID)
    {
        $sth = $GLOBALS['db']->prepare('SELECT * FROM `image` WHERE `id`=' . $imageID);
        $sth->execute();
        $return = $sth->fetchAll();

        return $return;
    }

    /**
     * Get average luminance of an image to determine if it's dark or light
     * Function from here: http://stackoverflow.com/a/5959461/5699307
     *
     * @param string $filename    - FilePath
     * @param int    $num_samples - Get average luminance, by sampling $num_samples times in both x,y directions
     *
     * @return float - Average luminance of the image
     */
    public static function get_avg_luminance(string $filename, int $num_samples = 20)
    {
        $img = imagecreatefrompng(DOCUMENT_ROOT . $filename);

        $width  = imagesx($img);
        $height = imagesy($img);

        $x_step = intval($width / $num_samples);
        $y_step = intval($height / $num_samples);

        $total_lum = 0;

        $sample_no = 1;

        for ($x = 0 ; $x < $width ; $x += $x_step) {
            for ($y = 0 ; $y < $height ; $y += $y_step) {

                $rgb = imagecolorat($img, $x, $y);
                $r   = ($rgb >> 16) & 0xFF;
                $g   = ($rgb >> 8) & 0xFF;
                $b   = $rgb & 0xFF;

                // choose a simple luminance formula from here
                // http://stackoverflow.com/questions/596216/formula-to-determine-brightness-of-rgb-color
                $lum = ($r + $r + $b + $g + $g + $g) / 6;

                $total_lum += $lum;

                // debugging code
                //           echo "$sample_no - XY: $x,$y = $r, $g, $b = $lum<br />";
                $sample_no++;
            }
        }

        // work out the average
        $avg_lum = $total_lum / $sample_no;

        return $avg_lum;
    }

    /**
     * Checks if a file is an image (gif/jpeg/png)
     *
     * @param string $image - Path to file
     *
     * @return bool - If it is an image
     * @throws Exception - If it's not an image
     */
    public static function checkImage(string $image)
    {
        $info = getimagesize($image);
        if ($info === FALSE) {
            throw new Exception("Unable to determine image type of uploaded file");
        }


        if (($info[2] !== IMAGETYPE_GIF) && ($info[2] !== IMAGETYPE_JPEG) && ($info[2] !== IMAGETYPE_PNG)) {
            throw new Exception("Not a gif/jpeg/png");
        }

        return TRUE;
    }

    /**
     * Creates an PNG out of an normal Image except for GIFs
     *
     * @param string $imagePath - Path of the image
     * @param string $path      - New Path for the image
     */
    public function createPngFromImage(string $imagePath, string $path)
    {
        $info      = getimagesize($imagePath);
        $imageName = uniqid();

        if ($info[2] !== IMAGETYPE_GIF) {
            $newPath = $path . $imageName . '.png';
            imagepng(imagecreatefromstring(file_get_contents($imagePath)), $newPath);
        } else {
            $newPath = $path . $imageName . '.gif';
            move_uploaded_file($imagePath, $newPath);
        }

        return $newPath;
    }
}
