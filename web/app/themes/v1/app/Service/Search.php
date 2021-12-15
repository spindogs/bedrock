<?php
namespace App\Service;

class Search {

    /**
     * @return void
     */
    public static function setup()
    {
        add_action('pre_get_posts', array(__CLASS__, 'queryPosts'));
    }

    /**
     * @param WP_Query $wp_query
     * @return void
     */
    public static function queryPosts($wp_query)
    {
        if ($wp_query->is_search && is_search() && isset($_GET['s'])) {
            $wp_query->set('s', $_GET['s']);
        }
    }

}
