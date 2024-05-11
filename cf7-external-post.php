<?php

/*
 * Plugin Name: Contact Form 7 External POST  
 * Author: Mayur Saptal
 */


add_action('wpcf7_mail_sent', 'send_to_gsxconnector');

function send_to_gsxconnector($contact_form)
{
    // Get form submission data
    $submission = WPCF7_Submission::get_instance();
    // Make sure submission is valid
    if ($submission) {
        $posted_data = $submission->get_posted_data();
        $properties =  $contact_form->get_properties();


        $additional_settings =  array_filter(array_map(
            static function ($setting) {
                $pattern = '/^([a-zA-Z0-9_]+)[\t ]*:(.*)$/';

                if (preg_match($pattern, $setting, $matches)) {
                    $name = trim($matches[1]);
                    $value = trim($matches[2]);

                    if (in_array($value, array('on', 'true'), true)) {
                        $value = true;
                    } elseif (in_array($value, array('off', 'false'), true)) {
                        $value = false;
                    }

                    return array($name, $value);
                }

                return false;
            },
            explode("\n", $properties['additional_settings'])
        ));

        foreach ($additional_settings as $setting) {

            if ($setting[0] == 'external_post_url') {
                $url  =  $setting[1];
                $args = array(
                    'method'      => 'POST',
                    'body'        => $posted_data,
                );
                wp_remote_post($url, $args);
            }
        }
    }
}
