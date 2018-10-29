<?php
namespace App\Controller;

use Platform\Controller;
use App\Order;

class ExampleController extends Controller {

    /**
     * @param string $type
     * @return void
     */
    public function showOrder($id)
    {
        $order = Order::getById($id); //load order based on id in url
        $this->data('order', $order); //pass data through to view
        $this->render('account/order'); //view loaded (eg. /themes/v1/account/order.php)
    }

}
