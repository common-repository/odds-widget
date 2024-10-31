<div class="wrap">
<?php
// Check to see if they have an API Key
if (get_option('ow_api_key') != '') {
	// Load in the JSON we require
	$leagues = wp_remote_get('http://cdn.oddswidget.com/files/soccer/leagues.json');
	$leagues = json_decode(wp_remote_retrieve_body($leagues));
	
	// Check to see if we're editing
	if (isset($_GET['id'])) {
		global $wpdb;
		$table = $wpdb->prefix.'ow_widgets';
		$edit = $wpdb->get_row("SELECT * FROM $table WHERE id = '".esc_sql($_GET['id'])."'");
		
		if ($edit) {
			// Get raw data from CDN
			$widget_data = wp_remote_get('http://cdn.oddswidget.com/widgets/data/'.$edit->code.'.json');
			$widget_data = json_decode(wp_remote_retrieve_body($widget_data));
		}
	}
	
	?>
	
	<h2><?php echo (isset($_GET['id']) ? 'Edit' : 'New')?> Widget<?php echo (isset($_GET['id']) ? ' "'.$edit->name.'"' : ''); ?></h2>
	
	<?php if (isset($_GET['id'])) { ?>
	<p>You can edit your widget by changing the settings below:</p>
	<?php } else { ?>
	<p>Configure your odds widget using the options below:</p>
	<?php } ?>

	<form id="build-widget" action="admin.php?page=ow-preview" method="post">
		<table class="form-table">
			<tbody>
				<tr>
					<th>
						<label for="widgetname">Widget Name/Title</label>
					</th>
					<td>
						<input type="text" name="widgetname" maxlength="60" value="<?php echo (isset($edit->name) ? $edit->name : ''); ?>" />
						<p class="description">Something descriptive, this is just a reference and the name won't appear anywhere on the widget unless you choose it to.</p>
					</td>
				</tr>
				<tr>
					<th>
						<label for="sport">Sport</label>
					</th>
					<td>
						<select id="sport_id" name="sport_id">
							<option value="">Select Sport</option>
							<option value="1"<?php echo (isset($edit->sport) == 1 ? ' selected="selected"' : ''); ?>>Football (Soccer)</option>
						</select>
					</td>
				</tr>
				<tr class="football-leagues"<?php echo (isset($edit) ? '' : ' style="display:none;"'); ?>>
					<th>
						<label for="football_league_ids">Leagues/Tournaments</label>
					</th>
					<td>
						<select id="football_league_ids" multiple name="football_league_ids[]">
							<?php 
							// Build an array of leagues that the user has selected
							$leagues_array = array();
							if (isset($edit)) {
								foreach ($widget_data->leagues as $l) {
									$leagues_array[] = $l;
								}
							}
							
							foreach ($leagues as $key => $league) { ?>
							<option value="<?php echo $key; ?>"<?php echo ((in_array($key, $leagues_array)) ? ' selected="selected"' : ''); ?>><?php echo $league; ?></option>
							<?php } ?>
						</select>
						<p class="upcoming-fixtures" style="display:none;">Upcoming Fixtures: <span class="uf-count" style="font-weight:bold;"></span></p>
						<p class="description">To select multiple leagues/tournaments hold down CTRL/CMD and click on the leagues/tournaments.</p>
					</td>
				</tr>
				<tr class="football-teams"<?php echo (isset($edit) ? '' : ' style="display:none;"'); ?>>
					<th>
						<label for="football_team_ids">Teams</label>
					</th>
					<td>
						<?php if (isset($edit)) { ?>
						<button type="button" id="edit-soccer-teams">Edit Teams</button>
						<select id="football_team_ids" multiple name="football_team_ids[]" style="display:none">
							<?php // loop through teams
							if ($widget_data->teams) {
								foreach ($widget_data->teams as $team) { ?>
									<option value="<?php echo $team; ?>" selected="selected">Team <?php echo $team; ?></option>
								<?php }
							}
							?>
							<option>Loading...</option>
						</select>
						<p class="description teams-warning" style="display:none;"><strong>Remember if you had teams selected before you must select them again!</strong></p>
						<p class="description">To select multiple teams hold down CTRL/CMD and click on the team.</p>
						<?php } else { ?>
						<select id="football_team_ids" multiple name="football_team_ids[]">
						</select>
						<?php } ?>
					</td>
				</tr>
				<tr class="football-fixtures-limit"<?php echo (isset($edit) ? '' : ' style="display:none;"'); ?>>
					<th>
						<label for="football_fixtures_limit">Max Fixtures Limit</label>
					</th>
					<td>
						<select name="football_fixtures_limit">
							<?php
							$fixtures_limit = 0;
							if (isset($edit)) {
								if (isset($widget_data->fixtures_limit)) {
									$fixtures_limit = $widget_data->fixtures_limit;
								}
							}
							for ($x=20; $x>=1; $x--) { ?>
							<option value="<?php echo $x; ?>"<?php echo ($fixtures_limit == $x ? ' selected="selected"' : ''); ?>><?php echo $x; ?></option>
							<?php } ?>
						</select>
					</td>
				</tr>
                <tr>
                    <td class="ow-nw-section">General Layout</td>
                </tr>
				<tr>
					<th>
						<label for="layout_type">Layout Type</label>
					</th>
					<td>
						<select id="layout_type" name="layout_type">
							<?php
							$fluid_selected = '';
							$fixed_selected = '';
							if (isset($edit)) {
								if ($widget_data->layout->type == 1) {
									$fluid_selected = ' selected="selected"';
								} else {
									$fixed_selected = ' selected="selected"';
								}
							} ?>
							<option value="1"<?php echo $fluid_selected; ?>>Fluid</option>
							<option value="2"<?php echo $fixed_selected; ?>>Fixed</option>
						</select>
						<p class="description">We recommend using a fluid layout, it will fit the available space and resize automatically depending on the content.</p>
					</td>
				</tr>
				<?php // Show width and height when editing
				$layout_size = '';
				if (isset($edit)) {
					if ($widget_data->layout->type == 2) {
						$layout_size = ' style="display:table-row;"';
					}
				} ?>
				<tr class="layout-size"<?php echo $layout_size; ?>>
					<th>
						<label for="width">Width</label>
					</th>
					<td>
						<input type="text" name="width" value="<?php echo (isset($edit) ? $widget_data->layout->width : ''); ?>" maxlength="4" size="4" /> px
					</td>
				</tr>
				<tr class="layout-size"<?php echo $layout_size; ?>>
					<th>
						<label for="height">Height</label>
					</th>
					<td>
						<input type="text" name="height" value="<?php echo (isset($edit) ? $widget_data->layout->height : ''); ?>" maxlength="4" size="4" /> px

					</td>
				</tr>
				<tr>
					<th>
						<label for="background">Background Colour</label>
					</th>
					<td>
						<input type="text" name="background" value="<?php echo (isset($edit) ? $widget_data->layout->background : '#ffffff'); ?>" class="color-picker" data-default-color="#ffffff" />
					</td>
				</tr>
                <tr>
                    <th>
                        <label for="body_colour">Date/Fixture Colour</label>
                    </th>
                    <td>
                        <input type="text" name="body_colour" value="<?php echo (isset($edit) ? $widget_data->layout->body_colour : '#000000'); ?>" class="color-picker" data-default-color="#000000" />
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="team_alignment">Team names and vs on a new line</label>
                    </th>
                    <td>
                        <?php
                        $fixture_alignment_checked = '';
                        if (isset($edit)) {
                            if ($widget_data->layout->fixture_alignment == 1) {
                                $fixture_alignment_checked = ' checked="checked"';
                            }
                        } ?>
                        <input type="checkbox" name="fixture_alignment"<?php echo $fixture_alignment_checked; ?> />
                        <p class="description">This is only recommended if the widget is going to be used in a narrow location (sidebar for example). Selecting this option will also centre the fixture.</p>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="versus_text">Versus Text</label>
                    </th>
                    <td>
                        <select id="versus_text" name="versus_text">
                            <?php
                            $versus_text_array = array(1 => 'v', 'v.', 'vs', 'vs.');
                            foreach ($versus_text_array as $key => $versus) {
                                $versus_selected = '';
                                if ($widget_data->layout->versus_text == $key) $versus_selected = ' selected="selected"';
                                echo '<option value="'.$key.'"'.$versus_selected.'>'.$versus.'</option>';
                            } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="border">Border</label>
                    </th>
                    <td>
                        <?php // Checkbox check
                        $border_checked = '';
                        $border_style = '';
                        if (isset($edit)) {
                            if ($widget_data->layout->border == 1) {
                                $border_checked = ' checked="checked"';
                                $border_style = ' style="display:table-row;"';
                            }
                        } ?>
                        <input type="checkbox" class="border-checkbox" name="border"<?php echo $border_checked; ?> />
                    </td>
                </tr>
                <tr class="border-colour"<?php echo $border_style; ?>>
                    <th>
                        <label for="border_colour">Border Colour</label>
                    </th>
                    <td>
                        <input type="text" name="border_colour" value="<?php echo (isset($edit) ? $widget_data->layout->border_colour : '#000000'); ?>" class="color-picker" data-default-color="#000000" />
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="bookmaker_logos">Show Bookmaker Logos</label>
                    </th>
                    <td>
                        <?php // Checkbox check
                        $bookmaker_logos_checked = '';
                        $odds_disabled = '';
                        if (isset($edit)) {
                            if ($widget_data->layout->bookmaker_logos == 1) {
                                $bookmaker_logos_checked = ' checked="checked"';
                                $odds_disabled = 1;
                            }
                        } ?>
                        <input type="checkbox" class="bookmaker-logos-checkbox" name="bookmaker_logos"<?php echo $bookmaker_logos_checked; ?> />
                        <p class="description">Selecting this option will show the logo instead of the odds.</p>
                    </td>
                </tr>
                <tr>
                    <td class="ow-nw-section">Odds Options</td>
                </tr>
                <tr>
                    <th>
                        <label for="odds_type">Odds Type</label>
                    </th>
                    <td>
                        <select id="odds_type" name="odds_type" class="ow-odds-option"<?php echo ($odds_disabled == 1 ? ' disabled' : ''); ?>>
                            <?php
                            $fraction_selected = '';
                            $decimal_selected = '';
                            if (isset($edit)) {
                                if ($widget_data->odds_type == 0) {
                                    $fraction_selected = ' selected="selected"';
                                } else {
                                    $decimal_selected = ' selected="selected"';
                                }
                            } ?>
                            <option value="0"<?php echo $fraction_selected; ?>>Fractions</option>
                            <option value="1"<?php echo $decimal_selected; ?>>Decimals</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="odds_colour">Odds Colour</label>
                    </th>
                    <td>
                        <input type="text" name="odds_colour" value="<?php echo (isset($edit) ? $widget_data->layout->odds_colour : '#ff0000'); ?>" class="color-picker" data-default-color="#ff0000" />
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="odds_underline">Underline Odds</label>
                    </th>
                    <td>
                        <?php // Underline odds
                        $odds_underline_checked = '';
                        $odds_underline = '';
                        if (isset($edit)) {
                            if ($widget_data->layout->odds_underline == 1) {
                                $odds_underline_checked = ' checked="checked"';
                            }
                        } ?>
                        <input type="checkbox" name="odds_underline"<?php echo $odds_underline_checked; ?> class="ow-odds-option"<?php echo ($odds_disabled == 1 ? ' disabled="disabled"' : ''); ?> />
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="odds_bold">Bold Odds</label>
                    </th>
                    <td>
                        <?php // Bold odds
                        $odds_bold_checked = ' checked="checked"';
                        if (isset($edit)) {
                            if ($widget_data->layout->odds_bold == 1) {
                                $odds_bold_checked = ' checked="checked"';
                            }
                        } ?>
                        <input type="checkbox" name="odds_bold"<?php echo $odds_bold_checked; ?> class="ow-odds-option"<?php echo ($odds_disabled == 1 ? ' disabled="disabled"' : ''); ?> />
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="odds_underline_hover">Underline Odds on Hover</label>
                    </th>
                    <td>
                        <?php // Underline hover odds
                        $odds_underline_hover_checked = ' checked="checked"';
                        if (isset($edit)) {
                            if ($widget_data->layout->odds_underline_hover == 1) {
                                $odds_underline_hover_checked = ' checked="checked"';
                            }
                        } ?>
                        <input type="checkbox" name="odds_underline_hover"<?php echo $odds_underline_hover_checked; ?> class="ow-odds-option"<?php echo ($odds_disabled == 1 ? ' disabled="disabled"' : ''); ?> />
                    </td>
                </tr>
                <tr>
                    <td class="ow-nw-section">Widget Title</td>
                </tr>
				<tr>
					<th>
						<label for="show_title">Show Title on Widget</label>
					</th>
					<td>
						<?php // Checkbox check
						$title_checked = '';
						$title_style = '';
						if (isset($edit)) {
							if ($widget_data->layout->show_title == 1) {
								$title_checked = ' checked="checked"';
								$title_style = ' style="display:table-row;"';
							}
						} ?>
						<input type="checkbox" class="title-checkbox" name="show_title"<?php echo $title_checked; ?> />
					</td>
				</tr>
				<tr class="title-element"<?php echo $title_style; ?>>
					<th>
						<label for="title_colour">Title Colour</label>
					</th>
					<td>
						<input type="text" name="title_colour" value="<?php echo (isset($edit) ? $widget_data->layout->title_colour : '#000000'); ?>" class="color-picker" data-default-color="#000000" />
					</td>
				</tr>
                <tr class="title-element"<?php echo $title_style; ?>>
                    <th>
                        <label for="title_background">Title Background Colour</label>
                    </th>
                    <td>
                        <input type="text" name="title_background" value="<?php echo (isset($edit) ? $widget_data->layout->title_background : '#ffffff'); ?>" class="color-picker" data-default-color="#ffffff" />
                    </td>
                </tr>
                <tr class="title-element"<?php echo $title_style; ?>>
                    <th>
                        <label for="title_position">Title Alignment</label>
                    </th>
                    <td>
                        <select id="title_position" name="title_position">
                            <?php
                            $position_selected = '';
                            $positions = array('Left', 'Center', 'Right');
                            foreach ($positions as $position) {
                                if (isset($edit)) {
                                    $position_selected = '';
                                    if (strtolower($position) == $widget_data->layout->title_position) {
                                        $position_selected = ' selected="selected"';
                                    }
                                }
                                echo '<option value="'.strtolower($position).'"'.$position_selected.'>'.$position.'</option>';
                            } ?>
                        </select>
                    </td>
                </tr>
                <tr class="title-element"<?php echo $title_style; ?>>
                    <th>
                        <label for="title_padding">Title Padding</label>
                    </th>
                    <td>
                        <select id="title_padding" name="title_padding">
                            <?php
                            $title_padding_selected = '';
                            for ($tp = 0; $tp <= 25; $tp++) {
                                if (isset($edit)) {
                                    $title_padding_selected = '';
                                    if ($tp == $widget_data->layout->title_padding) {
                                        $title_padding_selected = ' selected="selected"';
                                    }
                                } else {
                                    // Default is 5px
                                    $title_padding_selected = '';
                                    if ($tp == 5) {
                                        $title_padding_selected = ' selected="selected"';
                                    }
                                }
                                echo '<option value="'.$tp.'"'.$title_padding_selected.'>'.$tp.'px</option>';
                            } ?>
                        </select>
                    </td>
                </tr>
                <tr class="title-element"<?php echo $title_style; ?>>
                    <th>
                        <label for="title_size">Title Font Size</label>
                    </th>
                    <td>
                        <select id="title_size" name="title_size">
                            <?php
                            $title_size_selected = '';
                            for ($f = 10; $f <= 24; $f++) {
                                if (isset($edit)) {
                                    $title_size_selected = '';
                                    if ($f == $widget_data->layout->title_size) {
                                        $title_size_selected = ' selected="selected"';
                                    }
                                }
                                echo '<option value="'.$f.'"'.$title_size_selected.'>'.$f.'px</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="ow-nw-section">Widget Body</td>
                </tr>
                <tr>
                    <th>
                        <label for="padding">Table Padding</label>
                    </th>
                    <td>
                        <select id="padding" name="padding">
                            <?php
                            $padding_selected = '';
                            for ($p = 1; $p <= 20; $p++) {
                                if (isset($edit)) {
                                    $padding_selected = '';
                                    if ($p == $widget_data->layout->padding) {
                                        $padding_selected = ' selected="selected"';
                                    }
                                } else {
                                    // Default is 4px
                                    $padding_selected = '';
                                    if ($p == 4) {
                                        $padding_selected = ' selected="selected"';
                                    }
                                }
                                echo '<option value="'.$p.'"'.$padding_selected.'>'.$p.'px</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="font">Font</label>
                    </th>
                    <td>
                        <select id="font" name="font">
                            <?php
                            $font_selected = '';
                            $fonts = array('Arial', 'Times New Roman', 'Open Sans');
                            foreach ($fonts as $font) {
                                if (isset($edit)) {
                                    $font_selected = '';
                                    if ($font == $widget_data->layout->font) {
                                        $font_selected = ' selected="selected"';
                                    }
                                }
                                echo '<option value="'.$font.'"'.$font_selected.'>'.$font.'</option>';
                            } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="font_size">Font Size</label>
                    </th>
                    <td>
                        <select id="font_size" name="font_size">
                            <?php
                            $font_size_selected = '';
                            for ($f = 10; $f <= 18; $f++) {
                                if (isset($edit)) {
                                    $font_size_selected = '';
                                    if ($f == $widget_data->layout->font_size) {
                                        $font_size_selected = ' selected="selected"';
                                    }
                                } else {
                                    // Default is 11px
                                    $font_size_selected = '';
                                    if ($f == 11) {
                                        $font_size_selected = ' selected="selected"';
                                    }
                                }
                                echo '<option value="'.$f.'"'.$font_size_selected.'>'.$f.'px</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="date_format">Date Format</label>
                    </th>
                    <td>
                        <select id="date_format" name="date_format">
                            <?php
                            $date_format1 = '';
                            $date_format2 = '';
                            $date_format3 = '';
                            $date_format4 = '';
                            $date_format5 = '';
                            $date_format6 = '';
                            if (isset($edit)) {
                                if ($widget_data->layout->date_format == 1) {
                                    $date_format1 = ' selected="selected"';
                                } elseif ($widget_data->layout->date_format == 2) {
                                    $date_format2 = ' selected="selected"';
                                } elseif ($widget_data->layout->date_format == 3) {
                                    $date_format3 = ' selected="selected"';
                                } elseif ($widget_data->layout->date_format == 4) {
                                    $date_format4 = ' selected="selected"';
                                } elseif ($widget_data->layout->date_format == 5) {
                                    $date_format5 = ' selected="selected"';
                                } elseif ($widget_data->layout->date_format == 6) {
                                    $date_format6 = ' selected="selected"';
                                }
                            } ?>
                            <option value="1"<?php echo $date_format1; ?>><?php echo date('D j M'); ?> 20:00</option>
                            <option value="2"<?php echo $date_format2; ?>><?php echo date('d/m/y'); ?> 20:00 (Day/Month/Year)</option>
                            <option value="3"<?php echo $date_format3; ?>><?php echo date('m/d/y'); ?> 20:00 (Month/Day/Year)</option>
                            <option value="4"<?php echo $date_format4; ?>><?php echo date('y/m/d'); ?> 20:00 (Year/Month/Day)</option>
                            <option value="5"<?php echo $date_format5; ?>><?php echo date('m/d'); ?> 20:00 (Month/Day)</option>
                            <option value="6"<?php echo $date_format6; ?>><?php echo date('d/m'); ?> 20:00 (Day/Month)</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="odds_heading">Odds Heading Format</label>
                    </th>
                    <td>
                        <select id="odds_heading" name="odds_heading">
                            <?php
                            $odds_heading1 = '';
                            $odds_heading2 = '';
                            $odds_heading3 = '';
                            if (isset($edit)) {
                                if ($widget_data->layout->odds_heading == 1) {
                                    $odds_heading1 = ' selected="selected"';
                                } elseif ($widget_data->layout->odds_heading == 2) {
                                    $odds_heading2 = ' selected="selected"';
                                } elseif ($widget_data->layout->odds_heading == 3) {
                                    $odds_heading3 = ' selected="selected"';
                                }
                            } ?>
                            <option value="1"<?php echo $odds_heading1; ?>>1/X/2</option>
                            <option value="2"<?php echo $odds_heading2; ?>>W/D/A</option>
                            <option value="3"<?php echo $odds_heading3; ?>>Win/Draw/Away</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="capitalise_heading">Capitalise Odds Heading</label>
                    </th>
                    <td>
                        <?php // Capitalise Heading
                        $capitalise_heading_checked = '';
                        if (isset($edit)) {
                            if ($widget_data->layout->capitalise_heading == 1) {
                                $capitalise_heading_checked = ' checked="checked"';
                            }
                        } ?>
                        <input type="checkbox" name="capitalise_heading"<?php echo $capitalise_heading_checked; ?> />
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="bold_heading">Bold Table Heading</label>
                    </th>
                    <td>
                        <?php // Bold Odds Heading
                        $bold_heading_checked = ' checked="checked"';
                        if (isset($edit)) {
                            if ($widget_data->layout->bold_heading == 1) {
                                $bold_heading_checked = ' checked="checked"';
                            }
                        } ?>
                        <input type="checkbox" name="bold_heading"<?php echo $bold_heading_checked; ?> />
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="heading_colour">Heading Colour</label>
                    </th>
                    <td>
                        <input type="text" name="heading_colour" value="<?php echo (isset($edit) ? $widget_data->layout->heading_colour : '#ffffff'); ?>" class="color-picker" data-default-color="#ffffff" />
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="heading_background_colour">Heading Background Colour</label>
                    </th>
                    <td>
                        <input type="text" name="heading_background_colour" value="<?php echo (isset($edit) ? $widget_data->layout->heading_background_colour : '#005fbf'); ?>" class="color-picker" data-default-color="#005fbf" />
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="date_fixture_position">Date/Fixture Alignment</label>
                    </th>
                    <td>
                        <select id="date_fixture_position" name="date_fixture_position">
                            <?php
                            $date_fixture_position_selected = '';
                            $date_fixture_positions = array('Left', 'Center', 'Right');
                            foreach ($date_fixture_positions as $position) {
                                if (isset($edit)) {
                                    $date_fixture_position_selected = '';
                                    if (strtolower($position) == $widget_data->layout->date_fixture_position) {
                                        $date_fixture_position_selected = ' selected="selected"';
                                    }
                                }
                                echo '<option value="'.strtolower($position).'"'.$date_fixture_position_selected.'>'.$position.'</option>';
                            } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="alternate_rows">Use Alternate Row Coloring</label>
                    </th>
                    <td>
                        <?php // Checkbox check
                        $alternate_rows_checked = ' checked="checked"';
                        $alternate_rows_style = ' style="display:table-row;"';
                        if (isset($edit)) {
                            if ($widget_data->layout->alternate_rows == 1) {
                                $alternate_rows_checked = ' checked="checked"';
                                $alternate_rows_style = ' style="display:table-row;"';
                            }
                        } ?>
                        <input type="checkbox" class="alternate-rows-checkbox" name="alternate_rows"<?php echo $alternate_rows_checked; ?> />
                    </td>
                </tr>
                <tr class="row-colour"<?php echo $alternate_rows_style; ?>>
                    <th>
                        <label for="row_colour1">Row Colour 1</label>
                    </th>
                    <td>
                        <input type="text" name="row_colour1" value="<?php echo (isset($edit) ? $widget_data->layout->row_colour1 : '#ffffff'); ?>" class="color-picker" data-default-color="#ffffff" />
                    </td>
                </tr>
                <tr class="row-colour"<?php echo $alternate_rows_style; ?>>
                    <th>
                        <label for="row_colour1">Row Colour 2</label>
                    </th>
                    <td>
                        <input type="text" name="row_colour2" value="<?php echo (isset($edit) ? $widget_data->layout->row_colour2 : '#aad4ff'); ?>" class="color-picker" data-default-color="#aad4ff" />
                    </td>
                </tr>
			</tbody>
		</table>
		<?php if (isset($edit)) { ?>
		<input type="hidden" name="type" value="edit" />
		<input type="hidden" name="widget_id" value="<?php echo $_GET['id']; ?>" />
		<input type="hidden" name="code" value="<?php echo $edit->code; ?>" />
		<?php } ?>
        <br />
		<input type="submit" class="button-primary" value="Save" />
	</form>
	<?php 
} else {
	include(WP_PLUGIN_DIR.'/odds-widget/includes/api-error.php');
} ?>
</div>