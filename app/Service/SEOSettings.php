<?php
namespace App\Service;

use Platform\Form;
use Timber\Timber;

class SEOSettings {

    //id of *spindogs* user account
    protected static $super_admin_id = 1;
    protected static $use_header_section = true;
    protected static $use_footer_section = true;
    protected static $use_redirects_section = true;
    protected static $use_seo_section = true;
    public $csv_form;

    /**
     * @return void
     */
    public static function setup()
    {

        if (self::$use_redirects_section) {
            add_action('init', array(__CLASS__, 'actionSpindogsRedirects'), 1);

            if (isset($_GET['sd_redirect_export'])) {
                add_action('init', array(__CLASS__, 'exportRedirectData'));
            }

            if (isset($_POST['301_redirects'])) {
                add_action('admin_init', array(__CLASS__,'saveSEORedirects'));
            }
        }

        if (is_user_logged_in() && get_current_user_id() == self::$super_admin_id) {
            add_action('admin_menu', array(__CLASS__, 'addAdminMenuItems'));
        }

        if (self::$use_seo_section) {
            add_action('wp_ajax_updateSEO', array(__CLASS__, 'updateSEO'));
            add_action('wp_ajax_nopriv_updateSEO', array(__CLASS__, 'updateSEO'));

            add_action('wp_ajax_updateCategorySEO', array(__CLASS__, 'updateCategorySEO'));
            add_action('wp_ajax_nopriv_updateCategorySEO', array(__CLASS__, 'updateCategorySEO'));

            if (isset($_GET['sd_seo_export'])) {
                add_action('init', array(__CLASS__, 'exportSEOData'));
            }
        }

        //add_action( 'admin_notices', array(__CLASS__,'my_error_notice'));
        //add_action( 'admin_notices', array(__CLASS__,'my_update_notice'));


    }

    public static function addAdminMenuItems()
    {

        $main = add_menu_page(
            'Theme Settings',                         // page title
            'Theme Settings',                         // menu title
            'manage_options',                  // capability
            'sd-theme',                         // menu slug
            array ( __CLASS__, 'renderLandingPage' ) // callback function
        );

        if (self::$use_header_section) {
            $sub = add_submenu_page(
                'sd-theme',                         // parent slug
                'Theme Header',                     // page title
                'Header',                     // menu title
                'manage_options',                  // capability
                'sd-theme-header',                     // menu slug
                array(__CLASS__, 'renderHeaderSettings') // callback function, same as above
            );
        }

        if (self::$use_footer_section) {
            $sub = add_submenu_page(
                'sd-theme',                         // parent slug
                'Theme Footer',                     // page title
                'Footer',                     // menu title
                'manage_options',                  // capability
                'sd-theme-footer',                     // menu slug
                array(__CLASS__, 'renderFooterSettings') // callback function, same as above
            );
        }

        if (self::$use_redirects_section) {
            $sub = add_submenu_page(
                'sd-theme',                         // parent slug
                'Theme Redirects',                     // page title
                'Redirects',                     // menu title
                'manage_options',                  // capability
                'sd-theme-redirects',                     // menu slug
                array(__CLASS__, 'renderRedirectSettings') // callback function, same as above
            );
        }

        if (self::$use_seo_section) {
            $sub = add_submenu_page(
                'sd-theme',                         // parent slug
                'Seo Settings',                     // page title
                'Seo Settings',                     // menu title
                'manage_options',                  // capability
                'sd-theme-seo',                     // menu slug
                array(__CLASS__, 'renderSeoSettings') // callback function, same as above
            );
        }

    }

    public static function renderLandingPage()
    {

        $data = [];

        Timber::render('/admin/seo-landing.twig', $data);

    }

