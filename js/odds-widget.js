jQuery.noConflict();
jQuery(document).ready(function($) {
	// Delete a widget
	$(".ow-delete").on('click', function(e) {		
		if (confirm("Are you sure you want to delete this widget?")) {
			var widget_id = $(this).attr('data-id');
			var widget_code = $(this).attr('data-code');
			$.ajax({
				url: ajax_object.ajaxurl,
				type: 'POST',
				data: {
					action: 'ow_delete_widget',
					code: widget_code
				},
				success: function(data) {
					$('#widget'+widget_id).hide();
				}
			});
			e.preventDefault;
		} else {
			return false;
		}
	});
	
	// Generate API Key
	$(".generate-api-key").on('click', function() {
		$('.ow-loading').show();
		$.ajax({
			url: ajax_object.ajaxurl,
			type: 'POST',
			data: {
				action: 'ow_generate_api_key'
			},
			success: function(data) {
				$('.ow-loading').hide();
				var response = $.parseJSON(data);
				if (response.status == 1) {
					$('#ow_api_key').val(response.key);
                    alert("API Key Successfully Generated. Don't forget to click save!");
				} else if (response.status == 3) {
					alert("It looks like you're trying to run the plugin on your development environment - please email support@oddswidget.com for an API key.");
					$('#ow_api_key').val();
				} else {
					alert('There has been a problem generating your key - please email support@oddswidget.com for help.');
					$('#ow_api_key').val();
				}
			}
		});
		return false;
	});
	
	$("#ow-settings").validate({
	    rules: {
	        "ow_email_address": "optional"
	    },
	    messages: {
	    	ow_email_address: "Please enter a valid email address or clear the box."
	    }
	});

    // Resize a fluid height iframe
    $(".ow-iframe-fluid").load(function() {
        $(this).height( $(this).contents().find("html").height());
    });
});