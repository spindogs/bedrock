<?php
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
// App\Service\Admin::setup();
App\Service\Menus::setup();
App\Service\Search::setup();
App\Service\Comments::setup();
App\Service\SiteOptions::setup();

//override paging
Platform\Paging::setup();

//options page
acf_add_options_page();

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