    public static function renderHeaderSettings()
    {

        if (!empty($_POST['closing_head_content'])){
            update_option('sd_closing_head_content', htmlentities(stripslashes($_POST['closing_head_content'])));
        }

        if (!empty($_POST['opening_body_content'])){
            update_option('sd_opening_body_content', htmlentities(stripslashes($_POST['opening_body_content'])));
        }

        if (!empty($_POST['header_site_language'])){
            update_option('sd_header_site_language', htmlentities(stripslashes($_POST['header_site_language'])));
        }

        $data = [
            'header_site_language' => html_entity_decode(get_option('sd_header_site_language')),
            'closing_head_content' => html_entity_decode(get_option('sd_closing_head_content')),
            'opening_body_content' => html_entity_decode(get_option('sd_opening_body_content'))
        ];

        Timber::render('/admin/seo-header.twig', $data);

    }

    public static function renderFooterSettings()
    {

        if (!empty($_POST['closing_body_content'])){
            update_option('sd_closing_body_content', htmlentities(stripslashes($_POST['closing_body_content'])));
        }

        if (!empty($_POST['closing_html_content'])){
            update_option('sd_closing_html_content', htmlentities(stripslashes($_POST['closing_html_content'])));
        }

        $data = [
            'closing_body_content' => html_entity_decode(get_option('sd_closing_body_content')),
            'closing_html_content' => html_entity_decode(get_option('sd_closing_html_content'))
        ];

        Timber::render('/admin/seo-footer.twig', $data);

    }

    public static function renderRedirectSettings()
    {

        if (!empty($_POST['301_redirects'])){
            self::updateRedirects($_POST['301_redirects']);
        }

        $redirects = get_option('sd_seo_redirects');

        $SeoImport = new self();
        $import_form = $SeoImport->makeRedirectImportForm();

        $redirect_types = array(
            '301' => '301',
            '302' => '302'
        );

        $data = [
            'redirects' => $redirects,
            'redirect_types' => $redirect_types,
            'import_form' => $import_form,
        ];

        Timber::render('/admin/seo-redirects.twig', $data);

    }

    public static function renderSeoSettings()
    {

        $post_types = self::getPostTypes(false, false, array('attachment'));
        $all_post_types = array();
        foreach ($post_types as $key => $value){
            $all_post_types[$key]['post_type'] = $value;
            $all_post_types[$key]['posts'] = self::getPostsByPostType($value->name);
        }

        $categories = self::getCategories(false, false);
        $all_categories = array();
        foreach ($categories as $key => $value){
            //print_r($value);
            $all_categories[$key]['taxonomy'] = $value;
            $all_categories[$key]['terms'] = self::getCategoryByTaxonomy($key);
        }


        $SeoImport = new self();
        $import_form = $SeoImport->makeImportForm();

        //print_r($all_categories);

        $admin_ajax_url =  admin_url('admin-ajax.php');

        $data = array(
            'post_types' => $all_post_types,
            'categories' => $all_categories,
            'ajax_url' => $admin_ajax_url,
            'import_form' => $import_form,
        );

        //print_r($all_post_types);

        Timber::render('/admin/seo-settings.twig', $data);

    }

    public static function updateRedirects($data){

        $redirects = array();

        for($i = 0; $i < sizeof($data['request']); ++$i) {

            $request = trim( sanitize_text_field( $data['request'][$i] ) );
            $destination = trim( sanitize_text_field( $data['destination'][$i] ) );

            if ($request == '' && $destination == '') {
                continue;
            } else {
                $redirects[$request] = $destination;
            }
        }

        update_option('301_redirects', $redirects);

    }

