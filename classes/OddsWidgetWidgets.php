<?php

require_once 'OddsWidgetFunctions.php';

/**
 * Class OddsWidgetWidgets
 */
class OddsWidgetWidgets extends WP_Widget {

    /**
     * Class constructor
     */
    function __construct() {
        $widget_ops = array(
            'classname' => 'OddsWidgetWidgets',
            'description' => __('An existing Odds Widget')
        );
        parent::__construct('OddsWidgetWidgets', __('Odds Widget'), $widget_ops);
    }

    /**
     * @param $args
     * @param $instance
     */
    function widget( $args, $instance ) {
        $ow = new OddsWidgetFunctions();

        // Check to see if there is a widget set
        if (isset($instance['ow_id'])) {
            $widget_id = $instance['ow_id'];

            echo $args['before_widget'];
            // Work out whether to display widget title or not
            if ( ! empty( $instance['title'] ) ) {
                echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
            }

            // Display the selected widget
            echo $ow->ow_create_widget_html($widget_id);
            echo $args['after_widget'];
        }
    }

    /**
     * @param $new_instance
     * @param $old_instance
     * @return array
     */
    function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['ow_id'] = ( ! empty( $new_instance['ow_id'] ) ) ? strip_tags( $new_instance['ow_id'] ) : '';

        return $instance;
    }

    /**
     * @param $instance
     */
    function form( $instance ) {
        global $wpdb;

        $widget_id = '';
        if (isset($instance['ow_id'])) {
            $widget_id = $instance['ow_id'];
        }

        $title = ! empty( $instance['title'] ) ? $instance['title'] : '';

        $sql = "SELECT * FROM $wpdb->prefix"."ow_widgets WHERE status = 1 ORDER BY id ASC";
        $widgets = $wpdb->get_results($sql);

        if ($widgets) {
            $form = '<p>
<label for="'.$this->get_field_id( 'title' ).'">Title (optional):</label>
<input class="widefat" id="'.$this->get_field_id( 'title' ).'" name="'.$this->get_field_name( 'title' ).'" type="text" value="'.esc_attr( $title ).'">
<br /><br />
<label>Odds Widget:<br />
    <select name='.$this->get_field_name( 'ow_id' ).'>';
            foreach ($widgets as $widget) {
                $selected = '';
                if ($widget_id == $widget->id) {
                    $selected = ' selected="selected"';
                }
                $form .= '<option value="'.$widget->id.'"'.$selected.'>'.$widget->name.'</option>';
            }
            $form .= '</select></label>





</p>';

        } else {
            $form = '<p>You have yet to create a widget, please create an Odds Widget and then return to this page.</p>';
        }

        echo $form;
    }

    /**
     * Allows access to required functions
     *
     * @return OddsWidgetFunctions
     */
    public function ow() {
        return new OddsWidgetFunctions();
    }
}

/**
 *
 */
function ow_register_widgets() {
    register_widget( 'OddsWidgetWidgets' );
}

add_action( 'widgets_init', 'ow_register_widgets' );