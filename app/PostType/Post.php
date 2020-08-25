<?php
namespace App\PostType;

use \DateTime;
use Platform\PostType;
use Platform\Filter;

class Post {

    protected static $custom_type = 'post';

    /**
     * @return void
     */
    public static function setup()
    {
        add_action('pre_get_posts', array(__CLASS__, 'queryPosts'));
        add_action( 'init', array(__CLASS__, 'changeDefaultPostType'));
    }

    /**
     * @example
     * @param WP_Query $wp_query
     * @return void
     */
    public static function queryPosts($wp_query)
    {

        if ($wp_query->is_posts_page == 1){

            if (isset($_GET['category_id']) && $_GET['category_id'] > 0 && $wp_query->is_posts_page == 1){

                $tax_query = array(
                    array(
                        'taxonomy' => 'category',
                        'field' => 'term_id',
                        'terms' => array ($_GET['category_id'])
                    )
                );

                $wp_query->set('tax_query', $tax_query);
                $wp_query->set('posts_per_page', 999);
            }


        }

    }

    public static function getLatestPosts()
    {
        $post_args = array(
            'post_type' => 'post',
            'posts_per_page' => '6',
            'meta_key' => 'news_date',
            'orderby' => 'meta_value_num',
            'order' => 'ASC'
        );

        $posts = get_posts($post_args);

        return $posts;
    }

    public static function changeDefaultPostType(){

        $get_post_type = get_post_type_object('post');
        $labels = $get_post_type->labels;
        $labels->name = 'News';
        $labels->singular_name = 'News';
        $labels->add_new = 'Add News';
        $labels->add_new_item = 'Add News';
        $labels->edit_item = 'Edit News';
        $labels->new_item = 'News';
        $labels->view_item = 'View News';
        $labels->search_items = 'Search News';
        $labels->not_found = 'No News found';
        $labels->not_found_in_trash = 'No News found in Trash';
        $labels->all_items = 'All News';
        $labels->menu_name = 'News';
        $labels->name_admin_bar = 'News';

    }

}
