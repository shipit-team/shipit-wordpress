<?php
  defined('ABSPATH') or die("Bye bye");
  if (!class_exists('Shipit_Settings_Admin')) {
    class Shipit_Settings_Admin {
      private $settings_api;

      function __construct() {
        $this->settings_api = new Shipit_Settings;
        add_action('admin_init', array($this, 'admin_init'));
        add_action( 'admin_menu', array($this, 'admin_menu') );
      }

      function admin_init() {
        $this->settings_api->set_sections($this->get_settings_sections());
        $this->settings_api->set_fields($this->get_settings_fields());
        $this->settings_api->admin_init();
      }

      function admin_menu() {
        add_menu_page('Settings Shipit', 'Settings Shipit', 'delete_posts', 'settings_api', array($this, 'plugin_page'), plugin_dir_url(__FILE__) . 'images/favicon.png');
      }

      function get_settings_sections() {
        $sections = array(
          array(
            'id'    => 'shipit_user',
            'title' => __('Shipit Settings', 'shipit')
          )
        );
        return $sections;
      }

      function get_settings_fields() {
        $settings_fields = array(
          'shipit_user' => array(
            array(
              'name' => 'shipit_user',
              'label' => __('Email Shipit', 'shipit'),
              'desc' => __('Email Shipit description', 'shipit'),
              'placeholder' => __('Email Shipit placeholder', 'shipit'),
              'type' => 'text',
              'default' => get_option('shipit_user'),
              'sanitize_callback' => 'sanitize_text_field'
            ),
            array(
              'name' => 'shipit_token',
              'label' => __('Token', 'shipit'),
              'desc' => __('Token description', 'shipit'),
              'type' => 'password',
              'default' => get_option('shipit_token')
            )
          )
        );
        return $settings_fields;
      }

      function plugin_page() {
        echo '<div class="wrap">';
        echo'<h2>Inicio de sesion de usuario</h2> Bienvenido a la configuración de plugin Shipit para Woocommerce </div>';
        echo '<form id="token-form" method = "post" action = "options.php"> ';
        $this->settings_api->show_navigation();
        $this->settings_api->show_forms();
        echo '</form>';
        echo '</div>';
        shipit_admin_add_foobar();
      }

      function get_pages() {
        $pages = get_pages();
        $pages_options = array();
        if ($pages) {
          foreach ($pages as $page) {
            $pages_options[$page->ID] = $page->post_title;
          }
        }
        return $pages_options;
      }
    }
  }
?>