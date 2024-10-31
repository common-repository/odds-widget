<?php add_thickbox();
if ($_POST) {
	// Loop through each bookie and save as JSON in the options table
	$bookmakers_array = array();
	foreach ($_POST as $bookie => $val) {
		$bookmakers_array[$bookie] = $val;
	}
		
	$bookmakers_array = json_encode($bookmakers_array);
	update_option( 'ow_bookmakers', $bookmakers_array );
		
	// Send data through API to Odds Widget
	$content = wp_remote_post('http://api.oddswidget.com/savebookmakers.php', array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(),
			'body' => array(
				'api_key' => get_option('ow_api_key'),
				'bookmakers' => $bookmakers_array
			),
			'cookies' => array()
		)
	);
		
	$response = json_decode(wp_remote_retrieve_body($content));
}
?>
<div class="wrap" id="ow-bookmakers">
	<h2>Bookmakers</h2>
	
	<?php
	if ($_POST) {
		if ($response->status == 2) { ?>
			<div id="message" class="error">Your details have been saved but you must have a valid API key so we can direct users through your links. Go to the <a href="admin.php?page=ow-settings">settings page</a> to create one.</div>
		<?php } else { ?>
            <div id="message" class="updated">Bookmakers data saved successfully!</div>
        <?php }

	}
	if (get_option('ow_api_key') == '') { ?>
		<div id="message" class="error">You have yet to generate an API Key, you must have one so we can direct users through your links. Go to the <a href="admin.php?page=ow-settings">settings page</a> to create one.</div>
	<?php } ?>
	
	<p>Odds Widget works with most major bookmakers, see list below. New bookmakers are being added regularly so keep checking this page for new ones. To ensure you obtain maximum commission, register with as many of the bookies below as possible and enter your affiliate IDs in the boxes.</p>
	
	<p>If you're not sure what to enter, use the 'Instructions' link where you will find instructions for each bookmaker.</p>
	
	<p><strong>Tip:</strong> The more bookies you have selected and are registered with the better. More Bookies = Better Odds = More $$$</p>
	
	<?php 
	// Load in the bookmakers
	$bookies = wp_remote_get('http://cdn.oddswidget.com/files/bookmakers.json');
	$bookies = json_decode(wp_remote_retrieve_body($bookies));
	settings_fields( 'ow_bookmakers_group' );
	$aff_ids = json_decode(get_option('ow_bookmakers'));
	?>
	<form id="build-widget" action="admin.php?page=ow-bookmakers" method="post">
		<table class="widefat">
			<thead>
				<tr>
					<th>Bookmaker</th>
					<th>Affiliate ID</th>
					<th>Register as Affiliate</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($bookies as $key => $bookie) { ?>
				<tr>
					<td>
						<img src="http://cdn.oddswidget.com/images/bookmakers/icons/<?php echo $bookie->slug; ?>_h.png" alt="<?php echo $bookie->name; ?>" title="<?php echo $bookie->name; ?>" class="ow-bookie-logo" /> <label for="widgetname"><?php echo $bookie->name; ?></label>
					</td>
					<td>
						<input type="text" name="b<?php echo $key; ?>" maxlength="60" value="<?php echo $aff_ids->{'b'.$key}; ?>" /><br />
						<a href="#TB_inline?width=300&height=250&inlineId=modal-window-<?php echo $key; ?>" class="thickbox ow-bookie-instructions">Instructions</a>
					</td>
					<td>Not yet a <?php echo $bookie->name; ?> affiliate?<br /><a href="<?php echo $bookie->join_url; ?>" target="_blank">Click here to sign up</a></td>
				</tr>
				<?php } ?>
				<tr>
					<td>
						<input type="submit" class="button-primary" value="Update" />
					</td>
				</tr>
			</tbody>
		</table>
	</form>
		
	<?php foreach ($bookies as $key => $bookie) { ?>
	<div id="modal-window-<?php echo $key; ?>" style="display:none;">
		<h2><?php echo $bookie->name; ?> Instructions</h2>
        <?php echo $bookie->instructions; ?>
	</div>
	<?php } ?>
</div>