<?php

/**
 * @copyright   Nick Espig <nickespig.xyz>
 * @author      Nick Espig <info@nickespig.xyz>
 * @package     Imagely
 * @version     1.0
 * @subpackage  core
 */
class Imagely
{

    /**
     * Language object
     *
     * @var Language
     */
    private $language;
    /**
     * Template Object
     *
     * @var Template
     */
    private $template;
    /**
     * Gallery Object
     *
     * @var Gallery
     */
    private $gallery;
    /**
     * Image Object
     *
     * @var Image
     */
    private $image;
    /**
     * User Object
     *
     * @var User
     */
    private $user;

    /**
     * Imagely constructor.
     * Starts session and loads all needed classes
     */
    function __construct()
    {
        //Start session
        session_start();
        //Include language
        require_once(DOCUMENT_ROOT . '/core/Model/Language.class.php');
        //Include gallery
        require_once(DOCUMENT_ROOT . '/core/Model/Gallery.class.php');
        //Include image
        require_once(DOCUMENT_ROOT . '/core/Model/Image.class.php');
        //Include user
        require_once(DOCUMENT_ROOT . '/core/Model/User.class.php');
        //Include template
        require_once(DOCUMENT_ROOT . '/core/View/Template.class.php');

        $this->language = new Language();
        $this->template = new Template();
        $this->gallery  = new Gallery();
        $this->image    = new Image();
        $this->user     = new User();
    }

    /**
     * Checks if user exists in session, to make sure that the user is logged in
     *
     * @return bool - If he's logged in
     */
    function checkSession()
    {
        //Check if username exists in session, to make sure that user is logged in
        if (isset($_SESSION['userId']) && isset($_SESSION['hash'])) {
            //Get hash from database and check if hash and id in session match, to make sure user is correctly logged in
            $correctHash = User::getHashById($_SESSION['userId']);
            if ($correctHash === $_SESSION['hash'] || User::isAdmin($_SESSION['userId']) === TRUE) {
                return TRUE;
            }

            return FALSE;
        }

        return FALSE;
    }

