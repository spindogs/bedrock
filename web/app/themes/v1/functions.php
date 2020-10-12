<?php
// Escape if Timber isn't loaded yet.
// Can happen on new-installs prior to DB setup. MU-plugins haven't yet been added to the DB. 
if ( ! class_exists( 'Timber' ) ) {
    add_action( 'admin_notices', function() {
        echo '<div class="error"><p>Timber not activated.</p></div>';
    } );
    return;
}
//setup platform
Platform\Setup::setupWordpress();

//setup timber
Timber\Timber::$autoescape = true;
Timber\Timber::$cache = false;

//setup post types
App\PostType\Example::setup();
App\PostType\Post::setup();

//setup taxonomies
App\Taxonomy\ExampleTaxonomy::setup();

//setup widgets
App\Widget\ExampleWidget::setup();

//setup services
App\Service\Admin::setup();
App\Service\Menus::setup();
App\Service\Search::setup();
App\Service\Comments::setup();
App\Service\SiteOptions::setup();
App\Service\SEOSettings::setup();

//override paging
Platform\Paging::setup();

//timber context
add_filter('timber/context', function($context) {
    $context['options'] = get_fields('option');


    $Breadcrumb = new Platform\Breadcrumb();
    $context['breadcrumb'] = $Breadcrumb;

    $menus = App\Service\Menus::getMenus();
    $menu_locations = get_nav_menu_locations();

    foreach ($menus as $menu_key => $menu_value) {
        $menu_object = (isset($menu_locations[$menu_key]) ? wp_get_nav_menu_object($menu_locations[$menu_key]) : null);
        $context[$menu_key.'_name'] = (isset($menu_object->name) ? $menu_object->name : '');
        $context[$menu_key] = new Timber\Menu($menu_key);
    }

    return $context;
});

//Used for Google Map integration for Advanced Custom Fields
// function my_acf_init() {
//     acf_update_setting('google_api_key', '');
// }

// add_action('acf/init', 'my_acf_init');