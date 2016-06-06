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
     * ArrayList with all possible actions
     *
     * @var array
     */
    private $availableActions = [
        'DoLogin',
        'DoSignup',
        'DoDeleteAccount',
        'DoEditAccount',
        'DoCreateGallery',
        'DoDeleteGallery',
        'DoEditGallery',

    ];
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
        //Create instances

        //getAvailableLanguages
        $availableLanguages = $this->language->getAvailableLanguages();

        //getAvailableTemplates
        $availableTemplates = $this->template->getAvailableSites();

        //Get requestedLanguage & requestedTemplate
        $urlParts = explode('/', $_GET['__cap']);

        //Set requestedLanguage
        if (!isset($urlParts[1]) || $urlParts[1] == 'index.php' || $urlParts[1] == '' || $urlParts[2] == '' || !in_array($urlParts[1], $availableLanguages, TRUE))
            $this->redirectTo($availableTemplates[0], NULL);
        else
            $requestedLanguage = $urlParts[1];

        //Set default site
        $defaultSite = 'Location: ' . PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . PATH_OFFSET . '/' . $requestedLanguage . '/' . $availableTemplates[0];

        //getLanguageArray by requestedLanguage
        $languageArray = $this->language->getLanguageArray($requestedLanguage);
        //Set requestedTemplate
        if (isset($urlParts[2])) {
            $requestedTemplate = $urlParts[2];
            if (in_array($requestedTemplate, $availableTemplates, TRUE)) {
                $requestedParameter = $urlParts[3];
            }
        } else {
            header($defaultSite);
        }

        //getTemplate by requestedTemplate
        $page = $this->template->getTemplate($requestedTemplate);

        switch ($requestedTemplate) {
            case 'Home':
                $content   = NULL;
                $galleries = $this->gallery->getAll();
                foreach ($galleries as $item) {
                    $entry   = file_get_contents(DOCUMENT_ROOT . '/template/home_entry.html');
                    $entry   = str_replace('{GALLERY_LINK_HREF}', '/' . $requestedLanguage . '/Detail/' . $item['id'], $entry);
                    $entry   = str_replace('{TXT_GALLERY_NAME}', $item['name'], $entry);
                    $entry   = str_replace('{TXT_GALLERY_DESCRIPTION}', $item['description'], $entry);
                    $entry   = str_replace('{TXT_GALLERY_AUTHOR}', $this->user->getNameById($item['author']), $entry);
                    $entry   = str_replace('{TXT_GALLERY_DATE}', $item['creationDate'], $entry);
                    $content = $content . $entry;
                }
                $page = str_replace('{GALLERY_ENTRIES}', $content, $page);
                break;

            case 'Admin':
                Imagely::checkSessionRedirect($defaultSite);
                Imagely::checkAdminRedirect($defaultSite);
                $contentAccounts = NULL;
                $users           = $this->user->getAll();
                foreach ($users as $key => $item) {
                    $entry           = file_get_contents(DOCUMENT_ROOT . '/template/account_entry.html');
                    $entry           = str_replace('{ACCOUNT_DELETE_HREF}', '/' . $requestedLanguage . '/DoDeleteAccount/' . $item['id'], $entry);
                    $entry           = str_replace('{ACCOUNT_EDIT_HREF}', '/' . $requestedLanguage . '/EditAccount/' . $item['id'], $entry);
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
                    $entry            = file_get_contents(DOCUMENT_ROOT . '/template/gallery_entry.html');
                    $entry            = str_replace('{GALLERIES_EDIT_HREF}', '/' . $requestedLanguage . '/Edit/' . $item['id'], $entry);
                    $entry            = str_replace('{GALLERIES_LINK_HREF}', '/' . $requestedLanguage . '/Detail/' . $item['id'], $entry);
                    $entry            = str_replace('{GALLERIES_DELETE_HREF}', '/' . $requestedLanguage . '/DoDeleteGallery/' . $item['id'], $entry);
                    $entry            = str_replace('{TXT_GALLERIES_NAME}', $item['name'], $entry);
                    $entry            = str_replace('{TXT_GALLERIES_DESCRIPTION}', $item['description'], $entry);
                    $entry            = str_replace('{TXT_GALLERIES_MODIFIED}', $item['modifiedDate'], $entry);
                    $contentGalleries = $contentGalleries . $entry;
                }
                $page = str_replace('{GALLERY_ENTRIES}', $contentGalleries, $page);
                break;
            case
            'Account':
                Imagely::checkSessionRedirect($defaultSite);
                $content    = NULL;
                $user       = $this->user->getUserById($_SESSION['userId']);
                $entry      = file_get_contents(DOCUMENT_ROOT . '/template/account_entry.html');
                $entry      = str_replace('{ACCOUNT_DELETE_HREF}', '/' . $requestedLanguage . '/DoDeleteAccount/' . $user['id'], $entry);
                $entry      = str_replace('{ACCOUNT_EDIT_HREF}', '/' . $requestedLanguage . '/EditAccount/' . $user['id'], $entry);
                $entry      = str_replace('{TXT_ACCOUNT_NAME}', $user['name'], $entry);
                $isAdmin    = '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>';
                $isNoAdmin  = '<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>';
                $isAdminTxt = $user['isAdmin'] == 1 ? $isAdmin : $isNoAdmin;
                $entry      = str_replace('{TXT_ACCOUNT_ISADMIN}', $isAdminTxt, $entry);
                $content    = $content . $entry;
                $page       = str_replace('{ACCOUNT_ENTRIES}', $content, $page);
                break;
            case 'EditAccount':
                //todo: edit account page
                break;
            case 'DoLogin':
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
                        $this->redirectTo($availableTemplates[12], $requestedLanguage);
                    } else {
                        $this->redirectTo($availableTemplates[3], $requestedLanguage);
                    }
                } else {
                    $this->redirectTo($availableTemplates[3], $requestedLanguage);
                }
                break;
            case 'DoSignup':
                if (isset($_POST)) {
                    $id = $this->user->getIdByName('\'' . $_POST['Username'] . '\'');
                    // Dog_12345
                    $_SESSION['SESSION_VARS']['TXT_SIGNUP_USERNAME'] = $_POST['Username'];
                    if ($id === FALSE || $id === '') {
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
                        header('Location: ' . PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . PATH_OFFSET . '/' . $requestedLanguage . '/' . $availableTemplates[5]);
                    } else {
                        header('Location: ' . PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . PATH_OFFSET . '/' . $requestedLanguage . '/' . $availableTemplates[4]);
                    }
                } else {
                    header('Location: ' . PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . PATH_OFFSET . '/' . $requestedLanguage . '/' . $availableTemplates[3]);
                }
                break;

            case 'Create':
                Imagely::checkSessionRedirect($defaultSite);
                break;
            case 'Detail':
                $galleryArr = $this->gallery->getGalleryById($requestedParameter);
                $images     = $this->gallery->getImageByGalleryId($requestedParameter);

                $page = str_replace('{TXT_GALLERY_AUTHOR}', $this->user->getNameById($galleryArr['author']), $page);
                $page = str_replace('{TXT_GALLERY_DATE}', $galleryArr['creationDate'], $page);
                $page = str_replace('{TXT_GALLERY_NAME}', $galleryArr['name'], $page);
                $page = str_replace('{TXT_GALLERY_DESCRIPTION}', $galleryArr['description'], $page);
                $page = str_replace('{GALLERY_TEASER_IMG}', $galleryArr['teaserImage'], $page);

                $avg_lum = $this->gallery->get_avg_luminance($galleryArr['teaserImage']);
                if ($avg_lum > 170) {
                    $page = str_replace('{TXT_CLASS_TEXT_COLOUR}', 'dark', $page);
                    $page = str_replace('{TXT_CLASS_NAV_COLOUR}', 'navbar-inverse', $page);
                }


                break;
            case 'DoCreateGallery':
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
                            $languageArray['TXT_IMAGELY_CREATE_PICTURE'] .
                            '</div></div></div>';
                        $this->redirectTo('Create', $requestedLanguage);
                    }
                    $_SESSION['SESSION_VARS']['TXT_INFO'] = '<div class="row"><div class="col-xs-12"><div class="alert alert-info">' .
                        $languageArray['TXT_IMAGELY_CREATE_CREATED'] .
                        '</div></div></div>';
                    $this->redirectTo('Home', $requestedLanguage);
                } else if (count(strip_tags($_POST['description'])) > 500) {
                    $_SESSION['SESSION_VARS']['TXT_INFO'] = '<div class="row"><div class="col-xs-12"><div class="alert alert-warning">' .
                        $languageArray['TXT_IMAGELY_CREATE_DESCRIPTION_LENGTH'] .
                        '</div></div></div>';
                }
                $_SESSION['SESSION_VARS']['TXT_INFO']               = '<div class="row"><div class="col-xs-12"><div class="alert alert-warning">' .
                    $languageArray['TXT_IMAGELY_CREATE_ALL_FIELDS'] .
                    '</div></div></div>';
                $_SESSION['SESSION_VARS']['TXT_GALLERY_NAME_VALUE'] = $_POST['name'];
                $_SESSION['SESSION_VARS']['TXT_GALLERY_DESC_VALUE'] = $_POST['description'];
                $this->redirectTo('Create', $requestedLanguage);

                break;
            case 'DoCreateImage':
                $request              = [];
                $request['galleryId'] = $requestedParameter;
                if (isset($_FILES)) {
                    $file     = $_FILES['image']['name'];
                    $fileExt  = pathinfo($file, PATHINFO_EXTENSION);
                    $tempFile = $_FILES['image']['tmp_name'];


                    $storeFolder   = '/data/media/gallery/';
                    $existingFiles = scandir($storeFolder, 1);
                    do {
                        $randomString = uniqid();
                    } while (in_array($randomString, $existingFiles, FALSE));

                    $targetFile = DOCUMENT_ROOT . $storeFolder . $randomString . '.' . $fileExt;
                    move_uploaded_file($tempFile, $targetFile);

                    //create thumbnail
                    $this->image->createThumbnail($targetFile);

                    $request['imagePath']     = $storeFolder . $randomString . '.' . $fileExt;
                    $request['thumbnailPath'] = $storeFolder . $randomString . '.' . $fileExt . '.thumbnail';
                    $this->image->referImageInDB($request);
                }
                header('Location: ' . PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . PATH_OFFSET . '/' . $requestedLanguage . '/' . $availableTemplates[15] . '/' . $requestedParameter);
                break;
            case 'DoDeleteAccount':
                Imagely::checkSessionRedirect($defaultSite);
                $this->user->checkIfOwnAccountOrRedirect($_SESSION['userId'], $requestedParameter, $defaultSite);
                $this->user->deleteUserById($requestedParameter);
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                break;
            case 'DoEditAccount':
                //todo: make this work
                Imagely::checkSessionRedirect($defaultSite);
                $this->user->checkIfOwnAccountOrRedirect($_SESSION['userId'], $requestedParameter, $defaultSite);
                $this->user->deleteUserById($requestedParameter);
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                break;
            case 'DoDeleteGallery':
                Imagely::checkSessionRedirect($defaultSite);
                $this->gallery->checkIfOwnAccountOrRedirect($_SESSION['userId'], $requestedParameter, $defaultSite);
                $this->gallery->deleteGalleryById($requestedParameter);
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                break;
            case 'DoEditGallery':
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
                            $languageArray['TXT_IMAGELY_CREATE_PICTURE'] .
                            '</div></div></div>';
                        $this->redirectTo('Edit', $requestedLanguage, $requestedParameter);
                    }
                    $_SESSION['SESSION_VARS']['TXT_INFO'] = '<div class="row"><div class="col-xs-12"><div class="alert alert-info">' .
                        $languageArray['TXT_IMAGELY_CREATE_CREATED'] .
                        '</div></div></div>';
                    $this->redirectTo('Galleries');
                } else if (count(strip_tags($_POST['description'])) > 500) {
                    $_SESSION['SESSION_VARS']['TXT_INFO'] = '<div class="row"><div class="col-xs-12"><div class="alert alert-warning">' .
                        $languageArray['TXT_IMAGELY_CREATE_DESCRIPTION_LENGTH'] .
                        '</div></div></div>';
                }
                $_SESSION['SESSION_VARS']['TXT_INFO'] = '<div class="row"><div class="col-xs-12"><div class="alert alert-warning">' .
                    $languageArray['TXT_IMAGELY_CREATE_ALL_FIELDS'] .
                    '</div></div></div>';
                $this->redirectTo('Edit', $requestedLanguage, $requestedParameter);
                break;
            case 'Edit':
                Imagely::checkSessionRedirect($defaultSite);
                $this->gallery->checkIfOwnAccountOrRedirect($_SESSION['userId'], $requestedParameter, $defaultSite);
                $entry = $this->gallery->getGalleryById($requestedParameter);
                $page  = str_replace('{TXT_EDIT_ID}', $entry['id'], $page);
                $page  = str_replace('{TXT_EDIT_NAME}', $entry['name'], $page);
                $page  = str_replace('{TXT_EDIT_DESCRIPTION}', $entry['description'], $page);
                break;
            case 'Logout':
                session_destroy();
                header($defaultSite);
                break;
            case 'Galleries':
                Imagely::checkSessionRedirect($defaultSite);
                $content   = NULL;
                $galleries = $this->gallery->getAllByAuthor($_SESSION['userId']);
                foreach ($galleries as $key => $item) {
                    $entry   = file_get_contents(DOCUMENT_ROOT . '/template/gallery_entry.html');
                    $entry   = str_replace('{GALLERIES_EDIT_HREF}', '/' . $requestedLanguage . '/Edit/' . $item['id'], $entry);
                    $entry   = str_replace('{GALLERIES_LINK_HREF}', '/' . $requestedLanguage . '/Detail/' . $item['id'], $entry);
                    $entry   = str_replace('{GALLERIES_DELETE_HREF}', '/' . $requestedLanguage . '//DoDeleteGallery/' . $item['id'], $entry);
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

        //Replace placeholder through requestedLanguage
        foreach ($languageArray as $key => $item) {
            $page = str_replace('{' . $key . '}', $item, $page);
        }

        $page = preg_replace('/{TXT_[A-Z_]+}/', '', $page);

        //return page
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
        //Check if username exists in session, to make sure that user is logged in
        if (isset($_SESSION['userId']) && isset($_SESSION['hash'])) {
            //Get hash from database and check if hash and id in session match, to make sure user is correctly logged in
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
    public function checkAdminRedirect(string $redirect)
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
    function redirectTo(string $site, string $lang = NULL, int $requestedParameter = NULL)
    {
        $availableTemplates = $this->template->getAvailableSites();
        $availableLanguages = $this->language->getAvailableLanguages();
        $requestedParameter = trim(($requestedParameter != NULL) ? $requestedParameter : '');
        $site               = trim(($site != NULL && in_array($site, $availableTemplates, TRUE)) ? $site : 'home');
        $lang               = trim(($lang != NULL && in_array($lang, $availableLanguages, TRUE)) ? $lang : $GLOBALS['CONFIG']['LANG']);

        $location = 'Location: ' . PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . PATH_OFFSET . '/:lang/:site/:requestParameter';
        $needle   = [
            ':lang',
            ':site',
            ':requestParameter'
        ];
        $replace  = [
            $lang,
            $site,
            $requestedParameter
        ];

        $location = str_replace($needle, $replace, $location);

        header($location);
        die();
    }

}