    public static function actionSpindogsRedirects() {

//        $userrequest = str_ireplace(get_option('home'),'',self::get_address());
        $userrequest = self::get_address();
        $userrequest = rtrim($userrequest,'/');

        $redirects = get_option('sd_seo_redirects');

        if (!empty($redirects)) {

            $do_redirect = '';

            $wildcard = false;

            foreach ($redirects as $key => $value) {
                if (!empty($value['type'])){
                    $redirect_type = $value['type'];
                } else {
                    $redirect_type = '301';
                }
                if ($wildcard === 'true' && strpos($value['request'],'*') !== false) {

                    if ( strpos($userrequest, '/wp-login') !== 0 && strpos($userrequest, '/wp-admin') !== 0 ) {
                        $stored_destination = $value['destination'];
                        $stored_request = str_replace('*','(.*)',$value['request']);
                        $pattern = '/^' . str_replace( '/', '\/', rtrim( $stored_request, '/' ) ) . '/';
                        $destination = str_replace('*','$1',$stored_destination);
                        $output = preg_replace($pattern, $destination, $userrequest);
                        if ($output !== $userrequest) {
                            $do_redirect = $output;
                        }
                    }
                } elseif(urldecode($userrequest) == rtrim($value['request'],'/')) {
                    $do_redirect = $value['destination'];
                }

//				if (is_user_logged_in()) {
//				    echo rtrim($storedrequest,'/');
//				    echo "<br>1<br>";
//				    echo urldecode($userrequest);
//				    echo "2<br>";
//
//                    echo "3<br>";
//                    echo $userrequest;
//                    echo "<br>";
//
//                    echo "<";
//                    echo $do_redirect;
//                    echo ">";
//
//                    //exit;
//				}

                if ($do_redirect !== '' && trim($do_redirect,'/') !== trim($userrequest,'/')) {

                    if (strpos($do_redirect,'/') === 0){
                        $do_redirect = home_url().$do_redirect;
                    }
                    header ('HTTP/1.1 '.$redirect_type.' Moved Permanently');
                    header ('Location: ' . $do_redirect);
                    exit();
                }
                else { unset($redirects); }
            }

        }
    }


    public static function get_address() {

        return self::get_protocol().'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

    }

    public static function get_protocol() {

        $protocol = 'http';

        if ( isset( $_SERVER["HTTPS"] ) && strtolower( $_SERVER["HTTPS"] ) == "on" ) {
            $protocol .= "s";
        }

        return $protocol;
    }

    public static function getPostTypes($args = false, $custom_args = false, $exclude = array()){

        if (!$args) {
            $args = array(
                'public'   => true,
                '_builtin' => true
            );
        }

        if (!$custom_args) {
            $custom_args = array(
                'public'   => true,
                '_builtin' => false
            );
        }

        $output = 'objects'; // names or objects, note names is the default
        $operator = 'and'; // 'and' or 'or'

        $custom_post_types = get_post_types($custom_args, $output, $operator);
        $post_types = get_post_types($args, $output, $operator);

        $all_post_types = array_merge($post_types, $custom_post_types);

        if (isset($exclude) && count($exclude) > 0) {
            foreach ($exclude as $value) {
                unset($all_post_types[$value]);
            }
        }

        //$post_types = get_post_types($args);

        return $all_post_types;
    }

    public static function getPostsByPostType($post_type){

        $args = array(
            'post_type' => $post_type,
            'posts_per_page' => 9999,
        );

        $posts = get_posts($args);

        $results = array();

        foreach ($posts as $key => $value) {

            $results[$value->ID]['id'] = $value->ID;
            $results[$value->ID]['post_title'] = $value->post_title;
            $results[$value->ID]['post_type'] = $value->post_type;
            $results[$value->ID]['post_name'] = $value->post_name;

            $seo_title = get_post_meta($value->ID, 'seo_browser_title', true);
            $seo_desc = get_post_meta($value->ID, 'seo_meta_desc', true);
            $seo_image_id = get_post_meta($value->ID, 'seo_og_image_id', true);

            if (isset($seo_title) && strlen($seo_title) > 0) {
                $results[$value->ID]['seo_title'] = $seo_title;
            } else {
                $results[$value->ID]['seo_title'] = $value->post_title;
            }

            if (isset($seo_desc) && strlen($seo_desc) > 0) {
                $results[$value->ID]['seo_desc'] = $seo_desc;
            } else {
                $results[$value->ID]['seo_desc'] = $value->post_title;
            }

            if (isset($seo_image_id) && $seo_image_id > 0) {
                $results[$value->ID]['seo_image_id'] = $seo_image_id;
            } else {
                $results[$value->ID]['seo_image_id'] = '';
            }

        }

        return $results;

    }

