<div class="wrap">
<?php
global $wpdb;

if ($_POST) {

    $current_time = current_time('mysql');
    $new_widget = '';

	// Clean the data so we can safely insert it
	$name = trim($_POST['widgetname']);
	$sport_id = $_POST['sport_id'];
		
	if (!isset($_POST['football_team_ids'])) {
		$_POST['football_team_ids'] = '';
	}
		
	// Football
	if ($_POST['sport_id'] == 1) {
		// Checkboxes
		$border = '0';
		if (isset($_POST['border'])) {
			$border = '1';
		}
		$show_title = '0';
		if (isset($_POST['show_title'])) {
			$show_title = '1';
		}
        $capitalise_heading = '0';
        if (isset($_POST['capitalise_heading'])) {
            $capitalise_heading = '1';
        }
        $bold_heading = '0';
        if (isset($_POST['bold_heading'])) {
            $bold_heading = '1';
        }
        $odds_type = '1';
        if (isset($_POST['odds_type'])) {
            $odds_type = $_POST['odds_type'];
        }
        $odds_underline = '0';
        if (isset($_POST['odds_underline'])) {
            $odds_underline = '1';
        }
        $odds_bold = '0';
        if (isset($_POST['odds_bold'])) {
            $odds_bold = '1';
        }
        $odds_underline_hover = '0';
        if (isset($_POST['odds_underline_hover'])) {
            $odds_underline_hover = '1';
        }
        $alternate_rows = '0';
        if (isset($_POST['alternate_rows'])) {
            $alternate_rows = '1';
        }
        $bookmaker_logos = '0';
        if (isset($_POST['bookmaker_logos'])) {
            $bookmaker_logos = '1';
        }
        $fixture_alignment = '0';
        if (isset($_POST['fixture_alignment'])) {
            $fixture_alignment = '1';
        }

		$data_array = array(
				'leagues' => $_POST['football_league_ids'],
				'teams' => $_POST['football_team_ids'],
				'fixtures_limit' => $_POST['football_fixtures_limit'],
				'odds_type' => $odds_type,
				// Layout
				'layout' => array(
					'type' => $_POST['layout_type'],
					'width' => $_POST['width'],
					'height' => $_POST['height'],
					'background' => $_POST['background'],
					'border' => $border,
					'border_colour' => $_POST['border_colour'],
					'show_title' => $show_title,
					'title_colour' => $_POST['title_colour'],
                    'title_background' => $_POST['title_background'],
                    'title_position' => $_POST['title_position'],
                    'title_padding' => $_POST['title_padding'],
                    'title_size' => $_POST['title_size'],
                    'padding' => $_POST['padding'],
                    'font' => $_POST['font'],
                    'font_size' => $_POST['font_size'],
                    'date_format' => $_POST['date_format'],
                    'odds_heading' => $_POST['odds_heading'],
                    'capitalise_heading' => $capitalise_heading,
                    'bold_heading' => $bold_heading,
                    'heading_colour' => $_POST['heading_colour'],
                    'heading_background_colour' => $_POST['heading_background_colour'],
                    'body_colour' => $_POST['body_colour'],
                    'fixture_alignment' => $fixture_alignment,
                    'versus_text' => $_POST['versus_text'],
                    'odds_colour' => $_POST['odds_colour'],
                    'odds_underline' => $odds_underline,
                    'odds_bold' => $odds_bold,
                    'odds_underline_hover' => $odds_underline_hover,
                    'date_fixture_position' => $_POST['date_fixture_position'],
                    'alternate_rows' => $alternate_rows,
                    'row_colour1' => $_POST['row_colour1'],
                    'row_colour2' => $_POST['row_colour2'],
                    'bookmaker_logos' => $bookmaker_logos,
				)
		);
        $layout = json_encode(array(
            'type' => $_POST['layout_type'],
            'width' => $_POST['width'],
            'height' => $_POST['height']
        ));
	} else {
		$data_array = array();
        $layout = '';

	}
	$widget_data = json_encode($data_array);

	
	// Type is only set if user is editing
	if (isset($_POST['type'])) {
		$widget_id = $_POST['widget_id'];
		$code = $_POST['code'];
		// Update
		$wpdb->update(
			$wpdb->prefix.'ow_widgets',
			array(
				'name' => $name,
				'sport' => $sport_id,
                'layout' => $layout,
				'updated' => $current_time
			),
			array('id' => $widget_id)
		);

	} else {
        $new_widget = true;
        $code = 0; // It's a new widget so there is no code
	}
		
	$content = wp_remote_post('http://api.oddswidget.com/widget.php', array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(),
			'body' => array(
				'code' => $code,
				'api_key' => get_option('ow_api_key'),
				'sport' => $sport_id,
				'name' => $name,
				'widget_data' => $widget_data
			),
			'cookies' => array()
		)
	);
	
	$response = json_decode(wp_remote_retrieve_body($content)); // Get the content

	// If there is a code it means the widget is being built
	if ($response->status == 1 && $response->code != '') {
        // Status is fine, therefore if it's a new widget add it to the local database
        if ($new_widget == true) {
            // Add row to ow_widgets table
            $wpdb->insert(
                $wpdb->prefix . 'ow_widgets',
                array(
                    'name' => $name,
                    'code' => $response->code,
                    'sport' => $sport_id,
                    'layout' => $layout,
                    'status' => 1,
                    'updated' => $current_time,
                    'created' => $current_time
                )
            );
            $widget_id = $wpdb->insert_id; // The id of the last inserted widget
        } else {
            // Update
            $wpdb->update(
                $wpdb->prefix.'ow_widgets',
                array(
                    'code' => $response->code,
                    'updated' => $current_time
                ),
                array('id' => $widget_id)
            );
        }
        ?>

		<div class="ow-please-bear">
			<h2>Please bear with us whilst we build your widget!</h2>
			<img src="<?php echo plugins_url(); ?>/odds-widget/images/loading-large.gif" alt="Loading" title="Loading" />
			<p>Your widget is being built and you will be redirected in 5 seconds!</p>
			<script>
			// Wait 5 seconds for the widget to be built
			window.setTimeout(function(){
				window.location.href = "admin.php?page=ow-preview&id=<?php echo $widget_id; ?>";
			}, 5000);
			</script>
		</div>
	<?php } else { ?>
		<h2>Error</h2>
		<p>There was a problem creating the widget, please try again. If the problem persists please email <a href="support@oddswidget.com">support@oddswidget.com</a> for assistance.
	<?php }
		

} elseif (isset($_GET['id'])) {
	$widget = $wpdb->get_row("SELECT * FROM $wpdb->prefix"."ow_widgets WHERE id = ".esc_sql($_GET['id'])." AND status = 1");
	if (isset($widget)) { ?>
    <h2>Widget: <?php echo $widget->name; ?></h2>

    <?php
    // Get the layout
    $layout = json_decode($widget->layout);
    ?>

    <div style="width:50%;margin-right:20px;">
        <iframe class="<?php echo ($layout->type == 1 ? 'ow-iframe-fluid' : 'ow-iframe-fixed'); ?>" src="<?php echo plugins_url(); ?>/odds-widget/show-widget.php?code=<?php echo $widget->code; ?>&t=<?php echo time(); ?>" style="width:<?php echo ($layout->type == 1 ? '100%' : $layout->width.'px'); ?>;height:<?php echo ($layout->type == 1 ? '10px' : $layout->height.'px'); ?>" scrollbar="<?php echo ($layout->type == 2 ? 'yes' : 'no'); ?>"></iframe>
    </div>

    <p><strong>Created:</strong> <?php echo date('d/m/Y H:i:s', strtotime($widget->created)); ?></p>
    <?php if ($widget->created != $widget->updated) { ?>
    <p><strong>Last Updated:</strong> <?php echo date('d/m/Y H:i:s', strtotime($widget->updated)); ?></p>
    <?php } ?>

    <a href="admin.php?page=ow-new-widget&id=<?php echo $widget->id; ?>" class="button-primary">Edit Widget</a>

    <br /><br /><br />

    <h3>How to display a widget on your website/blog</h3>
    <p>You can either add the widget through the <a href="">widgets page</a> or into your posts/pages by using a shortcode. See the instructions below if you're unsure:</p>

    <table class="widefat ow-instructions-table">
        <tbody>
            <tr>
                <th class="ow-table-col">Add to Post/Page with Shortcode</th>
                <td>
                    <p>If you want to add your Odds Widget to a specific post/page on your site then you need to use a shortcode. The shortcode for this widget is:</p>
                    <code>[oddswidget id="<?php echo $widget->id; ?>"]</code>
                    <p style="margin-top: 0.8em;">Simply copy the code above and then paste it anywhere into your page/post.</p>
                </td>
            </tr>
        </tbody>
    </table>
    <table class="widefat ow-instructions-table">
        <tbody>
            <tr>
                <th class="ow-table-col">Add as Widget</th>
                <td>
                    <p class="ow-warning-msg">This option can only be used if your website/blog supports the use of Wordpress Widgets.</p>
                    <p>Adding the Odds Widget as a widget in your sidebar/footer etc is easy. Providing your Wordpress theme supports widgets (most do) you can add them by clicking the button below:</p>
                    <a href="widgets.php" class="button-primary">Add Widget as a Wordpress Widget</a>
                </td>
            </tr>
        </tbody>
    </table>

	<?php } else {
		echo'<script> window.location="admin.php?page=ow-widgets"; </script> ';
	} ?>
	
<?php	
} else {
	echo'<script> window.location="admin.php?page=ow-widgets"; </script> ';
} ?>
</div>