    /**
     * Controls the whole process from get link to show the site
     */
    function getPage()
    {
        //getAvailableLanguages
        $availableLanguages = $this->language->getAvailableLanguages();

        //getAvailableTemplates
        $availableTemplates = $this->template->pagesArr;

        //Get requestedLanguage & requestedTemplate
        $urlParts = explode('/', $_GET['__cap']);
        //Set requestedLanguage
        if ($this->template->getPageByName($urlParts[1]) !== FALSE) {
            if (!isset($_SESSION['lang']))
                $_SESSION['lang'] = $GLOBALS['CONFIG']['LANG'];
            $requestedParameter = (isset($urlParts[2])) ? $urlParts[2] : NULL;

            //todo get params
            /*            $get = '?';
                        foreach ($_GET as $key => $item) {
                            if ($key !== '__cap') {
                                $get .= $key . '=' . $item . '&';
                            }
                        }
                        $get = (count($get) >  2) ? $get : '';*/

            $this->redirectTo($urlParts[1], $_SESSION['lang'], $requestedParameter);

        } else if (!isset($urlParts[1]) || $urlParts[1] == 'index.php' || $urlParts[1] == '' || $urlParts[2] == '' || !in_array($urlParts[1], $availableLanguages, TRUE)) {
            if (!isset($_SESSION['lang']))
                $_SESSION['lang'] = $GLOBALS['CONFIG']['LANG'];
            $this->redirectTo('Home', $_SESSION['lang']);
        } else {
            $_SESSION['lang']  = $urlParts[1];
            $requestedLanguage = $urlParts[1];
        }
        //Set default site
        $defaultSite = 'Location: ' . PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . PATH_OFFSET . '/' . $requestedLanguage . '/' . $availableTemplates[0];

        //todo: if logged in do stuff
        // if(isset($_SESSION['userId']) && )

        //getLanguageArray by requestedLanguage
        $languageArray = $this->language->getLanguageArray($requestedLanguage);
        //Set requestedTemplate
        if (isset($urlParts[2]) && $this->template->getPageByName($urlParts[2])) {
            $requestedTemplate = $urlParts[2];

            if ($this->template->needsParams($requestedTemplate)) {
                if (isset($urlParts[3]) && $urlParts[3] != '') {
                    $requestedParameter = $urlParts[3];
                } else {
                    $_SESSION['SESSION_VARS']['TXT_INFO'] = '<div class="row"><div class="col-xs-12"><div class="alert alert-info">' .
                        $languageArray['TXT_IMAGELY_GENERAL_NOPARAS'] .
                        '</div></div></div>';
                    $this->redirectTo('Home');
                }
            }
        } else {
            echo '<pre>';
            var_dump($urlParts);
            echo '</pre>';
            die();
            $this->redirectTo('Home');
        }


        //getTemplate by requestedTemplate
        $page = $this->template->getTemplate($requestedTemplate);

        switch ($requestedTemplate) {
            case 'Home':
                $content   = NULL;
                $galleries = $this->gallery->getAll();
                foreach ($galleries as $item) {
                    $entry   = file_get_contents(DOCUMENT_ROOT . '/template/Module/Home//Entry.html');
                    $entry   = str_replace('{GALLERY_LINK_HREF}', '/' . $requestedLanguage . '/Gallery_Detail/' . $item['id'], $entry);
                    $entry   = str_replace('{TXT_GALLERY_NAME}', $item['name'], $entry);
                    $entry   = str_replace('{TXT_GALLERY_DESCRIPTION}', $item['description'], $entry);
                    $entry   = str_replace('{TXT_GALLERY_AUTHOR}', $this->user->getNameById($item['author']), $entry);
                    $entry   = str_replace('{TXT_GALLERY_DATE}', $item['creationDate'], $entry);
                    $content = $content . $entry;
                }
                $page = str_replace('{GALLERY_ENTRIES}', $content, $page);
                break;
            case 'Account_Admin':
                Imagely::checkSessionRedirect($defaultSite);
                Imagely::checkAdminRedirect($defaultSite);
                $contentAccounts = NULL;
                $users           = $this->user->getAll();
                foreach ($users as $key => $item) {
                    $entry           = file_get_contents(DOCUMENT_ROOT . '/template/Module/Account/Entry.html');
                    $entry           = str_replace('{ACCOUNT_DELETE_HREF}', '/' . $requestedLanguage . '/Account_DoDelete/' . $item['id'], $entry);
                    $entry           = str_replace('{ACCOUNT_EDIT_HREF}', '/' . $requestedLanguage . '/Account_Edit/' . $item['id'], $entry);
                    $entry           = str_replace('{TXT_ACCOUNT_NAME}', $item['name'], $entry);
                    $isAdmin         = '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>';
                    $isNoAdmin       = '<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>';
                    $isAdminTxt      = ($item['isAdmin'] == 1) ? $isAdmin : $isNoAdmin;
                    $entry           = str_replace('{TXT_ACCOUNT_ISADMIN}', $isAdminTxt, $entry);
                    $contentAccounts = $contentAccounts . $entry;
                }
                $page             = str_replace('{ACCOUNT_ENTRIES}', $contentAccounts, $page);
                $contentGalleries = NULL;
                $galleries        = $this->gallery->getAll();
                foreach ($galleries as $key => $item) {
                    $entry            = file_get_contents(DOCUMENT_ROOT . '/template/Module/Gallery/Entry.html');
                    $entry            = str_replace('{GALLERIES_EDIT_HREF}', '/' . $requestedLanguage . '/Gallery_Edit/' . $item['id'], $entry);
                    $entry            = str_replace('{GALLERIES_LINK_HREF}', '/' . $requestedLanguage . '/Gallery_Detail/' . $item['id'], $entry);
                    $entry            = str_replace('{GALLERIES_DELETE_HREF}', '/' . $requestedLanguage . '/Gallery_DoDelete/' . $item['id'], $entry);
                    $entry            = str_replace('{TXT_GALLERIES_NAME}', $item['name'], $entry);
                    $entry            = str_replace('{TXT_GALLERIES_DESCRIPTION}', $item['description'], $entry);
                    $entry            = str_replace('{TXT_GALLERIES_MODIFIED}', $item['modifiedDate'], $entry);
                    $contentGalleries = $contentGalleries . $entry;
                }
                $page = str_replace('{GALLERY_ENTRIES}', $contentGalleries, $page);
                break;
            case 'Account_Profile':
                Imagely::checkSessionRedirect($defaultSite);
                $content    = NULL;
                $user       = $this->user->getUserById($_SESSION['userId']);
                $entry      = file_get_contents(DOCUMENT_ROOT . '/template/Module/Account/Entry.html');
                $entry      = str_replace('{ACCOUNT_DELETE_HREF}', '/' . $requestedLanguage . '/Account_DoDelete/' . $user['id'], $entry);
                $entry      = str_replace('{ACCOUNT_EDIT_HREF}', '/' . $requestedLanguage . '/Account_Edit/' . $user['id'], $entry);
                $entry      = str_replace('{TXT_ACCOUNT_NAME}', $user['name'], $entry);
                $isAdmin    = '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>';
                $isNoAdmin  = '<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>';
                $isAdminTxt = $user['isAdmin'] == 1 ? $isAdmin : $isNoAdmin;
                $entry      = str_replace('{TXT_ACCOUNT_ISADMIN}', $isAdminTxt, $entry);
                $content    = $content . $entry;
                $page       = str_replace('{ACCOUNT_ENTRIES}', $content, $page);
                break;
            case 'Account_Edit':
                $page = str_ireplace('{ACCOUNT_USERID}', $requestedParameter, $page);
                break;
            case 'Account_DoLogin':
                if (isset($_POST)) {
                    $username                                       = htmlentities($_POST['Username']);
                    $password                                       = htmlentities($_POST['Password']);
                    $id                                             = $this->user->getIdByName('\'' . $username . '\'');
                    $_SESSION['SESSION_VARS']['TXT_LOGIN_USERNAME'] = $username;
                    if ($id === FALSE) {
                        $this->redirectTo($availableTemplates[3], $requestedLanguage);
                    }
                    $hash = $this->user->getHashById($id);
                    if (password_verify($password, $hash)) {
                        $request             = [];
                        $request['username'] = $username;
                        $request['password'] = $password;
                        $_POST               = [];
                        $id                  = $this->user->getIdByName('\'' . $request['username'] . '\'');
                        $_SESSION['userId']  = $id;
                        $_SESSION['hash']    = $hash;
                        unset($_SESSION['SESSION_VARS']['TXT_LOGIN_USERNAME']);
                        $this->redirectTo('Account_Profile', NULL, (int)$requestedLanguage);
                    } else {
                        $_SESSION['SESSION_VARS']['TXT_INFO'] = '<div class="row"><div class="col-xs-12"><div class="alert alert-warning">' .
                            $languageArray['TXT_IMAGELY_ACCOUNT_LOGIN_INCORRECT'] .
                            '</div></div></div>';
                        $this->redirectTo('Account_Login');
                    }
                } else {
                    $_SESSION['SESSION_VARS']['TXT_INFO'] = '<div class="row"><div class="col-xs-12"><div class="alert alert-warning">' .
                        $languageArray['TEXT_IMAGELY_GENERAL_FILL_ALL'] .
                        '</div></div></div>';


                    $this->redirectTo('Account_Login');
                }
                break;
            case 'Account_Signup':
                if (isset($_SESSION['userId'])) {
                    $this->redirectTo('Account_Profile', $_SESSION['userId']);
                }
                break;
            case 'Account_DoSignup':
                if (isset($_POST)) {
                    $id = $this->user->getIdByName('\'' . $_POST['Username'] . '\'');

                    $_SESSION['SESSION_VARS']['TXT_SIGNUP_USERNAME'] = $_POST['Username'];
                    if ($id !== FALSE && $id !== '' && $id <= 0) {
                        //todo validate
                        $request                   = [];
                        $request['username']       = $_POST['Username'];
                        $request['password']       = $_POST['Password'];
                        $request['passwordRepeat'] = $_POST['PasswordRepeat'];
                        $_POST                     = [];

                        $this->user->createUser($request);

                        $id                 = $this->user->getIdByName('\'' . $request['username'] . '\'');
                        $hash               = $this->user->getHashById($id);
                        $_SESSION['userId'] = $id;
                        $_SESSION['hash']   = $hash;
                        $this->redirectTo('Account_Profile', NULL, $id);

                    } else {
                        $_SESSION['SESSION_VARS']['TXT_INFO'] = '<div class="row"><div class="col-xs-12"><div class="alert alert-warning">' .
                            $languageArray['TXT_IMAGELY_ACCOUNT_SIGNUP_EXISTS'] .
                            '</div></div></div>';
                        $this->redirectTo('Account_Signup');

                    }
                } else {
                    $_SESSION['SESSION_VARS']['TXT_INFO'] = '<div class="row"><div class="col-xs-12"><div class="alert alert-warning">' .
                        $languageArray['TEXT_IMAGELY_GENERAL_FILL_ALL'] .
                        '</div></div></div>';

                    $this->redirectTo('Account_Signup');
                }
                break;

            case 'Gallery_Create':
                Imagely::checkSessionRedirect($defaultSite);
                break;
            case 'Gallery_Detail':
                $galleryArr = $this->gallery->getGalleryById((int)$requestedParameter);
                $images     = $this->gallery->getImageByGalleryId((int)$requestedParameter);

                if (isset($_SESSION['userId'])) {
                    $owner = $this->gallery->checkIfOwnerOrAdmin($_SESSION['userId'], $requestedParameter);
                    if (isset($owner) && $owner) {
                        $addImageCode = '<a class="btn btn-block btn-default" href="/' . $requestedLanguage . '/Image_Add/' . $requestedParameter . '"><i class="glyphicon glyphicon-cloud-upload"></i>&nbsp;{TXT_IMAGELY_GALLERY_ADD_IMAGE}</a>';
                    } else {
                        $addImageCode = '';
                    }
                } else
                    $addImageCode = '';
                $entry = '';

                $page = str_replace('{GALLERY_ADD_IMAGES}', $addImageCode, $page);
                if (empty($images)) {
                    $noImagesCode = '<div class="alert alert-info text-center"><p>{TXT_IMAGELY_GALLERY_NO_IMAGES}</p></div>';
                    $page         = str_replace('{GALLERY_NO_IMAGES}', $noImagesCode, $page);
                } else {
                    $page          = str_replace('{GALLERY_NO_IMAGES}', '', $page);
                    $entryTemplate = file_get_contents(DOCUMENT_ROOT . '/template/Module/Image/Entry.html');
                    foreach ($images as $image) {
                        $temp = $entryTemplate;
                        // $temp = str_replace()
                        $temp = str_replace('{IMAGE_URL}', $image['imagePath'], $temp);
                        $temp = str_replace('{IMAGE_THUMB_SMALL_URL}', $image['thumbnailPath1'], $temp);
                        $temp = str_replace('{IMAGE_THUMB_MEDIUM_URL}', $image['thumbnailPath2'], $temp);
                        $temp = str_replace('{IMAGE_THUMB_LARGE_URL}', $image['thumbnailPath3'], $temp);
                        if (isset($_SESSION['userId']) && $this->gallery->checkIfOwnerOrAdmin($_SESSION['userId'], (int)$requestedParameter)) {
                            $deleteLink = '<div class="caption"><h3><a class="delete" href=":link"><span class="glyphicon glyphicon-trash"></span></a></h3></div>
';
                            $deleteLink = str_replace(':link', $_SESSION['lang'] . '/Image_DoDelete/' . $image['id'] . '?galleryID=' . $requestedParameter, $deleteLink);
                            $temp       = str_replace('{GALLERIES_DELETE_LINK}', $deleteLink, $temp);
                        } else {
                            $temp = str_replace('{GALLERIES_DELETE_LINK}', '', $temp);
                        }
                        $entry .= $temp;
                    }

                    $page = str_replace('{TXT_GALLERY_IMAGES}', $entry, $page);
                }

                $page = str_replace('{TXT_GALLERY_AUTHOR}', $this->user->getNameById($galleryArr['author']), $page);
                $page = str_replace('{TXT_GALLERY_DATE}', $galleryArr['creationDate'], $page);
                $page = str_replace('{TXT_GALLERY_NAME}', $galleryArr['name'], $page);
                $page = str_replace('{TXT_GALLERY_DESCRIPTION}', $galleryArr['description'], $page);
                $page = str_replace('{GALLERY_TEASER_IMG}', $galleryArr['teaserImage'], $page);

                $avg_lum = $this->image->get_avg_luminance($galleryArr['teaserImage']);
                if ($avg_lum > 170) {
                    $page = str_replace('{TXT_CLASS_TEXT_COLOUR}', 'dark', $page);
                    $page = str_replace('{TXT_CLASS_NAV_COLOUR}', 'navbar-inverse', $page);
                }


                break;
            case 'Image_DoDelete':
                $galleryID = (isset($_GET['galleryID'])) ? $_GET['galleryID'] : NULL;
                if (isset($_SESSION['userId']) && $this->gallery->checkIfOwnerOrAdmin($_SESSION['userId'], (int)$requestedParameter)) {
                    $this->image->deleteImageById((int)$requestedParameter);
                    $_SESSION['SESSION_VARS']['TXT_INFO'] = '<div class="row"><div class="col-xs-12"><div class="alert alert-warning">' .
                        $languageArray['TXT_IMAGELY_IMAGE_DELETE_SUCCESS'] .
                        '</div></div></div>';

                    $this->redirectTo('Home', NULL, (int)$galleryID);
                } else {
                }
                break;
            case 'Gallery_DoCreate':
                Imagely::checkSessionRedirect($defaultSite);
                if ((isset($_POST['name']) && $_POST['name'] != '') &&
                    (isset($_POST['description']) && $_POST['description'] != '' && count(strip_tags($_POST['description'])) <= 500) &&
                    isset($_FILES['image'])
                ) {
                    $request['author']       = $_SESSION['userId'];
                    $request['name']         = $_POST['name'];
                    $request['status']       = TRUE;
                    $request['description']  = $_POST['description'];
                    $request['creationDate'] = time();
                    $request['modifiedDate'] = time();
                    $request['image']        = $_FILES['image'];

                    $_POST  = [];
                    $_FILES = [];

                    try {
                        $this->gallery->createGallery($request);
                    } catch (Exception $e) {
                        $_SESSION['SESSION_VARS']['TXT_GALLERY_NAME_VALUE'] = $request['name'];
                        $_SESSION['SESSION_VARS']['TXT_GALLERY_DESC_VALUE'] = $request['description'];

                        $_SESSION['SESSION_VARS']['TXT_INFO'] = '<div class="row"><div class="col-xs-12"><div class="alert alert-warning">' .
                            $languageArray['TXT_IMAGELY_GALLERY_CREATE_PICTURE'] .
                            '</div></div></div>';
                        $this->redirectTo('Create', $requestedLanguage);
                    }
                    $_SESSION['SESSION_VARS']['TXT_INFO'] = '<div class="row"><div class="col-xs-12"><div class="alert alert-info">' .
                        $languageArray['TXT_IMAGELY_GALLERY_CREATE_CREATED'] .
                        '</div></div></div>';
                    $this->redirectTo('Home', $requestedLanguage);
                } else if (count(strip_tags($_POST['description'])) > 500) {
                    $_SESSION['SESSION_VARS']['TXT_INFO'] = '<div class="row"><div class="col-xs-12"><div class="alert alert-warning">' .
                        $languageArray['TXT_IMAGELY_GALLERY_CREATE_DESCRIPTION_LENGTH'] .
                        '</div></div></div>';
                }
                $_SESSION['SESSION_VARS']['TXT_INFO']               = '<div class="row"><div class="col-xs-12"><div class="alert alert-warning">' .
                    $languageArray['TEXT_IMAGELY_GENERAL_FILL_ALL'] .
                    '</div></div></div>';
                $_SESSION['SESSION_VARS']['TXT_GALLERY_NAME_VALUE'] = $_POST['name'];
                $_SESSION['SESSION_VARS']['TXT_GALLERY_DESC_VALUE'] = $_POST['description'];
                $this->redirectTo('Create', $requestedLanguage);

                break;
            case 'Image_Add':
                $page = str_ireplace('{IMAGE_GALLERYID}', $requestedParameter, $page);
                break;
            case 'Image_DoAdd':
                if (isset($_FILES['image']['tmp_name'])) {
                    try {
                        if ($this->image->addImagesToGallery($_FILES['image']['tmp_name'], (int)$requestedParameter)) {
                            $this->redirectTo('Gallery_Detail', NULL, (int)$requestedParameter);
                        } else {
                            $_SESSION['SESSION_VARS']['TXT_INFO'] = '<div class="row"><div class="col-xs-12"><div class="alert alert-warning">' .
                                $languageArray['TXT_IMAGELY_IMAGE_ADD_NO_GALLERY_OR_NOT_OWNER'] .
                                '</div></div></div>';
                            $this->redirectTo('Image_Add', NULL, (int)$requestedParameter);
                        }
                    } catch
                    (Exception $e) {
                        $_SESSION['SESSION_VARS']['TXT_INFO'] = '<div class="row"><div class="col-xs-12"><div class="alert alert-warning">' .
                            $e->getMessage() .
                            '</div></div></div>';
                        $this->redirectTo('Image_Add', NULL, (int)$requestedParameter);

                    }
                } else {
                    $_SESSION['SESSION_VARS']['TXT_INFO'] = '<div class="row"><div class="col-xs-12"><div class="alert alert-warning">' .
                        $languageArray['TXT_IMAGELY_IMAGE_ADD_ALL_FIELDS'] .
                        '</div></div></div>';
                    $this->redirectTo('Image_Add', NULL, (int)$requestedParameter);
                }

                break;
            case 'Account_DoDelete':
                //todo: DoDeleteAccount
                Imagely::checkSessionRedirect($defaultSite);
                $this->user->checkIfOwnAccountOrRedirect($_SESSION['userId'], $requestedParameter, $defaultSite);
                $this->user->deleteUserById((int)$requestedParameter);
                unset($_SESSION['userId']);
                unset($_SESSION['hash']);
                $this->redirectTo('Home');
                break;
            case 'Account_DoEdit':
                Imagely::checkSessionRedirect($defaultSite);
                $this->user->checkIfOwnAccountOrRedirect($_SESSION['userId'], $requestedParameter, $defaultSite);
                //todo validate
                $email    = (isset($_POST['email'])) ? $_POST['email'] : NULL;
                $password = (isset($_POST['password'])) ? $_POST['password'] : NULL;

                $this->user->editUserByID((int)$requestedParameter, $email, $password);
                //todo: change all these redirects to the ones from the database
                $this->redirectTo('Account_Profile', NULL, (int)$requestedParameter);
                break;
            case 'Gallery_DoDelete':
                //todo: DoDeleteGallery
                Imagely::checkSessionRedirect($defaultSite);
                $this->gallery->checkIfOwnAccountOrRedirect($_SESSION['userId'], $requestedParameter, $defaultSite);
                $this->gallery->deleteGalleryById($requestedParameter);
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                break;
            case 'Gallery_DoEdit':
                Imagely::checkSessionRedirect($defaultSite);
                $this->gallery->checkIfOwnAccountOrRedirect($_SESSION['userId'], $requestedParameter, $defaultSite);

                if ((isset($_POST['name']) && $_POST['name'] != '') &&
                    (isset($_POST['description']) && $_POST['description'] != '' && count(strip_tags($_POST['description'])) <= 500)
                ) {
                    $request['id']           = $requestedParameter;
                    $request['author']       = $_SESSION['userId'];
                    $request['name']         = $_POST['name'];
                    $request['status']       = TRUE;
                    $request['description']  = $_POST['description'];
                    $request['modifiedDate'] = time();
                    $request['image']        = (isset($_FILES['image']) && $_FILES['image']['name'] != '') ? $_FILES['image'] : NULL;

                    $_POST  = [];
                    $_FILES = [];

                    try {
                        $this->gallery->editGallery($request);
                    } catch (Exception $e) {
                        $_SESSION['SESSION_VARS']['TXT_INFO'] = '<div class="row"><div class="col-xs-12"><div class="alert alert-warning">' .
                            $languageArray['TXT_IMAGELY_GALLERY_CREATE_PICTURE'] .
                            '</div></div></div>';
                        $this->redirectTo('Edit', $requestedLanguage, $requestedParameter);
                    }
                    $_SESSION['SESSION_VARS']['TXT_INFO'] = '<div class="row"><div class="col-xs-12"><div class="alert alert-info">' .
                        $languageArray['TXT_IMAGELY_GALLERY_CREATE_CREATED'] .
                        '</div></div></div>';
                    $this->redirectTo('Galleries');
                } else if (count(strip_tags($_POST['description'])) > 500) {
                    $_SESSION['SESSION_VARS']['TXT_INFO'] = '<div class="row"><div class="col-xs-12"><div class="alert alert-warning">' .
                        $languageArray['TXT_IMAGELY_GALLERY_CREATE_DESCRIPTION_LENGTH'] .
                        '</div></div></div>';
                }
                $_SESSION['SESSION_VARS']['TXT_INFO'] = '<div class="row"><div class="col-xs-12"><div class="alert alert-warning">' .
                    $languageArray['TEXT_IMAGELY_GENERAL_FILL_ALL'] .
                    '</div></div></div>';
                $this->redirectTo('Edit', $requestedLanguage, $requestedParameter);
                break;
            case 'Gallery_Edit':
                //todo: edit
                Imagely::checkSessionRedirect($defaultSite);
                $this->gallery->checkIfOwnAccountOrRedirect($_SESSION['userId'], (int)$requestedParameter, $defaultSite);
                $entry = $this->gallery->getGalleryById($requestedParameter);
                $page  = str_replace('{TXT_EDIT_ID}', $entry['id'], $page);
                $page  = str_replace('{TXT_EDIT_NAME}', $entry['name'], $page);
                $page  = str_replace('{TXT_EDIT_DESCRIPTION}', $entry['description'], $page);
                break;
            case 'Account_Logout':
                session_destroy();
                header($defaultSite);
                break;
            case 'Account_Galleries':
                Imagely::checkSessionRedirect($defaultSite);
                $content   = NULL;
                $galleries = $this->gallery->getAllGalleriesByUserID($_SESSION['userId']);
                foreach ($galleries as $key => $item) {
                    $entry   = file_get_contents(DOCUMENT_ROOT . '/template/Module/Gallery/Entry.html');
                    $entry   = str_replace('{GALLERIES_EDIT_HREF}', '/' . $requestedLanguage . '/Gallery_Edit/' . $item['id'], $entry);
                    $entry   = str_replace('{GALLERIES_LINK_HREF}', '/' . $requestedLanguage . '/Gallery_Detail/' . $item['id'], $entry);
                    $entry   = str_replace('{GALLERIES_DELETE_HREF}', '/' . $requestedLanguage . '/Gallery_DoDelete/' . $item['id'], $entry);
                    $entry   = str_replace('{TXT_GALLERIES_NAME}', $item['name'], $entry);
                    $entry   = str_replace('{TXT_GALLERIES_DESCRIPTION}', $item['description'], $entry);
                    $entry   = str_replace('{TXT_GALLERIES_MODIFIED}', $item['modifiedDate'], $entry);
                    $content = $content . $entry;
                }
                $page = str_replace('{GALLERY_ENTRIES}', $content, $page);
                break;
            default:

        }
        $page = str_replace('{DEFAULT_SITE}', PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . PATH_OFFSET . '/' . $requestedLanguage . '/' . $availableTemplates[0], $page);
        $page = str_replace('{NAVIGATION}', $this->template->getNavigation(), $page);
        $page = str_replace('{PATH_OFFSET}', PATH_OFFSET, $page);
        $page = str_replace('{LANGUAGE}', $requestedLanguage, $page);

        foreach ($_SESSION['SESSION_VARS'] as $key => $item) {
            $page = str_replace('{' . $key . '}', $item, $page);
            unset($_SESSION['SESSION_VARS'][ $key ]);
        }
        foreach ($languageArray as $key => $item) {
            $page = str_replace('{' . $key . '}', $item, $page);
        }

        $page = preg_replace('/{TXT_[A-Z_]+}/', '', $page);

        echo $page;
    }

