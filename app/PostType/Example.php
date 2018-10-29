<?php
namespace App\PostType;

use DateTime;
use Platform\PostType;
use Platform\Filter;

class Example extends PostType {

    protected static $custom_type = 'event';

    /**
     * @return void
     */
    public static function setup()
    {
        parent::setup();
        self::registerPostType();
        //EXAMPLE add_action('pre_get_posts', array(__CLASS__, 'queryPosts'));
    }

    /**
     * @return void
     */
    public static function registerPostType()
    {
        $labels = array(
            'name' => 'Events',
            'singular_name' => 'Event',
            'add_new' => 'Add event',
            'add_new_item' => 'Add event',
            'edit_item' => 'Edit event',
            'new_item' => 'New event',
            'view_item' => 'View event',
            'search_items' => 'Search events',
            'not_found' => 'No events found',
            'all_items' => 'List events',
            'menu_name' => 'Events',
            'name_admin_bar' => 'Event'
        );
        $args = array(
            'labels' => $labels,
            'public' => true,
            'show_ui' => true,
            'capability_type' => 'post',
            'menu_icon' => 'dashicons-calendar-alt',
            'hierarchical' => false,
            'supports' => ['title', 'editor', 'author', 'thumbnail'],
            'taxonomies' => [],
            'has_archive' => true,
            'rewrite' => ['slug' => 'events', 'with_front' => false]
        );

        register_post_type(self::$custom_type, $args);
    }

    /**
     * @example
     * @param WP_Query $wp_query
     * @return void
     */
    public static function queryPosts($wp_query)
    {
        //check the current WP_Query is actually querying this post_type, otherwise skip
        if ($wp_query->get('post_type') != self::$custom_type) {
            return;
        }

        //filter the WP_Query in the back office
        if (is_admin()) {
            $wp_query->set('meta_key', 'date_start');
            $wp_query->set('orderby', 'meta_value_num');
            $wp_query->set('order', 'DESC');
            return;
        }

        //custom meta query - this only shows posts that have a date_end in the future
        $meta_query = array();
        $meta_query[] = array(
            'key' => 'date_end',
            'compare' => '>=',
            'value' => date('Ymd'),
            'type' => 'NUMERIC'
        );

        //filter the WP_Query by our custom meta_query above
        $wp_query->set('meta_query', $meta_query);

        //order by a custom field
        $wp_query->set('meta_key', 'date_start');
        $wp_query->set('orderby', 'meta_value_num');
        $wp_query->set('order', 'ASC');
    }

}
