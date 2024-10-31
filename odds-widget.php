<?php
/**
 * Plugin Name: Odds Widget
 * Plugin URI: http://www.oddswidget.com
 * Description: Create a fully customisable odds widget containing live odds from the leading bookmakers
 * Version: 1.0.1
 * Author: Odds Widget
 * Author URI: http://www.oddswidget.com
 * License: GPLv2 or later
 */

require_once 'classes/OddsWidgetFunctions.php';
require_once 'classes/OddsWidgetWidgets.php';

/**
 * Class OddsWidget
 */
class OddsWidget {

	public function __construct() {

		register_activation_hook( __FILE__, array($this->ow(), 'ow_create_db_table'));
        register_deactivation_hook( __FILE__, array($this->ow(), 'ow_uninstall_routine'));
		add_action('admin_init', array($this, 'page_init'));
		add_action('admin_menu', array($this, 'build_menu'));
		add_action('admin_init', array($this, 'ow_register_settings'));
		
		add_shortcode('oddswidget', array($this->ow(), 'ow_display_shortcode_widget' ));

        add_action( 'wp_enqueue_scripts', array($this, 'add_frontend_scripts' ));
	}

    /**
     * Build and configure admin menu
     */
    function build_menu(){
    	add_menu_page('Odds Widget', 'Odds Widget', 'manage_options', 'oddswidget', array($this, 'ow_settings' ), plugins_url( 'odds-widget/images/admin-icon.png' ), '99.9' );
        add_submenu_page('oddswidget', 'Widgets', 'Widgets', 'manage_options', 'ow-widgets', array($this, 'ow_widgets'));
        add_submenu_page('oddswidget', 'New Widget', 'New Widget', 'manage_options', 'ow-new-widget', array($this, 'ow_new_widget'));
        add_submenu_page('oddswidget', 'Bookmakers', 'Bookmakers', 'manage_options', 'ow-bookmakers', array($this, 'ow_bookmakers'));
    	add_submenu_page('oddswidget', 'Settings', 'Settings', 'manage_options', 'ow-settings', array($this, 'ow_settings'));
    	add_submenu_page(null, 'Preview', 'Preview', 'manage_options', 'ow-preview', array($this, 'ow_preview'));
    	remove_submenu_page('oddswidget', 'oddswidget');
    }

    /**
     * Register and add settings
     */
    public function page_init() {
    	if (is_admin()) {
    		wp_enqueue_script('odds-widget', plugins_url(). '/odds-widget/js/odds-widget.js', array('jquery'), $this->ow_plugin_version());
    		wp_localize_script('odds-widget', 'ajax_object', array('ajaxurl' => admin_url('admin-ajax.php')));
    		add_action('wp_ajax_ow_delete_widget', array($this->ow(), 'ow_delete_widget'));
    		add_action('wp_ajax_ow_generate_api_key', array($this->ow(), 'ow_generate_api_key'));
    		add_action('admin_ow_create_widget', array($this, 'ow_create_widget'));
            wp_register_style('odds-widget',  plugins_url(). '/odds-widget/css/odds-widget.css', $this->ow_plugin_version());
            wp_enqueue_style( 'odds-widget' );

    		wp_enqueue_script('form-validation', plugins_url(). '/odds-widget/js/jquery.validate.min.js', array('jquery'));
    	}
    }

    /**
     * Add Frontend Scripts
     */
    function add_frontend_scripts() {
        wp_enqueue_script( 'ow-frontend', plugins_url(). '/odds-widget/js/ow-frontend.js', array( 'jquery' ), $this->ow_plugin_version());
    }

    /**
     * Register Settings
     */
    function ow_register_settings() {
    	register_setting( 'ow_settings_group', 'ow_api_key' );
    	register_setting( 'ow_settings_group', 'ow_email_address' );
    	register_setting( 'ow_settings_group', 'ow_email_updates' );
    	register_setting( 'ow_bookmakers_group', 'ow_bookmakers' );
    }

    /**
     * Widgets Page
     */
	public function ow_widgets() {
		require_once plugin_dir_path( __FILE__ ).'/pages/widgets.php';
	}

    /**
     * New Widget Page
     */
	public function ow_new_widget() {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script('build-widget', plugins_url(). '/odds-widget/js/build-widget.js', array( 'jquery' , 'wp-color-picker' ), $this->ow_plugin_version());
		wp_enqueue_script('form-validation', plugins_url(). '/odds-widget/js/jquery.validate.min.js', array('jquery'));
		require_once plugin_dir_path( __FILE__ ).'/pages/new-widget.php';
	}

    /**
     * Bookmakers Page
     */
	public function ow_bookmakers() {
		require_once plugin_dir_path( __FILE__ ).'/pages/bookmakers.php';
	}

    /**
     * Settings Page
     */
	public function ow_settings() {
        require_once plugin_dir_path( __FILE__ ).'/pages/settings.php';
    }

    /**
     * Widget Preview Page
     */
    public function ow_preview() {
		require_once plugin_dir_path( __FILE__ ).'/pages/preview-widget.php';
	}

    /**
     * Allows access to required functions
     *
     * @return OddsWidgetFunctions
     */
    public function ow() {
        return new OddsWidgetFunctions();
    }

    /**
     * The version number of the plugin
     *
     * @return string
     */
    function ow_plugin_version() {
        return '1.0.1';
    }

}

new OddsWidget(); // Run the plugin
new OddsWidgetWidgets(); // Enable the use of Odds Widgets in the Widgets section