    public static function getCategories($args = false, $custom_args = false, $exclude = array()){

        if (!$args) {
            $args = array(
                'public'   => true,
                '_builtin' => true
            );
        }

        if (!$custom_args) {
            $custom_args = array(
                'public'   => true,
                '_builtin' => false
            );
        }

        $output = 'objects'; // names or objects, note names is the default
        $operator = 'and'; // 'and' or 'or'

        $categories = get_taxonomies('', $output, $operator);

        //print_r($categories);

//        $categories = get_taxonomies();
//
//        print_r($categories);

        if (isset($exclude) && count($exclude) > 0 && !empty($exclude)) {
            foreach ($exclude as $value) {
                unset($categories[$value]);
            }
        }

        //$post_types = get_post_types($args);

        return $categories;
    }

    public static function getCategoryByTaxonomy($post_type){

        $args = array(
            'taxonomy' => $post_type,
            'hide_empty' => false,
        );

        $categories = get_terms($args);

        $results = array();

        foreach ($categories as $key => $value) {

            $results[$value->term_id]['id'] = $value->term_id;
            $results[$value->term_id]['name'] = $value->name;
            $results[$value->term_id]['taxonomy'] = $value->taxonomy;
            $results[$value->term_id]['slug'] = $value->slug;

            $seo_title = get_term_meta($value->term_id, 'seo_browser_title', true);
            $seo_desc = get_term_meta($value->term_id, 'seo_meta_desc', true);
            $seo_image_id = get_term_meta($value->term_id, 'seo_og_image_id', true);

            if (isset($seo_title) && strlen($seo_title) > 0) {
                $results[$value->term_id]['seo_title'] = $seo_title;
            } else {
                $results[$value->term_id]['seo_title'] = $value->name;
            }

            if (isset($seo_desc) && strlen($seo_desc) > 0) {
                $results[$value->term_id]['seo_desc'] = $seo_desc;
            } else {
                $results[$value->term_id]['seo_desc'] = $value->name;
            }

            if (isset($seo_image_id) && $seo_image_id > 0) {
                $results[$value->term_id]['seo_image_id'] = $seo_image_id;
            } else {
                $results[$value->term_id]['seo_image_id'] = '';
            }

        }

        return $results;

    }

    /**
     * @return void
     */
    public static function updateSEO()
    {

        $post_id = $_GET['post_id'];
        $title = $_GET['title'];
        $desc = $_GET['desc'];
        $img = $_GET['img'];
        $clean = $_GET['clean'];

        self::updateField('seo_browser_title', $title, $post_id);
        self::updateField('seo_meta_desc', $desc, $post_id);
        self::updateField('seo_og_image_id', $img, $post_id);
        //$Data->updatePostField('post_name', $clean, $post_id);

        add_action( 'admin_notices', array(__CLASS__,'my_error_notice'));
        add_action( 'admin_notices', array(__CLASS__,'my_update_notice'));

        die();
    }

    /**
     * @return void
     */
    public static function updateCategorySEO()
    {

        $post_id = $_GET['post_id'];
        $title = $_GET['title'];
        $desc = $_GET['desc'];
        $img = $_GET['img'];
        $clean = $_GET['clean'];

        self::updateCatField('seo_browser_title', $title, $post_id);
        self::updateCatField('seo_meta_desc', $desc, $post_id);
        self::updateCatField('seo_og_image_id', $img, $post_id);
        //$Data->updateCatField('slug', $clean, $post_id);

        add_action( 'admin_notices', array(__CLASS__,'my_error_notice'));
        add_action( 'admin_notices', array(__CLASS__,'my_update_notice'));

        die();
    }

    public static function updateField($field, $value, $post_id){

        update_post_meta( $post_id, $field, $value);

    }

    public static function updateCatField($field, $value, $term_id){

        update_term_meta( $term_id, $field, $value);

    }

    public function makeImportForm(){

        $csv_form = new Form('ExportUpload');
        $csv_form->placeholders = array();
        $csv_form->html('<div class="half_width">');
        $csv_form->file('file', 'File', '', true);
        $csv_form->html('<br>');
        $csv_form->submit('Import', array('class' => 'button button-primary'));
        $csv_form->html('</div>');

        if ($csv_form->success()) {

            $result = $this->import($csv_form->values['file']);

            foreach ($result as $key => $value) {

                if (isset($result) && count($result) && !empty($result)) {
                    $this->importUpdatedSEO($result);
                }

            }
        }

        return $csv_form;

    }

