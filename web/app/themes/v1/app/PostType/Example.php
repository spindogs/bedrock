<?php
namespace App\PostType;

use DateTime;
use Platform\PostType;
use Platform\Filter;

class Example extends PostType {

    protected static $custom_type = 'example';
    protected static $singular_name = 'Example';
    protected static $plural_name = 'Examples';
    protected static $archive_url = 'examples';
    protected static $dashboard_icon = 'dashicons-calendar-alt'; // https://developer.wordpress.org/resource/dashicons/

    /**
     * @return void
     */
    public static function setup()
    {
        parent::setup();
        self::registerPostType();
        add_action('pre_get_posts', array(__CLASS__, 'queryPosts'));
    }

    /**
     * @return void
     */
    public static function registerPostType()
    {
        $labels = array(
            'name' => self::$plural_name,
            'singular_name' => self::$singular_name,
            'add_new' => 'Add ' . self::$singular_name,
            'add_new_item' => 'Add ' . self::$singular_name,
            'edit_item' => 'Edit ' . self::$singular_name,
            'new_item' => 'New ' . self::$singular_name,
            'view_item' => 'View ' . self::$singular_name,
            'search_items' => 'Search ' . self::$plural_name,
            'not_found' => 'No ' . self::$plural_name .' found',
            'all_items' => 'List ' . self::$plural_name,
            'menu_name' => self::$plural_name,
            'name_admin_bar' => self::$singular_name
        );
        $args = array(
            'labels' => $labels,
            'public' => true,
            'show_ui' => true,
            'capability_type' => 'post',
            'menu_icon' => self::$dashboard_icon,
            'hierarchical' => false,
            'supports' => ['title', 'editor', 'author', 'thumbnail'],
            'taxonomies' => [],
            'has_archive' => true,
            'rewrite' => ['slug' => self::$archive_url, 'with_front' => false]
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
            return;
        }

        //tax query example
//        if (isset($_GET['category_id']) && $_GET['category_id'] > 0 && $wp_query->is_posts_page == 1){
//
//            $tax_query = array(
//                array(
//                    'taxonomy' => 'category',
//                    'field' => 'term_id',
//                    'terms' => array ($_GET['category_id'])
//                )
//            );
//
//            $wp_query->set('tax_query', $tax_query);
//            $wp_query->set('posts_per_page', 999);
//        }

        //custom meta query - this only shows posts that have a date_end in the future
//        $meta_query = array();
//        $meta_query[] = array(
//            'key' => 'date_end',
//            'compare' => '>=',
//            'value' => date('Ymd'),
//            'type' => 'NUMERIC'
//        );
//
//        //filter the WP_Query by our custom meta_query above
//        $wp_query->set('meta_query', $meta_query);
//
//        //order by a custom field
//        $wp_query->set('meta_key', 'date_start');
//        $wp_query->set('orderby', 'meta_value_num');
//        $wp_query->set('order', 'ASC');
    }

}
