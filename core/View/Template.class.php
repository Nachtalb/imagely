<?php

/**
 * @copyright   Nick Espig <nicku8.com>
 * @author      Nick Espig <info@nicku8.com>
 * @package     imagely
 * @version     1.0
 * @subpackage  core
 */
class Template
{
    /**
     * get all available sites/templates
     *
     * @return array
     */
    function getAvailableTemplates()
    {
        $availableTemplates = [
            'Home',
            // 0
            'Create',
            // 1
            'Detail',
            // 2
            'Login',
            // 3
            'Signup',
            // 4
            'Admin',
            // 5
            'Logout',
            // 6
            'DoLogin',
            // 7
            'DoSignup',
            // 8
            'DoCreateGallery',
            // 9
            'DoDeleteGallery',
            // 10
            'DoEditGallery',
            // 11
            'Galleries',
            // 12
            'Account',
            // 13
            'DoDeleteAccount',
            // 14
            'Edit',
            // 15
        ];

        return $availableTemplates;
    }


    /**
     * generates the html of the whole site
     *
     * @param string $pageName : name of the site you want to get
     *
     * @return string : html string
     */
    function getTemplate($pageName)
    {
        switch ($pageName) {
            case 'Account':
                $template = file_get_contents(DOCUMENT_ROOT . '/template/index.html');
                $page     = file_get_contents(DOCUMENT_ROOT . '/template/account.html');
                $template = str_replace('{CONTENT}', $page, $template);
                break;
            case 'Admin':
                $template = file_get_contents(DOCUMENT_ROOT . '/template/index.html');
                $page     = file_get_contents(DOCUMENT_ROOT . '/template/admin.html');
                $template = str_replace('{CONTENT}', $page, $template);
                break;
            case 'Create':
                $template = file_get_contents(DOCUMENT_ROOT . '/template/index.html');
                $page     = file_get_contents(DOCUMENT_ROOT . '/template/create.html');
                $template = str_replace('{CONTENT}', $page, $template);
                break;
            case 'Detail':
                $template = file_get_contents(DOCUMENT_ROOT . '/template/index.html');
                $page     = file_get_contents(DOCUMENT_ROOT . '/template/detail.html');
                $template = str_replace('{CONTENT}', $page, $template);
                break;
            case 'Edit':
                $template = file_get_contents(DOCUMENT_ROOT . '/template/index.html');
                $page     = file_get_contents(DOCUMENT_ROOT . '/template/edit.html');
                $template = str_replace('{CONTENT}', $page, $template);
                break;
            case 'Home':
                $template = file_get_contents(DOCUMENT_ROOT . '/template/index.html');
                $page     = file_get_contents(DOCUMENT_ROOT . '/template/home.html');
                $template = str_replace('{CONTENT}', $page, $template);
                break;
            case 'Login':
                $template = file_get_contents(DOCUMENT_ROOT . '/template/index.html');
                $page     = file_get_contents(DOCUMENT_ROOT . '/template/login.html');
                $template = str_replace('{CONTENT}', $page, $template);
                break;
            case 'Galleries':
                $template = file_get_contents(DOCUMENT_ROOT . '/template/index.html');
                $page     = file_get_contents(DOCUMENT_ROOT . '/template/galleries.html');
                $template = str_replace('{CONTENT}', $page, $template);
                break;
            case 'Signup':
                $template = file_get_contents(DOCUMENT_ROOT . '/template/index.html');
                $page     = file_get_contents(DOCUMENT_ROOT . '/template/signup.html');
                $template = str_replace('{CONTENT}', $page, $template);
                break;
            case 'DoSignup':
                $template = file_get_contents(DOCUMENT_ROOT . '/template/index.html');
                $page     = file_get_contents(DOCUMENT_ROOT . '/template/account.html');
                $template = str_replace('{CONTENT}', $page, $template);
                break;
            default:

        }

        return $template;
    }

    /**
     * builds the navigation html
     *
     * @return string : returns the html of the navigation
     */
    function getNavigation()

    {
        if (Imagely::checkSession()) {
            $pages = [
                0 => [
                    'link' => 'Create',
                    'name' => '{TXT_IMAGELY_NAVIGATION_CREATE}',
                ],
                1 => [
                    'link' => 'Galleries',
                    'name' => '{TXT_IMAGELY_NAVIGATION_GALLERIES}',
                ],
                2 => [
                    'link' => 'Account',
                    'name' => '{TXT_IMAGELY_NAVIGATION_ACCOUNT}',
                ],
                3 => [
                    'link' => 'Logout',
                    'name' => '{TXT_IMAGELY_NAVIGATION_LOGOUT}',
                ],
            ];
            $user  = new User();
            if ($user->isAdmin($_SESSION['userId']) == '1') {
                $pageAdmin = [
                    0 => [
                        'link' => 'Admin',
                        'name' => '{TXT_IMAGELY_NAVIGATION_ADMIN}',
                    ],
                ];
                array_splice($pages, 3, 0, $pageAdmin);
            }
        } else {
            $pages = [
                0 => [
                    'link' => 'Login',
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
