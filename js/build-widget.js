jQuery.noConflict();
jQuery(document).ready(function($) {
	
	// Show the correct sport options based upon what you select on the sports dropdown
	$("#sport_id").change(function() {
		var val = $(this).val();
		if (val == 1) { // Football
			$(".football-leagues").show();
		} else {
			$(".football-leagues, .football-teams").hide();
		}
	});
	
	/************ FOOTBALL/SOCCER ************/
	
	// Show teams from certain leagues based upon your league selection
	$("#football_league_ids").change(function() {
		get_soccer_teams();
	});
	
	// When editing load in the teams names
	$("#edit-soccer-teams").on('click', function(e) {
		if (confirm("Displaying a list of teams means you must select them again, are you sure you want to do this?")) {
			get_soccer_teams();
			$(this).hide();
			$('#football_team_ids, .teams-warning').show();
			e.preventDefault;
	    } else {
	        return false;
	    }
	});
	
	$("#build-widget").validate({
	    rules: {
	        widgetname: "required",
	        sport_id: "required",
	        "football_league_ids[]": "required"
	    },
	    messages: {
	    	widgetname: "Please enter a name for your widget",
	    	sport_id: "Please select a sport",
	    	"football_league_ids[]": "You must select at least 1 league/tournament"
	    }
	});
	
	function get_soccer_teams() {
		var league_ids = $("#football_league_ids").val();
		$('.upcoming-fixtures, .football-fixtures-limit').show();
		
		// Pass league id into our ajax function
		$.ajax({
	 		type: 'GET',
			url: 'http://api.oddswidget.com/soccerteams.php',
			dataType: 'json',
			data: {league_ids: league_ids},
			success: function (data) {
				var team = data.teams;
				var fixture_count = data.fixture_count;
				var options;
				options = '';
				$.each (team, function (id, team_name) {
					options += '<option value="'+id+'">'+team_name+'</option>';
				});
				
				$('.football-teams').show(); // Show the teams select menu
				$('#football_team_ids').html(options); // Update the select menu
				$('.uf-count').html(fixture_count); // Update the fixture count
			}
		});
		
	}
	
	// Show/hide width/height boxes
	$("#layout_type").change(function() {
		var val = $(this).val();
		if (val == 2) {
			$('.layout-size').show();
		} else {
			$('.layout-size').hide();
            $('input[name=width], input[name=height]').val('');
            $('input[name=scrollbar]').prop('checked', false);
		}
	});
	
	// Show/hide border colour option
	$('#build-widget .border-checkbox').change(function() {
		if ($(this).prop("checked")) {
			$('#build-widget .border-colour').show();
		} else {
			$('#build-widget .border-colour').hide();
		}
	});

    // Show/hide alternate row colour option
    $('#build-widget .alternate-rows-checkbox').change(function() {
        if ($(this).prop("checked")) {
            $('#build-widget .row-colour').show();
        } else {
            $('#build-widget .row-colour').hide();
        }
    });
	
	// Show/hide title colour option
	$('#build-widget .title-checkbox').change(function() {
		if ($(this).prop("checked")) {
			$('#build-widget .title-element').show();
		} else {
			$('#build-widget .title-element').hide();
		}
	});

    // Show/hide odds options if use bookmaker logos is selected
    $('#build-widget .bookmaker-logos-checkbox').change(function() {
        if ($(this).prop("checked")) {
            $("#build-widget .ow-odds-option").prop('disabled', true);
        } else {
            $("#build-widget .ow-odds-option").prop('disabled', false);
        }
    });
	
	// Colour picker on build widget page
	$('#build-widget .color-picker').wpColorPicker();

});