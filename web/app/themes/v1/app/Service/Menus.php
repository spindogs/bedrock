<?php
namespace App\Service;

class Menus {

    protected static $menus = array(
        'main_menu' => 'Main Menu',
    );


    public static function setup()
    {
        add_theme_support('menus');
        add_action('admin_menu', array(__CLASS__, 'addMenusToAdminMenu' ));
        self::addMenus();

    }

    public static function addMenus()
    {
        if (self::$menus && !empty(self::$menus)) {
            foreach (self::$menus as $key => $value) {
                register_nav_menu($key, $value);
            }
        }
    }

    public static function addMenusToAdminMenu(){

        add_menu_page(
            'Menus',
            'Menus',
            'manage_options',
            'nav-menus.php',
            '',
            'dashicons-list-view',
            4
        );

    }

    public static function getMenus()
    {
        $menus = self::$menus;
        return $menus;
    }

}
