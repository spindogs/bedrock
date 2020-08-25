<?php
namespace App\Taxonomy;

class ExampleTaxonomy {

    protected static $taxonomy = 'example_category';
    protected static $associated_post_types = ['example']; //['example', 'example2']
    protected static $taxonomy_name = 'Example Categories';
    protected static $taxonomy_url = 'example_categories';

    /**
     * @return void
     */
    public static function setup()
    {
        self::registerTaxonomy();
    }

    /**
     * @return void
     */
    public static function registerTaxonomy()
    {

        $args = array(
            'label' => self::$taxonomy_name,
            'public' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'hierarchical' => true,
            'rewrite' => ['slug' => self::$taxonomy_url, 'with_front' => false]
        );

        register_taxonomy(self::$taxonomy, self::$associated_post_types, $args);

    }

}
