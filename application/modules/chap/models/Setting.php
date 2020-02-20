<?php
/**
 * A wrapper class that finally uses the EAV model to persist data
 * All of the data in here is stored in the WHITELABLE_SETTINGS key 
 * @author Kiran
 *
 */
class Chap_Model_Setting {
    
    /**
     * This should ideally be loaded from some sort of text file
     * but time is of the essence. Refactor later
     * @var array
     */
    protected $_settings    = array(
        'site_title'        => array(
            'description'   => "The title you want to use for the site",
            'type'          => 'string',
            'default'       => 'App Store',
        ),
        'large_logo_name'        => array(
            'description'   => "File name of the large logo that is shown on the top left",
            'type'          => 'string',
            'default'       => 'default.jpg',
        ),
        'small_logo_name'   => array(
            'description'   => "File name of the small logo that is shown on the bottom left",
            'type'          => 'string',
            'default'       => 'default.jpg',
        ),
        'custom_css'        => array(
            'description'   => "Name of the custom CSS file to be used. Needs to be uploaded via admins first",
            'type'          => 'string',
            'default'       => '',
        ),
        'site_description'   => array(
            'description'   => "A small marketing blurb that is shown on the bottom left of the site",
            'type'          => 'text',
            'default'       => '',
        ),
        'site_twitter'        => array(
            'description'   => "Enter your twitter username if you want recent tweets to appear at the bottom",
            'type'          => 'string',
            'default'       => '',
        ),
        'main_menu'         => array(
            'description'   => "Give this in the form of [LINK TEXT] => [LINK HREF]",
            'type'          => 'text',
            'default'       => 'Link Text => http://example.com',
        ),
        'facebook_app_id'   => array(
            'description'   => "The ID of the FB app that is created to handle auth and commenting",
            'type'          => 'string',
            'default'       => ''
        ),
        'enable_comments'   => array(
            'description'   => "Indicates whether you want commenting enabled or not",
            'type'          => 'boolean',
            'default'       => 1
        ),
        'from_email'   => array(
            'description'   => "The email address which will be used to send emails to users",
            'type'          => 'string',
            'default'       => ''
        )
    );
    
    public function getSettings() {
        return $this->_settings;
    }
}