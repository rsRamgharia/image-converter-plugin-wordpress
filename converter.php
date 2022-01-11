<?php

/**
 * Plugin Name: Image Converter
 * Plugin URI: https://www.yourwebsiteurl.com/
 * Description: This is the very first plugin I ever created.
 * Version: 1.0
 * Author: Your Name Here
 * Author URI: http://yourwebsiteurl.com/
 **/


add_action('init', 'register_shortcode');
add_action('wp_enqueue_scripts', 'load_scripts');

function load_scripts()
{
    wp_enqueue_style('dropzone', plugin_dir_url(__FILE__) . 'css/dropzone.min.css');
    // wp_enqueue_style('bootstrap', plugin_dir_url(__FILE__) . 'css/bootstrap.min.css');
    wp_enqueue_style('image-convertor', plugin_dir_url(__FILE__) . 'css/image-converter.css');
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');

    wp_enqueue_script('jquery-js', plugin_dir_url(__FILE__) . 'js/jquery.min.js', array('jquery'), '', false);
    wp_enqueue_script('dropzone-js', plugin_dir_url(__FILE__) . 'js/dropzone.min.js', array(), '', false);
}

function register_shortcode()
{
    add_shortcode('image-converter', 'image_converter');
}

function image_converter()
{
    ob_start();
    require dirname(__FILE__) . '/template/image-convertor.php';
    return ob_get_clean();
}

class ImageConverter
{
    private $image_converter_options;

    public function __construct()
    {
        add_action('admin_menu', array($this, 'image_converter_add_plugin_page'));
        add_action('admin_init', array($this, 'image_converter_page_init'));
    }

    public function image_converter_add_plugin_page()
    {
        add_options_page(
            'Image Converter',
            'Image Converter',
            'manage_options',
            'image-converter',
            array($this, 'image_converter_create_admin_page')
        );
    }

    public function image_converter_create_admin_page()
    {
        $this->image_converter_options = get_option('image_converter_option_name'); ?>

        <div class="wrap">
            <h2>Image Converter</h2>
            <?php // settings_errors(); 
            ?>

            <form method="post" action="options.php">
                <?php
                settings_fields('image_converter_option_group');
                do_settings_sections('image-converter-admin');
                submit_button();
                ?>
            </form>
        </div>
<?php }

    public function image_converter_page_init()
    {
        register_setting(
            'image_converter_option_group',
            'image_converter_option_name',
            array($this, 'image_converter_sanitize')
        );

        add_settings_section(
            'image_converter_setting_section',
            'Settings',
            array($this, 'image_converter_section_info'),
            'image-converter-admin'
        );

        add_settings_field(
            'api_key_0',
            'API Key',
            array($this, 'api_key_0_callback'),
            'image-converter-admin',
            'image_converter_setting_section'
        );
    }

    public function image_converter_sanitize($input)
    {
        $sanitary_values = array();
        if (isset($input['api_key_0'])) {
            $sanitary_values['api_key_0'] = sanitize_text_field($input['api_key_0']);
        }

        return $sanitary_values;
    }

    public function image_converter_section_info()
    {
    }

    public function api_key_0_callback()
    {
        printf(
            '<input class="regular-text" type="text" name="image_converter_option_name[api_key_0]" id="api_key_0" value="%s">',
            isset($this->image_converter_options['api_key_0']) ? esc_attr($this->image_converter_options['api_key_0']) : ''
        );
    }
}
if (is_admin())
    $image_converter = new ImageConverter();
