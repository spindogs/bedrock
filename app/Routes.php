<?php
namespace App;

use Platform\Route;

class Routes extends Route {

    /**
     * @return void
     */
    public static function register()
    {
        self::match('basket/add/([0-9]+)')->call('BasketController', 'add');
    }

}
