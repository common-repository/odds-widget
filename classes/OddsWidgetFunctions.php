<?php

/**
 * Class OddsWidgetFunctions
 */
class OddsWidgetFunctions {

    /**
     * Deletes a widget
     */
    public function ow_delete_widget() {
        if (isset($_POST['code']) && is_admin()) {
            global $wpdb;
            // Update ow_widgets table
            $wpdb->update(
                $wpdb->prefix.'ow_widgets',
                array(
                    'status' => 2,
                    'updated' => current_time('mysql')
                ),
                array('code' => esc_sql(trim($_POST['code'])))
            );

            $content = wp_remote_post('http://api.oddswidget.com/deletewidget.php', array(
                    'method' => 'POST',
                    'timeout' => 45,
                    'redirection' => 5,
                    'httpversion' => '1.0',
                    'blocking' => true,
                    'headers' => array(),
                    'body' => array(
                        'api_key' => get_option('ow_api_key'),
                        'code' => $_POST['code']
                    ),
                    'cookies' => array()
                )
            );

            die();
        }
    }

    /**
     * Generate API Key on settings page
     */
    function ow_generate_api_key() {
        if (is_admin()) {
            // If the user have
            if (get_option('ow_bookmakers')) {
                $bookmakers = get_option('ow_bookmakers');
            } else {
                $bookmakers = '';
            }

            $response = wp_remote_post('http://api.oddswidget.com/generatekey.php', array(
                    'method' => 'POST',
                    'timeout' => 45,
                    'redirection' => 5,
                    'httpversion' => '1.0',
                    'blocking' => true,
                    'headers' => array(),
                    'body' => array(
                        'siteurl' => get_site_url(),
                        'gmt_offset' => get_option('gmt_offset'),
                        'bookmakers' => $bookmakers,
                    ),
                    'cookies' => array()
                )
            );

            $response = wp_remote_retrieve_body($response); // Get the content
            echo $response; // Converted to JSON in the jQuery function

            die();
        }
    }

    /**
     * Displays the widget on the frontend of website/blog
     *
     * @param $atts
     * @param string $content
     * @return string
     */
    public function ow_display_shortcode_widget($atts, $content = '') {
        $widget = shortcode_atts( array(
                'id' => 'id',
            ),
            $atts);

        return self::ow_create_widget_html($widget['id']);
    }

    /**
     * Creates the ow_widgets database table required the plugin
     */
    public function ow_create_db_table() {
        global $wpdb;

        $charset_collate = '';

        if ( ! empty( $wpdb->charset ) ) {
            $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
        }

        if ( ! empty( $wpdb->collate ) ) {
            $charset_collate .= " COLLATE {$wpdb->collate}";
        }

        $sql = "CREATE TABLE $wpdb->prefix"."ow_widgets (
		id int(11) NOT NULL AUTO_INCREMENT,
		code varchar(5) NOT NULL,
		name varchar(255) NOT NULL,
		sport int(3) NOT NULL,
		layout text NOT NULL,
		status int(1) NOT NULL,
		updated datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		UNIQUE KEY id (id)
		) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    /**
     * Performs tasks required when the plugin is installed
     */
        public function ow_uninstall_routine() {
        // Drop the ow_widgets table
        global $wpdb;
        $wpdb->query( "DROP TABLE IF EXISTS $wpdb->prefix"."ow_widgets" );
    }

    /**
     * Gets widget data from the local database
     *
     * @param $id
     * @return mixed
     */
    public function get_widget_details($id) {
        global $wpdb;

        $widget = $wpdb->get_row("SELECT * FROM $wpdb->prefix"."ow_widgets WHERE id = $id");

        return $widget;
    }

    /**
     * Create the HTML required to display the widget on the frontend of the website/blog
     *
     * @param $id
     * @return string
     */
    public function ow_create_widget_html($id) {
        $widget = self::get_widget_details($id);
        if ($widget) {
            $layout = json_decode($widget->layout);

            return '<iframe class="' . ($layout->type == 1 ? 'ow-iframe-fluid' : 'ow-iframe-fixed') . '" src="' . plugins_url() . '/odds-widget/show-widget.php?code=' . $widget->code . '&t=' . time() . '" style="width:' . ($layout->type == 1 ? '100%' : $layout->width . 'px') . ';height:' . ($layout->type == 1 ? '10px' : $layout->height . 'px') . '" scrollbar="' . ($layout->type == 2 ? 'yes' : 'no') . '"></iframe>';
        } else {
            return false;
        }
    }

}