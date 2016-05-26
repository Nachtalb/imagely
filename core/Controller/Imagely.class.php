<?php

/**
 * @copyright   Nick Espig <nicku8.com>
 * @author      Nick Espig <info@nicku8.com>
 * @package     Imagely
 * @version     1.0
 * @subpackage  core
 */
class Imagely
{

    private $availableActions = [
        'DoLogin',
        'DoSignup',
        'DoDeleteAccount',
        'DoEditAccount',
        'DoCreateGallery',
        'DoDeleteGallery',
        'DoEditGallery',

    ];
    private $language;
    private $template;
    private $gallery;
    private $image;
    private $user;

    /**
     * Imagely constructor.
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
     * checks if user exists in session, to make sure that the user is logged in
     *
     * @return bool : if he's logged in
     */
    function checkSession()
    {
        //Check if username exists in session, to make sure that user is logged in
        if (isset($_SESSION['userId']) && isset($_SESSION['hash'])) {
            //Get hash from database and check if hash and id in session match, to make sure user is correctly logged in
            $correctHash = $this->user->getHashById($_SESSION['userId']);
            if ($correctHash === $_SESSION['hash'] || $this->user->isAdmin($_SESSION['userId']) === TRUE) {
                return TRUE;
            }

            return FALSE;
        }

        return FALSE;
    }

    /**
     * controls the whole process from get link to show the site
     */
    function getPage()
    {
        //Create instances

        //getAvailableLanguages
        $availableLanguages = $this->language->getAvailableLanguages();

        //getAvailableTemplates
        $availableTemplates = $this->template->getAvailableTemplates();

        //Get requestedLanguage & requestedTemplate
        $urlParts = explode('/', $_GET['__cap']);

        //Set requestedLanguage
        if (!isset($urlParts[1]) || $urlParts[1] === 'index.php' || $urlParts[1] === '' || !in_array($urlParts[1], $availableLanguages, TRUE))
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
            if (in_array($requestedTemplate, $this->availableActions, TRUE)) {
                $availableUsers = $this->user->getAvailableUsersId();

                switch ($requestedTemplate) {
                    case 'DoLogin':
                    case 'DoSignUp':
                        break;
                    case 'DoDeleteAccount':
                        if (in_array((int)$urlParts[2], $availableUsers, TRUE)) {
                            $requestedParameter = $urlParts[3];
                            $redirect           = ($this->user->isAdmin($_SESSION['userId'])) ? $availableTemplates[5] : $availableTemplates[13];

                        } else {
                            header($defaultSite);
                        }
                        break;
                    case 'DoEditAccount':
                        if (in_array((int)$urlParts[2], $availableUsers, TRUE)) {
                            $requestedParameter = $urlParts[3];
                            $redirect           = ($this->user->isAdmin($_SESSION['userId'])) ? $availableTemplates[5] : $availableTemplates[13];

                        } else {
                            header($defaultSite);
                        }
                        break;
                }
            } else if (in_array($requestedTemplate, $availableTemplates, TRUE)) {
                $requestedParameter = $urlParts[3];
            }
        } else {
            header($defaultSite);
        }

        //getTemplate by requestedTemplate
        $page = (isset($redirect)) ? $this->template->getTemplate($redirect) : $this->template->getTemplate($requestedTemplate);