    public function makeRedirectImportForm(){

        $csv_form = new Form('RedirectImportUpload');
        $csv_form->placeholders = array();
        $csv_form->html('<div class="half_width">');
        $csv_form->file('file', 'File', '', true);
        $csv_form->html('<br>');
        $csv_form->submit('Import', array('class' => 'button button-primary'));
        $csv_form->html('</div>');

        if ($csv_form->success()) {

            $result = $this->import($csv_form->values['file']);

            $rows = array();
            if (!empty($result)) {
                foreach ($result as $key => $value) {
                    $rows[] = array(
                        'request'=> $value[1],
                        'destination'=> $value[2],
                        'type'=> $value[3]
                    );
                }

                update_option('sd_seo_redirects', $rows);
            }

        }

        return $csv_form;

    }


    public function importUpdatedSEO($results){

        foreach ($results as $key => $value){

            $post_id = $value[0];

            $current_browser_title = get_post_meta($post_id, 'seo_browser_title', true);

            $current_meta_desc = get_post_meta($post_id, 'seo_meta_desc', true);


            if (!isset($current_browser_title)) {
                self::updateField('seo_browser_title', $value[4], $post_id);
            } elseif (isset($current_browser_title) && $current_browser_title != $value[4]){
                self::updateField('seo_browser_title', $value[4], $post_id);
            }

            if (!isset($current_meta_desc)) {
                self::updateField('seo_meta_desc', $value[5], $post_id);
            } elseif (isset($current_meta_desc) && $current_meta_desc != $value[5]){
                self::updateField('seo_meta_desc', $value[5], $post_id);
            }
        }
    }

    public static function getAllPosts(){

        $args = array(
            'posts_per_page' => 99999,
            'post_type' => 'any',
            'orderby' => 'post_type'
        );

        $posts = get_posts($args);

        return $posts;
    }

    public static function exportSEOData() {

        $filename = 'spindogs-seo-data.csv';

        $rows = array();
        $headings = array(
            'ID',
            'Post Title',
            'Post URL',
            'Post Type',
            'Meta Title',
            'Meta Desc',
            'Whole URL',
        );

        $posts = self::getAllPosts();

        foreach ($posts as $key => $value) {
            $rows[] = array(
                'id' => $value->ID,
                'post_title' => $value->post_title,
                'post_name' => $value->post_name,
                'post_type' => $value->post_type,
                'meta_title' => get_post_meta($value->ID, 'seo_browser_title', true),
                'meta_desc' => get_post_meta($value->ID, 'seo_meta_desc', true),
                'whole_url' => get_permalink($value->ID),
            );
        }

        $csv = fopen('php://temp', 'r+');

        if (!$headings) {
            //do nothing
        } elseif (is_array($headings)) {
            $headings_values = array_values($headings);
            fputcsv($csv, $headings_values);
        } elseif ($rows) {
            $first_row = reset($rows);
            $first_row = (array)$first_row;
            $headings = array_keys($first_row);
            fputcsv($csv, $headings);
        }

        foreach ($rows as $r) {
            $r = (array)$r;
            fputcsv($csv, $r);
        }

        rewind($csv);

        $output = stream_get_contents($csv);

        @fclose($csv);
        @unlink($csv);

        if (ini_get('zlib.output_compression')) {
            ini_set('zlib.output_compression', 'Off');
        }

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        //header("Content-Type: text/comma-separated-values");
        //header("Content-Disposition: attachment; filename=\"".$filename."\";");
        header("Content-Transfer-Encoding: binary");

        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: csv" . date("Y-m-d") . ".csv");
        header( "Content-Disposition: attachment; filename=".$filename.".csv");

//        header("Pragma: public");
//        header("Expires: 0");
//        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
//        header("Cache-Control: private", false);
//        header("Content-Type: application/octet-stream");
//        header("Content-Disposition: attachment; filename=".$filename.".csv" );
//        header("Content-Transfer-Encoding: binary");
//
        print_r($output);
        exit;

    }

