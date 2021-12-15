<?php
namespace App\Service;

class SiteOptions {

    /**
     * @return void
     */
    public static function setup()
    {
        self::addSiteOptions();
    }

    /**
     * @param WP_Query $wp_query
     * @return void
     */
    public static function addSiteOptions()
    {

        if (function_exists('acf_add_options_page')) {

            // add parent
            $parent = acf_add_options_page(array(
                'page_title' => 'Options Top Page',
                'menu_title' => 'Site Options',
                'redirect' => false
            ));

            // add sub page
            acf_add_options_sub_page(array(
                'page_title' => 'Options Subpage',
                'menu_title' => 'Options Subpage',
                'parent_slug' => $parent['menu_slug'],
            ));


        }
    }

}