        switch ($requestedTemplate) {
            case 'Home':
                $content   = NULL;
                $galleries = $this->gallery->getAll();
                foreach ($galleries as $key => $value) {
                    $entry   = file_get_contents(DOCUMENT_ROOT . '/template/home_entry.html');
                    $entry   = str_replace('{GALLERY_LINK_HREF}', 'Detail/' . $value['id'], $entry);
                    $entry   = str_replace('{TXT_GALLERY_NAME}', $value['name'], $entry);
                    $entry   = str_replace('{TXT_GALLERY_DESCRIPTION}', $value['description'], $entry);
                    $entry   = str_replace('{TXT_GALLERY_AUTHOR}', $this->gallery->getAuthorNameById($value['author']), $entry);
                    $entry   = str_replace('{TXT_GALLERY_DATE}', $value['creationDate'], $entry);
                    $content = $content . $entry;
                }
                $page = str_replace('{GALLERY_ENTRIES}', $content, $page);
                break;

            case 'Admin':
                Imagely::checkSessionRedirect($defaultSite);
                Imagely::checkAdminRedirect($defaultSite);
                $contentAccounts = NULL;
                $users           = $this->user->getAll();
                foreach ($users as $key => $value) {
                    $entry           = file_get_contents(DOCUMENT_ROOT . '/template/account_entry.html');
                    $entry           = str_replace('{ACCOUNT_DELETE_HREF}', 'DoDeleteAccount/' . $value['id'], $entry);
                    $entry           = str_replace('{ACCOUNT_EDIT_HREF}', 'EditAccount/' . $value['id'], $entry);
                    $entry           = str_replace('{TXT_ACCOUNT_NAME}', $value['name'], $entry);
                    $isAdminTxt      = $value['isAdmin'] == 1 ? $languageArray['TXT_IMAGELY_ACCOUNT_ISADMIN_TRUE'] : $languageArray['TXT_IMAGELY_ACCOUNT_ISADMIN_FALSE'];
                    $entry           = str_replace('{TXT_ACCOUNT_ISADMIN}', $isAdminTxt, $entry);
                    $contentAccounts = $contentAccounts . $entry;
                }
                $page             = str_replace('{ACCOUNT_ENTRIES}', $contentAccounts, $page);
                $contentGalleries = NULL;
                $galleries        = $this->gallery->getAll();
                foreach ($galleries as $key => $value) {
                    $entry            = file_get_contents(DOCUMENT_ROOT . '/template/gallery_entry.html');
                    $entry            = str_replace('{GALLERIES_EDIT_HREF}', 'Edit/' . $value['id'], $entry);
                    $entry            = str_replace('{GALLERIES_LINK_HREF}', 'Detail/' . $value['id'], $entry);
                    $entry            = str_replace('{GALLERIES_DELETE_HREF}', 'DoDeleteGallery/' . $value['id'], $entry);
                    $entry            = str_replace('{TXT_GALLERIES_NAME}', $value['name'], $entry);
                    $entry            = str_replace('{TXT_GALLERIES_DESCRIPTION}', $value['description'], $entry);
                    $entry            = str_replace('{TXT_GALLERIES_MODIFIED}', $value['modifiedDate'], $entry);
                    $contentGalleries = $contentGalleries . $entry;
                }
                $page = str_replace('{GALLERY_ENTRIES}', $contentGalleries, $page);
                break;
            case 'Account':
                Imagely::checkSessionRedirect($defaultSite);
                $content    = NULL;
                $user       = $this->user->getUserById($_SESSION['userId']);
                $entry      = file_get_contents(DOCUMENT_ROOT . '/template/account_entry.html');
                $entry      = str_replace('{ACCOUNT_DELETE_HREF}', 'DoDeleteAccount/' . $user['id'], $entry);
                $entry      = str_replace('{ACCOUNT_EDIT_HREF}', 'EditAccount/' . $user['id'], $entry);
                $entry      = str_replace('{TXT_ACCOUNT_NAME}', $user['name'], $entry);
                $isAdminTxt = $user['isAdmin'] == 1 ? $languageArray['TXT_IMAGELY_ACCOUNT_ISADMIN_TRUE'] : $languageArray['TXT_IMAGELY_ACCOUNT_ISADMIN_FALSE'];
                $entry      = str_replace('{TXT_ACCOUNT_ISADMIN}', $isAdminTxt, $entry);
                $content    = $content . $entry;
                $page       = str_replace('{ACCOUNT_ENTRIES}', $content, $page);
                break;
            case 'EditAccount':
                //todo: edit account page
                break;
            case 'DoLogin':
                if (isset($_POST)) {
                    $id                                             = $this->user->getIdByName('\'' . $_POST['Username'] . '\'');
                    $_SESSION['SESSION_VARS']['TXT_LOGIN_USERNAME'] = $_POST['Username'];
                    if ($id === FALSE)
                        $this->redirectTo($availableTemplates[3], $requestedLanguage);
                    $hash = $this->user->getHashById($id);
                    if (password_verify($_POST['Password'], $hash)) {
                        $request             = [];
                        $request['username'] = $_POST['Username'];
                        $request['password'] = $_POST['Password'];
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

                $page = str_replace('{TXT_GALLERY_AUTHOR}', $this->gallery->getAuthorNameById($galleryArr['author']), $page);
                $page = str_replace('{TXT_GALLERY_DATE}', $galleryArr['creationDate'], $page);
                $page = str_replace('{TXT_GALLERY_NAME}', $galleryArr['name'], $page);
                $page = str_replace('{TXT_GALLERY_DESCRIPTION}', $galleryArr['description'], $page);
                break;
            case 'DoCreateGallery':
                Imagely::checkSessionRedirect($defaultSite);
                if (isset($_POST)) {
                    $request                 = [];
                    $request['author']       = $_SESSION['userId'];
                    $request['name']         = $_POST['name'];
                    $request['description']  = $_POST['description'];
                    $request['creationDate'] = date('Y-m-d h:i:s');
                    $request['modifiedDate'] = date('Y-m-d h:i:s');
                    $_POST                   = [];

                    $this->gallery->createGallery($request);

                    header('Location: ' . PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . PATH_OFFSET . '/' . $requestedLanguage . '/' . $availableTemplates[1]);
                }
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
                        $randomString = $this->image->generateRandomString();
                    } while (in_array($randomString, $existingFiles, FALSE));

                    $targetFile = DOCUMENT_ROOT . $storeFolder . $randomString . '.' . $fileExt;
                    move_uploaded_file($tempFile, $targetFile);

                    //create thumbnail
                    $this->image->createThumbnail($targetFile);

                    $request['imagePath']     = $storeFolder . $randomString . '.' . $fileExt;
                    $request['thumbnailPath'] = $storeFolder . $randomString . '.' . $fileExt . '.thumbnail';
                }
                $this->image->createImage($request);
                header('Location: ' . PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . PATH_OFFSET . '/' . $requestedLanguage . '/' . $availableTemplates[15] . '/' . $requestedParameter);
                break;
            case 'DoDeleteAccount':
                Imagely::checkSessionRedirect($defaultSite);
                $this->user->checkIfOwnAccountRedirect($_SESSION['userId'], $requestedParameter, $defaultSite);
                $this->user->deleteUserById($requestedParameter);
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                break;
            case 'DoEditAccount':
                //todo: make this work
                Imagely::checkSessionRedirect($defaultSite);
                $this->user->checkIfOwnAccountRedirect($_SESSION['userId'], $requestedParameter, $defaultSite);
                $this->user->deleteUserById($requestedParameter);
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                break;
            case 'DoDeleteGallery':
                Imagely::checkSessionRedirect($defaultSite);
                $this->gallery->checkIfOwnGalleryRedirect($_SESSION['userId'], $requestedParameter, $defaultSite);
                $this->gallery->deleteGalleryById($requestedParameter);
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                break;
            case 'DoEditGallery':
                Imagely::checkSessionRedirect($defaultSite);
                $this->gallery->checkIfOwnGalleryRedirect($_SESSION['userId'], $requestedParameter, $defaultSite);
                if (isset($_POST)) {
                    $request                 = [];
                    $request['id']           = $requestedParameter;
                    $request['name']         = $_POST['name'];
                    $request['description']  = $_POST['description'];
                    $request['modifiedDate'] = date('Y-m-d h:i:s');
                    $_POST                   = [];

                    $this->gallery->editGallery($request);

                    header('Location: ' . PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . PATH_OFFSET . '/' . $requestedLanguage . '/' . $availableTemplates[12]);
                }
                break;
            case 'Edit':
                Imagely::checkSessionRedirect($defaultSite);
                $this->gallery->checkIfOwnGalleryRedirect($_SESSION['userId'], $requestedParameter, $defaultSite);
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
                foreach ($galleries as $key => $value) {
                    $entry   = file_get_contents(DOCUMENT_ROOT . '/template/gallery_entry.html');
                    $entry   = str_replace('{GALLERIES_EDIT_HREF}', 'Edit/' . $value['id'], $entry);
                    $entry   = str_replace('{GALLERIES_LINK_HREF}', 'Detail/' . $value['id'], $entry);
                    $entry   = str_replace('{GALLERIES_DELETE_HREF}', 'DoDeleteGallery/' . $value['id'], $entry);
                    $entry   = str_replace('{TXT_GALLERIES_NAME}', $value['name'], $entry);
                    $entry   = str_replace('{TXT_GALLERIES_DESCRIPTION}', $value['description'], $entry);
                    $entry   = str_replace('{TXT_GALLERIES_MODIFIED}', $value['modifiedDate'], $entry);
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

        foreach ($_SESSION['SESSION_VARS'] as $key => $value) {
            $page = str_replace('{' . $key . '}', $value, $page);
        }

        //Replace placeholder through requestedLanguage
        foreach ($languageArray as $key => $value) {
            $page = str_replace('{' . $key . '}', $value, $page);
        }

        $page = preg_replace('/{TXT_[A-Z_]+}/', '', $page);

        //return page
        echo $page;

    }

    /**
     * check if user is logged in and returns true otherwise it redirects the client to the redirect page
     *
     * @param string $redirect : page to redirect to
     *
     * @return bool : true if user is logged in
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
     * checks if the logged in user is an admin, and if yes it returns true otherwise it redirects the client to the redirect page
     *
     * @param string $redirect : page to redirect to
     *
     * @return bool : returns true if logged in
     */
    function checkAdminRedirect($redirect)
    {
        //Check if username exists in session, to make sure that user is logged in
        if (isset($_SESSION['userId'])) {
            //Get hash from database and check if hash and id in session match, to make sure user is correctly logged in
            if ($this->user->isAdmin($_SESSION['userId']) === TRUE) {
                return TRUE;
            }
        }
        header($redirect);

        return FALSE;
    }

    /**
     * redirect to a certain site
     *
     * @param string $site : site you want to redirect to
     * @param string $lang : language as string
     */
    function redirectTo($site, $lang)
    {
        $availableTemplates = $this->template->getAvailableTemplates();
        $availableLanguages = $this->template->getAvailableTemplates();
        if (isset($lang) && $lang != NULL && in_array($lang, $availableLanguages, TRUE)) {
            if (in_array($site, $availableTemplates, TRUE))
                $redirect = 'Location: ' . PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . PATH_OFFSET . '/' . $lang . '/' . $site;
            else
                $redirect = 'Location: ' . PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . PATH_OFFSET . '/' . $lang . '/' . $availableTemplates[0];
        } else {
            $browserLanguage = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            if (in_array($browserLanguage, $availableLanguages, TRUE)) {
                if (in_array($site, $availableTemplates, TRUE))
                    $redirect = 'Location: ' . PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . PATH_OFFSET . '/' . $browserLanguage . '/' . $site;
                else
                    $redirect = 'Location: ' . PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . PATH_OFFSET . '/' . $browserLanguage . '/' . $availableTemplates[0];
            } else {
                if (in_array($site, $availableTemplates, TRUE))
                    $redirect = 'Location: ' . PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . PATH_OFFSET . '/' . $GLOBALS['DEFAULTS']['LANG'] . '/' . $site;
                else
                    $redirect = 'Location: ' . PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . PATH_OFFSET . '/' . $GLOBALS['DEFAULTS']['LANG'] . '/' . $availableTemplates[0];
            }

        }

        header($redirect);
        die();
    }

}
