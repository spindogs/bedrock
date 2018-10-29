<?php
namespace App\Widget;

use Platform\Widget;
use Platform\Form;

class ExampleWidget extends Widget {

    protected static $shortcode = 'contact';
    public $form;

    /**
     * @return void
     */
    public function prepare()
    {
        //Sets Here incase of multisite where translations needed
        $label_name = 'Name';
        $label_email = 'Email';
        $label_phone = 'Contact Number';
        $label_message = 'Message';
        $label_button = 'Submit';

        $this->form = new Form('ContactForm');
        $this->form->placeholder_all = true;
        $this->form->require_all = true;
        $this->form->text('_name', $label_name);
        $this->form->email('_email', $label_email);
        $this->form->phone('_phone', $label_phone);
        $this->form->textarea('_message', $label_message);
        $this->form->submit($label_button);

        if ($this->form->success()) {

            $message = 'Name: '.$this->form->values['_name']."\n";
            $message .= 'Phone: '. $this->form->values['_phone']."\n";
            $message .= 'Email: '.$this->form->values['_email']."\n";
            $message .= 'Message: '.$this->form->values['_message']."\n" ;

            $to = 'developers@spindogs.com';
            $subject = 'Contact Form on NCMH';
            wp_mail($to, $subject, $message);

            //redirects to a page on the site, will need to be setup and id passed in
            $redirect = get_permalink(304);
            wp_redirect($redirect);
            exit;
        }

    }

    /**
     * @return void
     */
    public function display()
    { ?>

        <?php $this->form->display(); ?>

    <?php }

}
