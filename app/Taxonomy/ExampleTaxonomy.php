<?php
namespace App\Taxonomy;

class ExampleTaxonomy {

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
        $taxonomy = 'example_category';
        $post_types = array(
            'example'
        );
        $args = array(
            'label' => 'Example categories',
            'public' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'hierarchical' => true,
            'rewrite' => ['slug' => 'example_categories', 'with_front' => false]
        );

        register_taxonomy($taxonomy, $post_types, $args);
    }

}
