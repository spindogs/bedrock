<?php
namespace App\Service;

/*
The class works one of two ways
•   At the top of the class you can chose with the $admin_core_posts variable whether the core pages are controlled via the CMS admin panel or via the individual class file
o   The Multi Select Dropdown in located in Settings > Reading at bottom of the form
•   If the core pages are managed via the Class file – Homepage and Blog page are automatically added to the Core Pages, any additional pages have to be added in the file!

If you want to make any custom post types or posts which aren’t pages ‘Core’ then you will have to use the manual route until I add a mechanism for post types that aren’t page!
*/

class CorePages {

    /* Automatically locks homepage */
    protected static $auto_front_page = true;
    /* Automatically locks blog/news page */
    protected static $auto_posts_page = true;
    /* Chooses whether core posts are set within those file or dropdown in admin */
    protected static $admin_core_posts = true;

    /**
     * @return void
     */
    public static function setup()
    {
        add_action( 'admin_head', array(__CLASS__, 'stopPostDeletion'));
        add_action( 'admin_head', array(__CLASS__, 'addPostListStyles'));
        add_action( 'admin_footer', array(__CLASS__, 'stopPostListDeletion'), 9999);

        if (self::$admin_core_posts) {
            add_filter('admin_init', array(__CLASS__, 'registerCorePageAdminSection'));
        }

    }

    /* Function that registers the core admin pages */
    public static function registerCorePageAdminSection()
    {
        register_setting('reading', 'core_pages', 'validate_setting');
        add_settings_field('core_pages', '<label for="core_pages">'.__('Core Pages' , 'core_pages' ).'</label>' , array(__CLASS__, 'addCorePageToSettings'), 'reading');
    }

    /* Function that sets up all the core posts on the site */
    public static function getCorePosts(){

        $page_ids = array();

        /* if set to manual - core posts are taken from reading settings in admin */
        if (self::$admin_core_posts) {

            $page_ids = get_option( 'core_pages', '' );

            /* Else the core pages are manually add via this file */
        } else {

            if (self::$auto_front_page) {

                $frontpage_id = get_option( 'page_on_front' );

                if (isset($frontpage_id) && $frontpage_id > 0) {
                    $page_ids[] = $frontpage_id;
                }

            }

            if (self::$auto_posts_page) {

                $posts_page_id = get_option( 'page_for_posts' );

                if (isset($posts_page_id) && $posts_page_id > 0) {
                    $page_ids[] = $posts_page_id;
                }

            }

            /*Add Pages to array to make them core */
            //$page_ids[] = 5;
            //$page_ids[] = 95;

        }

        return $page_ids;

    }

    /* Adds custom lock styles into admin section */
    public static function addPostListStyles(){
        ?>
        <style>
            tr.wp-locked-core .locked-indicator-icon:before {
                color: #82878c;
                content: "\f160";
                display: inline-block;
                font: 400 20px/1 dashicons;
                speak: none;
                vertical-align: middle;
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
            }
            tr.wp-locked-core .locked-indicator {
                margin-left: 6px;
                height: 20px;
                width: 16px;
            }
        </style>
        <?
    }


    /* Stops pages being deleted in the list view and adds a lock symbol */
    public static function stopPostListDeletion(){

        $page_ids = self::getCorePosts();

        if (isset($page_ids) && count($page_ids) > 0) {
            ?>
            <script type="text/javascript">
                jQuery( document ).ready( function( $ ) {
                    <?php foreach ($page_ids as $key => $value){ ?>
                    jQuery('#post-<?= $value; ?>').addClass('wp-locked-core');
                    jQuery('#post-<?= $value; ?>' + " .trash").hide();
                    jQuery('#post-<?= $value; ?>' + " #cb-select-<?= $value; ?>").remove();
                    <? } ?>
                });
            </script>
            <?
        }

    }

    /* Stops post deletion on individual post pages */
    public static function stopPostDeletion(){

        global $post;

        $page_ids = self::getCorePosts();

        if (isset($page_ids) && !empty($page_ids)) {
            if (isset($post) && isset($post->ID) && in_array($post->ID, $page_ids)) {
                ?>
                <script type="text/javascript">
                    jQuery(document).ready(function ($) {
                        $('#delete-action').remove();
                    });
                </script>
                <?php
            }
        }

    }


    /* Creates the Admin interface for core pages */
    public static function addCorePageToSettings()
    {
        $core_page_values = get_option('core_pages', '');

        $all_pages_args = array(
            'post_type' => 'page',
            'posts_per_page' => '9999',
            'orderby' => 'post_title',
            'order' => 'ASC'
        );

        $pages = get_posts($all_pages_args);

        echo '<select id="core_pages" name="core_pages[]" multiple style="height:500px;">';
        foreach ($pages as $key => $value) {
            if (in_array($value->ID, $core_page_values)) {
                $selected = 'selected';
            } else {
                $selected = '';
            }
            echo '<option '. $selected .' value="'.$value->ID.'">' . $value->post_title . '</option>';
        }
        echo "</select>";
    }


}
