jQuery(document).ready(function($) {
	
	var path = OASL_AJAX_PATH;
	var token = OASL_AJAX_TOKEN;
	
	/* Enable/Disable providers by a click on the logo */
	jQuery('.oasl_provider').click(function(){	
		var id = jQuery(this).attr ('data-for');
		jQuery('#' + id).trigger('click');
	});
	
	/* Autodetect API Connection Handler */
	jQuery('#OASL_VERIFY_CONNECTION_HANDLER').click(function(){	
		
		var message_string;		
		var message_container;
		var is_success;	
		
		var data = {
			'oasl_action' : 'autodetect_api_connection_handler', 
			'oasl_token' : token
		};
	
		message_container = jQuery('#OASL_VERIFY_CONNECTION_HANDLER_RESULT');	
		message_container.removeClass('oasl_success_message oasl_error_message').addClass('oasl_working_message');
		message_container.html('Contacting API - please wait ...');
		
		jQuery.post(path, data, function(response) {				
			
			/* Radio Boxes */
			var radio_curl = jQuery("#OASL_API_HANDLER_CURL");
			var radio_fsockopen = jQuery("#OASL_API_HANDLER_FSOCKOPEN");	
			var radio_443 = jQuery("#OASL_API_PORT_443");
			var radio_80 = jQuery("#OASL_API_PORT_80");
			
			radio_curl.removeAttr("checked");
			radio_fsockopen.removeAttr("checked");
			radio_443.removeAttr("checked");
			radio_80.removeAttr("checked");	
											
			/* CURL detected */
			if (response == 'success_autodetect_api_curl_https')
			{
				is_success = true;
				radio_curl.attr("checked", "checked");				
				radio_443.attr("checked", "checked");	
				message_string = 'Autodetected CURL on port 443 - do not forget to save your changes!';
			}	
			else if (response == 'success_autodetect_api_fsockopen_https')
			{
				is_success = true;
				radio_fsockopen.attr("checked", "checked");				
				radio_443.attr("checked", "checked");	
				message_string = 'Autodetected FSOCKOPEN on port 443 - do not forget to save your changes!';
			}	
			else if (response == 'success_autodetect_api_curl_http')
			{
				is_success = true;
				radio_curl.attr("checked", "checked");				
				radio_80.attr("checked", "checked");	
				message_string = 'Autodetected CURL on port 80 - do not forget to save your changes!';
			}
			else if (response == 'success_autodetect_api_fsockopen_http')
			{
				is_success = true;
				radio_fsockopen.attr("checked", "checked");				
				radio_80.attr("checked", "checked");	
				message_string = 'Autodetected FSOCKOPEN on port 80 - do not forget to save your changes!';
			}
			/* No handler detected */
			else
			{
				is_success = false;
				radio_curl.attr("checked", "checked");					
				message_string = 'Autodetection Error - our <a href="http://docs.oneall.com/plugins/guide/" target="_blank">documentation</a> might help you fix this issue.';
			}
			
			message_container.removeClass('oasl_working_message');
			message_container.html(message_string);
			
			if (is_success){
				message_container.addClass('oasl_success_message');
			} else {
				message_container.addClass('oasl_error_message');
			}						
		});
		return false;	
	});
	
	
	/* Test API Settings */
	jQuery('#OASL_VERIFY_CONNECTION_SETTINGS').click(function() {
		
		var message_string;		
		var message_container;
		var is_success;	
		
		var radio_curl_val = jQuery("#OASL_API_HANDLER_CURL:checked").val();
		var radio_fsockopen_val = jQuery("#OASL_API_HANDLER_FSOCKOPEN:checked").val();	
		var radio_use_port_443 = jQuery("#OASL_API_PORT_443:checked").val();
		var radio_use_port_80 = jQuery("#OASL_API_PORT_80:checked").val();
		
		var subdomain = jQuery('#OASL_API_SUBDOMAIN').val();
		var key = jQuery('#OASL_API_KEY').val();
		var secret = jQuery('#OASL_API_PASSWORD').val();
		var handler = (radio_fsockopen_val == 'fsockopen' ? 'fsockopen' : 'curl');		
		var port = (radio_use_port_80 == 1 ? 80 : 443);

		var data = {
		  'oasl_action' : 'check_api_settings',
			'oasl_token' : token,
		  'oasl_api_subdomain' : subdomain,
		  'oasl_api_key' : key,
		  'oasl_api_secret' : secret,
		  'oasl_api_connection_port': port,
		  'oasl_api_connection_handler' : handler
		};
					
		message_container = jQuery('#OASL_VERIFY_CONNECTION_SETTINGS_RESULT');	
		message_container.removeClass('oasl_success_message oasl_error_message').addClass('oasl_working_message');
		message_container.html('Contacting API - please wait ...');

		jQuery.post(path, data, function(response) {
			is_success = false;			
			
			if (response == 'error_selected_handler_faulty') {
				message_string = 'The API Connection cannot be made, try using the API Connection autodetection';
			} else if (response == 'error_not_all_fields_filled_out') {
				message_string = 'Please fill out each of the fields above'
			} else if (response == 'error_subdomain_wrong') {
				message_string = 'The subdomain does not exist. Have you filled it out correctly?'
			} else if (response == 'error_subdomain_wrong_syntax') {
				message_string = 'The subdomain has a wrong syntax!'
			} else if (response == 'error_communication') {
				message_string = 'Could not contact API. Try using another connection handler'
			} else if (response == 'error_authentication_credentials_wrong') {
				message_string = 'The API credentials are wrong';
			} else if (response == 'success') {
				is_success = true;
				message_string = 'The settings are correct - do not forget to save your changes!';
			} else {
				message_string = 'An unknow error occured! The settings could not be verified.';
			}

			message_container.removeClass('oasl_working_message');
			message_container.html(message_string);
			
			if (is_success){
				message_container.addClass('oasl_success_message');
			} else {
				message_container.addClass('oasl_error_message');
			}
		});
		return false;
	});
});