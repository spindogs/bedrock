<?php
namespace App\Service;

class Admin {

    //id of *spindogs* user account
    protected static $super_admin_id = 1;
    protected static $site_logo = '/images/logo.png';
    protected static $site_favicon = '/images/favicon.ico';
    protected static $site_primary_colour = '#E4192F';

    /**
     * @return void
     */
    public static function setup()
    {
        add_action('admin_menu', array(__CLASS__, 'removeMenuItems' ));
        add_action('login_head', array(__CLASS__, 'customAdminScreen'));

        add_action('admin_head', array(__CLASS__, 'customDashboard'));


        add_action('login_head', array(__CLASS__, 'enqueueAdminStyleSheets'));
        add_action( 'admin_enqueue_scripts', array(__CLASS__, 'enqueueAdminStyleSheets' ));

        add_action( 'restrict_manage_posts',array(__CLASS__, 'pageTemplateFilterDropdown'));
        add_filter( 'request', array(__CLASS__, 'filterPageList'));


    }

    public static function pageTemplateFilterDropdown()
    {
        if ( $GLOBALS['pagenow'] === 'upload.php' ) {
            return;
        }

        $template = isset( $_GET['page_template_filter'] ) ? $_GET['page_template_filter'] : "all";
        $default_title = apply_filters( 'default_page_template_title',  __( 'Default Template' ), 'meta-box' );
        ?>
        <select name="page_template_filter" id="page_template_filter">
            <option value="all">All Page Templates</option>
            <option value="default" <?php echo ( $template == 'default' )? ' selected="selected" ' : "";?>><?php echo esc_html( $default_title ); ?></option>
            <?php page_template_dropdown($template); ?>
        </select>
        <?php
    }//end func


    public static function filterPageList( $vars ){

        if ( ! isset( $_GET['page_template_filter'] ) ) return $vars;
        $template = trim($_GET['page_template_filter']);
        if ( $template == "" || $template == 'all' ) return $vars;

        $vars = array_merge(
            $vars,
            array(
                'meta_query' => array(
                    array(
                        'key'     => '_wp_page_template',
                        'value'   => $template,
                        'compare' => '=',
                    ),
                ),
            )
        );
        return $vars;

    }//end func

    public static function enqueueAdminStyleSheets()
    {

        $site_logo = get_template_directory_uri() . self::$site_logo;
        $colour = self::$site_primary_colour;

        echo '<style type="text/css">
            h1 a {
            background-image: url('.$site_logo.') !important;
            height:120px !important;
            width: 160px !important;
            background-size: 150px !important;
           
            }
            body.login {
              background-color: '.$colour.';
               background-size: 100% auto;
            }
            .login #backtoblog a, .login #nav a {
                text-decoration: none;
                color: #fff;
            }
            .login form {
                border: 2px solid #FFFFFF;
            }
            .login #nav, .login #backtoblog {
                text-align: center;
            }
            .login #nav {
                text-align: center;
            }
    </style>';

    }


    public static function removeMenuItems(){

        //remove_menu_page( 'edit.php' );               //Media
        remove_menu_page('edit-comments.php');          //Comments
        //remove_menu_page('index.php');                  //Dashboard
        remove_menu_page('themes.php');                 //Appearance
        //remove_menu_page('users.php');                //Users



        if(get_current_user_id() != self::$super_admin_id) {
            remove_menu_page('tools.php');                  //Tools
            remove_menu_page('options-general.php');        //Settings
            remove_menu_page('edit.php?post_type=acf-field-group'); // acf
            remove_menu_page('plugins.php');                //Plugins

        }

    }

    public static function customAdminScreen(){

        $site_logo = get_template_directory_uri() . self::$site_logo;

        echo '<style type="text/css">
            h1 a {
            background-image: url('. $site_logo .') !important;
           
            }
         
    </style>';

    }

    public static function customDashboard()
    {
        $colour = self::$site_primary_colour;
        $site_favicon = get_template_directory_uri() . self::$site_favicon;

        echo '<style>
            #wpadminbar #wp-admin-bar-wp-logo > .ab-item .ab-icon:before {
            background-image: url(' . $site_favicon . ') !important;
            background-position: 0 0;
            background-size: contain;
            color:rgba(0, 0, 0, 0);
            }       
            #adminmenuback,
            #adminmenuwrap,
            #adminmenu {
                background: #131316;
            }

            #adminmenu li.wp-menu-open .wp-has-current-submenu {
                background:'.$colour.' !important;
            }
            #adminmenu li.current a {
                background:'.$colour.' !important;
            }

            #adminmenu li.menu-top a:hover {
                color:'.$colour.' !important;
            }

            #adminmenu li.opensub > a {
                color:'.$colour.' !important;
            }

            #adminmenu li.opensub .wp-menu-image::before {
                color: '.$colour.' !important;
            }
            #adminmenu li:hover .wp-menu-image::before {
                color: '.$colour.' !important;
            }
            #adminmenu li:focus .wp-menu-image::before {
                color: '.$colour.' !important;
            }

            #adminmenu li.wp-menu-open:hover .wp-menu-image::before {
                color: white !important;
            }
            #adminmenu li.wp-menu-open:focus .wp-menu-image::before {
                color: white !important;
            }
            #adminmenu li.current:hover .wp-menu-image::before {
                color: white !important;
            }
            #adminmenu li.current:focus .wp-menu-image::before {
                color: white !important;
            }

            #adminmenu li a:hover {
                color: '.$colour.' !important;
            }
            #adminmenu li a:focus {
                color: '.$colour.' !important;
            }

            #adminmenu li.wp-menu-open a:hover {
                color:white !important;
            }
            #adminmenu li.wp-menu-open a:focus {
                color:white !important;
            }
            #adminmenu li.current a:hover {
                color:white !important;
            }
            #adminmenu li.current a:focus {
                color:white !important;
            }

            #collapse-button:hover {
                color: '.$colour.';
            }
            #collapse-button:focus {
                color: '.$colour.';
            }

            #wpadminbar {
                background:black;
            }
            
            #adminmenu .wp-submenu {
                background-color: #444 !important;
            }

            .acf-flexible-content .layout[data-layout="spacer"] {
                background:#eee;
            }
            
//            .postbox {
//                border: 0.5px solid '.$colour.';
//            }
        </style>';
    }


}
