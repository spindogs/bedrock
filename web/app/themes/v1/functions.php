<?php
//setup platform
Platform\Setup::setupWordpress();

//setup timber
Timber\Timber::$autoescape = true;
Timber\Timber::$cache = true;

//setup post types
App\PostType\Example::setup();

//setup taxonomies
App\Taxonomy\ExampleTaxonomy::setup();

//setup menus
App\Menu\MainMenu::setup();

//setup widgets
App\Widget\ExampleWidget::setup();

//setup search
App\Service\Search::setup();

//override paging
Platform\Paging::setup();

//options page
acf_add_options_page();

//timber context
add_filter('timber/context', function($context) {
    $context['options'] = get_fields('option');
    return $context;
});
