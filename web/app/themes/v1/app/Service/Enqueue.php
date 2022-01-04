<?php

namespace App\Service;

class Enqueue 
{
    public static function setup()
    {
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueueScripts'));
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueueStyles'));
        
    }

    public static function enqueueScripts()
    {
        wp_enqueue_script(
            'spindogs',
            get_template_directory_uri() . '/js/main.js',
            array(), 
            filemtime(get_template_directory() . '/js/main.js'),
            true
        );

        // wp_enqueue_script(
        //     'g-fonts',
        //     'https://ajax.googleapis.com/ajax/libs/webfont/1.6.26/webfont.js',
        //     array(),
        //     false,
        //     false
        // );
        // $script = "WebFont.load({google: {families: ['Roboto:400,500,700', 'Inter:400,500,600,700']}});";
        // wp_add_inline_script(
        //     'g-fonts',
        //     $script, 
        //     'after'
        // );
    }

    public static function enqueueStyles()
    {
        // wp_enqueue_style(
        //     'g-fonts',
        //     'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap'
        // );

        wp_enqueue_style(
            'spindogs-css',
            get_template_directory_uri() . "/css/main.css",
            array(),
            filemtime(get_template_directory() . "/css/main.css"),
            'all'
        );
    }
}