    /**
     * Check if user is logged in and returns true otherwise it redirects the client to the redirect page
     *
     * @param string $redirect - Page to redirect to
     *
     * @return bool - True if user is logged in
     */
    function checkSessionRedirect($redirect)
    {
        if (isset($_SESSION['userId']) && isset($_SESSION['hash'])) {
            $correctHash = $this->user->getHashById($_SESSION['userId']);
            if ($correctHash === $_SESSION['hash'] || $this->user->isAdmin($_SESSION['userId']) === TRUE) {
                return TRUE;
            }
        }
        header($redirect);

        return FALSE;
    }

    /**
     * Checks if the logged in user is an admin, and if yes it returns true otherwise it redirects the client to the
     * redirect page
     *
     * @param string $redirect - Page to redirect to
     *
     * @return bool - Returns true if logged in
     */
    public
    function checkAdminRedirect(string $redirect)
    {
        if (isset($_SESSION['userId'])) {
            if ($this->user->isAdmin($_SESSION['userId']) === TRUE) {
                return TRUE;
            }
        }
        header($redirect);

        return FALSE;
    }

    /**
     * Redirect to a certain site
     *
     * @param string      $site               - Site you want to redirect to
     * @param string|null $lang               - Language as string
     * @param string|int  $requestedParameter - Additional Parameter
     */
    public
    function redirectTo(string $site, string $lang = NULL, int $requestedParameter = NULL)
    {
        $availableTemplates = $this->template->pagesArr;
        $availableLanguages = $this->language->getAvailableLanguages();
        $requestedParameter = trim(($requestedParameter !== NULL) ? $requestedParameter : '');
        $site               = trim(($site != NULL && in_array($site, $availableTemplates, TRUE)) ? $site : 'Home');
        $lang               = trim(($lang != NULL && in_array($lang, $availableLanguages, TRUE)) ? $lang : $_SESSION['lang']);

        $location = 'Location: ' . PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . PATH_OFFSET . '/:lang/:site/:requestParameter';
        $needle   = [
            ':lang',
            ':site',
            ':requestParameter',
        ];
        $replace  = [
            $lang,
            $site,
            $requestedParameter,
        ];

        $location = str_replace($needle, $replace, $location);

        header($location);
        die();
    }

}