    public static function exportRedirectData() {

        $filename = 'spindogs-redirect-data.csv';

        $redirects = get_option('sd_seo_redirects');

        $rows = array();
        $headings = array(
            'ID',
            'Request',
            'Destination',
            'Type',
        );

        $i = 1;
        foreach ($redirects as $key => $value) {
            $rows[] = array(
                'id' => $i,
                'request' => $value['request'],
                'destination' => $value['destination'],
                'type' => $value['type'],
            );
        $i++;
        }

        $csv = fopen('php://temp', 'r+');

        if (!$headings) {
            //do nothing
        } elseif (is_array($headings)) {
            $headings_values = array_values($headings);
            fputcsv($csv, $headings_values);
        } elseif ($rows) {
            $first_row = reset($rows);
            $first_row = (array)$first_row;
            $headings = array_keys($first_row);
            fputcsv($csv, $headings);
        }

        foreach ($rows as $r) {
            $r = (array)$r;
            fputcsv($csv, $r);
        }

        rewind($csv);

        $output = stream_get_contents($csv);

        @fclose($csv);
        @unlink($csv);

        if (ini_get('zlib.output_compression')) {
            ini_set('zlib.output_compression', 'Off');
        }

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        //header("Content-Type: text/comma-separated-values");
        //header("Content-Disposition: attachment; filename=\"".$filename."\";");
        header("Content-Transfer-Encoding: binary");

        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: csv" . date("Y-m-d") . ".csv");
        header( "Content-Disposition: attachment; filename=".$filename.".csv");

//        header("Pragma: public");
//        header("Expires: 0");
//        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
//        header("Cache-Control: private", false);
//        header("Content-Type: application/octet-stream");
//        header("Content-Disposition: attachment; filename=".$filename.".csv" );
//        header("Content-Transfer-Encoding: binary");
//
        print_r($output);
        exit;

    }

    /*
    import
    */
    public function import($newfile, $num_rows=999999, $start_row=0, $ignore_header=true) {

        //brilliant bit of code to avoid confusion between Windows and Mac csv line endings
        ini_set("auto_detect_line_endings", true);

        //open handle
        $handle = fopen($newfile, 'r');

        $i = 0;
        $j = 0;

        if ($ignore_header) {
            $start_row++; //allow for header
        }

        //loop through each row
        $rtn = array();
        while ($row = fgetcsv($handle, 1000, ',')) {

            if ($i < $start_row) { //skip until we get to start row
            } else {
                $rtn[] = $row;
                $j++;
            }

            if ($j >= $num_rows) {
                break;
            }

            $i++;
        }

        @unlink($newfile);
        return $rtn;

    }

    public static function my_error_notice() {
        ?>
        <div class="error notice">
            <p><?php _e( 'Omg what a poop error', 'my_plugin_textdomain' ); ?></p>
        </div>
        <?php
    }


    public static function  my_update_notice() {
        ?>
        <div class="updated notice">
            <p><?php _e( 'well done m8', 'my_plugin_textdomain' ); ?></p>
        </div>
        <?php
    }


    public static function saveSEORedirects($data) {

        if ( !current_user_can('manage_options') )  {
            wp_die( 'You do not have sufficient permissions to access this page.' );
        }

        $data = $_POST['301_redirects'];

        $redirects = array();

        for($i = 0; $i < sizeof($data['request']); ++$i) {

            $request = trim( sanitize_text_field( $data['request'][$i] ) );
            $destination = trim( sanitize_text_field( $data['destination'][$i] ) );
            $type = trim( sanitize_text_field( $data['type'][$i] ) );

            if ($request == '' && $destination == '') {
                continue;
            } else {
                $redirects[] = array(
                    'request' => $request,
                    'destination' => $destination,
                    'type' => $type
                );
            }
        }

        update_option('sd_seo_redirects', $redirects);

    }



}
