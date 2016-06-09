<?php

/**
 * @copyright   Nick Espig <nickespig.xyz>
 * @author      Nick Espig <info@nickespig.xyz>
 * @package     imagely
 * @version     1.0
 * @subpackage  core
 */
class Template
{
    /**
     * @var array - Array with all pages
     */
    public $fullPagesArr = [];
    /**
     * @var array - Array with only the page names
     */
    public $pagesArr = [];

    /**
     * Template constructor.
     */
    public function __construct()
    {
        $this->fullPagesArr = $this->getAvailableSites();

        foreach ($this->fullPagesArr as $page) {
            array_push($this->pagesArr, $page['name']);
        }
    }


    /**
     * Get all available sites/templates
     *
     * @return array - Array with all available sites
     */
    private function getAvailableSites()
    {
        $sth = $GLOBALS['db']->prepare('SELECT * FROM `pages`;');
        $sth->execute();

        $result = $sth->fetchAll();
        $return = json_decode(json_encode($result), TRUE);

        return $return;
    }

    /**
     * Gives you the array with all the information about the requested site.
     *
     * @param string $pageName - Name of the page you want to get
     *
     * @return bool|array - Array of the requested page or false
     */
    public function getPageByName(string $pageName)
    {
        foreach ($this->fullPagesArr as $item) {
            if ($item['name'] === $pageName)
                return $item;
        }

        return FALSE;
    }

    /**
     * Gives you the array with all the information about the requested site.
     *
     * @param string $ID - The ID of the requested Page.
     *
     * @return bool|array - Array of the requested page or false
     */
    public function getPageByID(string $ID)
    {
        foreach ($this->fullPagesArr as $item) {
            if ($item['id'] === $ID)
                return $item;
        }

        return FALSE;
    }

    /**
     * Gives you the answer to the question if a page needs additional parameters
     *
     * @param string|NULL $pageName - Name of the page, if null you need to ad page id
     * @param int|NULL    $pageID   - ID of tha page
     *
     * @return bool
     */
    public function needsParams(string $pageName = NULL, int $pageID = NULL)
    {
        if ($pageName !== FALSE)
            $page = $this->getPageByName($pageName);
        else
            $page = $this->getPageByID($pageID);

        if ($page !== FALSE)
            return ($page['needsParam'] == 1);
        else
            return FALSE;
    }

    /**
     * Generates the HTML of the whole site
     *
     * @param string $pageName - Name of the site you want to get
     *
     * @return string - HTML string
     */
    public function getTemplate(string $pageName)
    {
        $template     = file_get_contents(DOCUMENT_ROOT . '/template/Index.html');
        $templatePath = DOCUMENT_ROOT . '/template/Module/';
        $path         = NULL;

        foreach ($this->fullPagesArr as $page) {
            if ($page['name'] == $pageName) {
                $fileAssociation = $page['fileAssociation'];
                if ($fileAssociation !== NULL && $fileAssociation !== '') {
                    $modul = explode('_', $page['name'])[0];
                    $path  = $templatePath . $modul . DIRECTORY_SEPARATOR . $fileAssociation;
                } else {
                    $newTemplate     = $this->fullPagesArr[ $page['redirect'] - 1 ];
                    $fileAssociation = $newTemplate['fileAssociation'];
                    $modul           = explode('_', $newTemplate['name'])[0];
                    $path            = $templatePath . $modul . DIRECTORY_SEPARATOR . $fileAssociation;
                }
            }
        }

        if ($path === NULL) {
            $newTemplate     = $this->fullPagesArr[0];
            $fileAssociation = $newTemplate['fileAssociation'];
            $modul           = explode('_', $newTemplate['name'])[0];
            $path            = $templatePath . $modul . DIRECTORY_SEPARATOR . $fileAssociation;
        }
        $page = file_get_contents($path);

        $template = str_replace('{CONTENT}', $page, $template);

        return $template;
    }

    /**
     * Builds the navigation HTML
     *
     * @return string - Returns the HTML of the navigation
     */
    function getNavigation()
    {
        if (Imagely::checkSession()) {
            $pages = [
                0 => [
                    'link' => 'Gallery_Create',
                    'name' => '{TXT_IMAGELY_NAVIGATION_CREATE}',
                ],
                1 => [
                    'link' => 'Account_Galleries',
                    'name' => '{TXT_IMAGELY_NAVIGATION_GALLERIES}',
                ],
                2 => [
                    'link' => 'Account_Profile/' . $_SESSION['userId'] ,
                    'name' => '{TXT_IMAGELY_NAVIGATION_ACCOUNT}',
                ],
                3 => [
                    'link' => 'Account_Logout',
                    'name' => '{TXT_IMAGELY_NAVIGATION_LOGOUT}',
                ],
            ];
            $user  = new User();
            if ($user->isAdmin($_SESSION['userId']) == '1') {
                $pageAdmin = [
                    0 => [
                        'link' => 'Account_Admin',
                        'name' => '{TXT_IMAGELY_NAVIGATION_ADMIN}',
                    ],
                ];
                array_splice($pages, 3, 0, $pageAdmin);
            }
        } else {
            $pages = [
                0 => [
                    'link' => 'Account_Login',
                    'name' => '{TXT_IMAGELY_NAVIGATION_LOGIN}',
                ],
            ];
        }
        $navigation = '';
        foreach ($pages as &$page) {
            $navigation = $navigation . '<li><a href=\'{PATH_OFFSET}/{LANGUAGE}/' . $page['link'] . '\'>' . $page['name'] . '</a></li>';
        }

        return $navigation;
    }
}
