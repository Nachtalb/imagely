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
    public function getAll()
    {
        $sth = $GLOBALS['db']->prepare('SELECT * FROM `gallery` ORDER BY `creationDate` DESC');
        $sth->execute();
        $result = $sth->fetchAll();
        $return = json_decode(json_encode($result), TRUE);

        for ($i = 0 ; $i < count($return) ; $i++) {
            $return[ $i ]['description'] = html_entity_decode($return[ $i ]['description']);

            $date = new DateTime();
            $date->setTimestamp($return[ $i ]['creationDate']);
            $return[ $i ]['creationDate'] = $date->format('d.m.Y H:i');
            $date2                        = new DateTime();
            $date2->setTimestamp($return[ $i ]['modifiedDate']);
            $return[ $i ]['modifiedDate'] = $date2->format('d.m.Y H:i');
        }

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
    public function getAllGalleriesByUserID(int $userID)
    {
        $sth = $GLOBALS['db']->prepare('SELECT * FROM `gallery` WHERE `author`=' . $userID . ' ORDER BY `creationDate` DESC');
        $sth->execute();
        $result = $sth->fetchAll();
        $return = json_decode(json_encode($result), TRUE);

        for ($i = 0 ; $i < count($return) ; $i++) {
            $return[ $i ]['description'] = html_entity_decode($return[ $i ]['description']);

            $date = new DateTime();
            $date->setTimestamp($return[ $i ]['creationDate']);
            $return[ $i ]['creationDate'] = $date->format('d.m.Y H:i');
            $date2                        = new DateTime();
            $date2->setTimestamp($return[ $i ]['modifiedDate']);
            $return[ $i ]['modifiedDate'] = $date2->format('d.m.Y H:i');
        }

        return $return;
    }

    /**
     * Gets all ID's of all galleries
     *
     * @return array - Array with all galleryID's
     */
    public function getAvailableGalleries()
    {
        $sth = $GLOBALS['db']->prepare('SELECT `id` FROM `gallery`');
        $sth->execute();
        $result = $sth->fetchAll();
        $return = json_decode(json_encode($result), TRUE);

        for ($i = 0 ; $i < count($return) ; $i++) {
            $return[ $i ]['description'] = html_entity_decode($return[ $i ]['description']);

            $date = new DateTime();
            $date->setTimestamp($return[ $i ]['creationDate']);
            $return[ $i ]['creationDate'] = $date->format('d.m.Y H:i');
            $date2                        = new DateTime();
            $date2->setTimestamp($return[ $i ]['modifiedDate']);
            $return[ $i ]['modifiedDate'] = $date2->format('d.m.Y H:i');
        }

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
    public function createGallery(array $data)
    {
        Image::checkImage($data['image']['tmp_name']);

        $author       = $data['author'];
        $status       = $data['status'];
        $name         = htmlentities($data['name']);
        $description  = htmlentities($data['description']);
        $creationDate = $data['creationDate'];
        $modifiedDate = $data['modifiedDate'];

        $teaserImage = $this->createTeaserImage($data['image']['tmp_name']);

        $teaserImagePath      = $teaserImage['imagePath'];
        $teaserImageThumbnail = $teaserImage['imageThumbnailPath'];

        $sth      = $GLOBALS['db']->prepare('INSERT INTO `gallery` (`author`,`status`,`name`,`description`,`creationDate`,`modifiedDate`,`teaserImage`,`teaserImageThumbnail1`,`teaserImageThumbnail2`,`teaserImageThumbnail3`) VALUES (:author,:status,:name,:description,:creationDate,:modifiedDate,:teaserImage,:teaserImageThumbnail1,:teaserImageThumbnail2,:teaserImageThumbnail3)');
        $bindings = [
            ':author'                => $author,
            ':status'                => $status,
            ':name'                  => $name,
            ':description'           => $description,
            ':creationDate'          => $creationDate,
            ':modifiedDate'          => $modifiedDate,
            ':teaserImage'           => $teaserImagePath,
            ':teaserImageThumbnail1' => $teaserImageThumbnail['small'],
            ':teaserImageThumbnail2' => $teaserImageThumbnail['medium'],
            ':teaserImageThumbnail3' => $teaserImageThumbnail['large'],
        ];
        $sth->execute($bindings);

    }

    /**
     * creates TeaseImage and the gallery folder, as well as the thumbnails
     *
     * @param string   $image     - Path to the image
     * @param null|int $galleryID - When it's set it uses this galleryID
     * @param null|int $userID    - When it's set it uses this userID
     *
     * @return array - Array with the teaser image path and the thumbnail paths
     *               ['imagePath'] => teaserImgPath, ['imageThumbnailPath] => ['small'] => path, ['medium'] => path,
     * ['large'] => path
     * @throws Exception - If the given file is not an image
     */
    private function createTeaserImage(string $image, int $galleryID = NULL, int $userID = NULL)
    {
        $info = getimagesize($image);
        if ($info[2] == IMAGETYPE_GIF)
            throw new Exception('Teaser image can\'t be a GIF, it has to be a JPEG or a PNG!');

        $galleryID = ($galleryID !== NULL) ? $galleryID : $this->getNextGalleryId();
        $userID    = ($userID !== NULL) ? $userID : $_SESSION['userId'];

        $folderPath = '/data/media/gallery/' . $userID . DIRECTORY_SEPARATOR . $galleryID . DIRECTORY_SEPARATOR;
        $imageName  = 'teaserImage';

        $finalImagePaht = $folderPath . $imageName . '.png';
        if (!is_dir(DOCUMENT_ROOT . $folderPath))
            mkdir(DOCUMENT_ROOT . $folderPath, 0755, TRUE);

        imagepng(imagecreatefromstring(file_get_contents($image)), DOCUMENT_ROOT . $finalImagePaht);
        $thumnail = Image::createThumbnail($finalImagePaht);

        $result = [
            'imagePath'          => $finalImagePaht,
            'imageThumbnailPath' => $thumnail,
        ];

        return $result;
    }


    /**
     * Gets the number of the next gallery which will be created
     *
     * @return int - Number of next gallery
     */
    private function getNextGalleryId()
    {
        $sth = $GLOBALS['db']->prepare('SELECT `AUTO_INCREMENT` FROM `information_schema`.`tables` WHERE `table_name` = \'gallery\' AND `table_schema` = \'imagely\' ');
        $sth->execute();

        $result = json_decode(json_encode($sth->fetch()), TRUE);

        return (int)$result['AUTO_INCREMENT'];
    }

    /**
     * Updates a gallery
     *
     * @param array $data    - New gallery data  must contain: id : int, status : boolean, name : string, description :
     *                       string and modifiedDate : string (will be change to timestamp in the future)
     */
    public function editGallery(array $data)
    {
        $ID           = htmlentities($data['id']);
        $status       = $data['status'];
        $name         = htmlentities($data['name']);
        $description  = htmlentities($data['description']);
        $modifiedDate = $data['modifiedDate'];

        if ($data['image'] !== NULL) {
            Image::checkImage($data['image']['tmp_name']);
            $teaserImage = $this->createTeaserImage($data['image']['tmp_name'], $ID);

            $teaserImagePath      = $teaserImage['imagePath'];
            $teaserImageThumbnail = $teaserImage['imageThumbnailPath'];

            $sth      = $GLOBALS['db']->prepare('UPDATE `gallery` SET `status`=:status,`name`=:name,`description`=:description,`modifiedDate`=:modifiedDate,`teaserImage`=:teaserImage,`teaserImageThumbnail1`=:teaserImageThumbnail1,`teaserImageThumbnail2`=:teaserImageThumbnail2,`teaserImageThumbnail3`=:teaserImageThumbnail3 WHERE `id`=:ID');
            $bindings = [
                ':status'                => $status,
                ':name'                  => $name,
                ':description'           => $description,
                ':modifiedDate'          => $modifiedDate,
                ':teaserImage'           => $teaserImagePath,
                ':teaserImageThumbnail1' => $teaserImageThumbnail['small'],
                ':teaserImageThumbnail2' => $teaserImageThumbnail['medium'],
                ':teaserImageThumbnail3' => $teaserImageThumbnail['large'],
                ':ID'                    => $ID,
            ];
        } else {
            $sth      = $GLOBALS['db']->prepare('UPDATE `gallery` SET `status`=:status,`name`=:name,`description`=:description,`modifiedDate`=:modifiedDate WHERE `id`=:ID');
            $bindings = [
                ':status'       => $status,
                ':name'         => $name,
                ':description'  => $description,
                ':modifiedDate' => $modifiedDate,
                ':ID'           => $ID,
            ];

        }
        $sth->execute($bindings);
    }

    /**
     * Deletes all galleries from a user
     *
     * @param int $userID - Users ID
     */
    public static function deleteGalleryByUser(int $userID)
    {
        $galleries = self::getAllGalleriesByUserID($userID);

        foreach ($galleries as $gallery) {
            Image::deleteImageByGalleryId($gallery['id']);
        }

        $sth = $GLOBALS['db']->prepare('DELETE FROM `gallery` WHERE `author`=' . $userID);
        $sth->execute();
    }

    /**
     * Deletes Gallery by it's ID
     *
     * @param int $id - The Gallery's ID
     */
    public function deleteGalleryById(int $id)
    {
        $sth = $GLOBALS['db']->prepare('DELETE FROM `gallery` WHERE `id`=' . $id);
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
    public static function getGalleryById(int $id)
    {
        $sth = $GLOBALS['db']->prepare('SELECT * FROM `gallery` WHERE `id`=' . $id);
        $sth->execute();
        $return = json_decode(json_encode($sth->fetch()), TRUE);
        if ($return === FALSE)
            return FALSE;
        $return['description'] = html_entity_decode($return['description']);

        $date = new DateTime();
        $date->setTimestamp($return['creationDate']);
        $return['creationDate'] = $date->format('d.m.Y H:i');

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
    public function getImageByGalleryId(int $galleryID)
    {
        $sth = $GLOBALS['db']->prepare('SELECT * FROM `image` WHERE `galleryId`=' . $galleryID);
        $sth->execute();
        $return = json_decode(json_encode($sth->fetchAll()), TRUE);

        return $return;
    }

    /**
     * Check if given user id matches with the given id or if the user is an admin, otherwise it will forward the user
     * to the given url
     *
     * @param int    $userID      - The users ID
     * @param int    $id          - ID you want to compare
     * @param string $redirectURL - URL to redirect to
     *
     * @return bool
     */
    public function checkIfOwnAccountOrRedirect(int $userID, int $id, string $redirectURL)
    {
        $return = [];
        $sth    = $GLOBALS['db']->prepare('SELECT `id` FROM `gallery` WHERE `author`=' . $userID);
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

    /**
     * Check if given user id matches with the given id or if the user is an admin
     *
     * @param int|null $userID    - ID of the user
     * @param int      $galleryID - ID of the gallery
     *
     * @return bool
     */
    public static function checkIfOwnerOrAdmin(int $userID = NULL, int $galleryID)
    {
        $return = [];
        if ($userID !== NULL) {
            $sth = $GLOBALS['db']->prepare('SELECT `author` FROM `gallery` WHERE `id`=:galleryID');
            $sth->bindValue(':galleryID', $galleryID);
            $sth->execute();

            while ($result = json_decode(json_encode($sth->fetch()), TRUE)) {
                $return = $result['author'];
            }

            if ($return == $_SESSION['userId'] || User::isAdmin($userID) == 1) {
                return TRUE;
            }
        }

        return FALSE;
    }
}