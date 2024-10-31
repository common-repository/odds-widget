<div class="wrap" id="ow-settings">
	<h2>Odds Widget Settings</h2>
	
	<form id="ow-settings" method="post" action="options.php">
		<?php settings_fields( 'ow_settings_group' ); ?>
		<table class="form-table">
			<tr>
				<th>API Key</th>
				<td>
					<input type="text" id="ow_api_key" name="ow_api_key" value="<?php echo esc_attr( get_option('ow_api_key') ); ?>" /> <a class="button-primary generate-api-key">Generate API Key</a> <img src="<?php echo plugins_url(); ?>/odds-widget/images/loading-small.gif" alt="Loading" title="Loading" class="ow-loading" style="display:none;" />
					<h4>Why do I need an API Key?</h4>
					<p class="description">So we can direct your visitors to the bookmaker's website using your affiliate link, we need to know who you are. An API Key allows us to do just that.
					Generating an API Key will send a request to our server and automatically generate a key in the box above.</p>
				</td>
			</tr>
	    </table>
	    
	    <?php submit_button(); ?>

        <p align="right">Plugin Version: <?php print_r($this->ow_plugin_version()); ?></p>
	</form>
</div>