<?php
/**
 * @package   	OneAll Social Login
 * @copyright 	Copyright 2011-2017 http://www.oneall.com
 * @license   	GNU/GPL 2 or later
  *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307,USA.
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 */
include_once ('../../../../config/config.inc.php');
include_once ('../../../../init.php');
include_once ('../../../../modules/oneallsociallogin/includes/tools.php');

// Otherwise it will not work in various browsers.
header ('Access-Control-Allow-Origin: *');

// Security Check.
if (Tools::getValue ('oasl_action') != '' and (Tools::getValue ('oasl_token') == sha1 (_COOKIE_KEY_ . 'ONEALLSOCIALLOGIN')))
{
	switch (Tools::getValue ('oasl_action'))
	{
		// ****** AUTODETECT CONNECTION HANDLER
		case 'autodetect_api_connection_handler' :
			
			// Check CURL HTTPS - Port 443
			if (oneall_social_login_tools::check_curl (true) === true)
			{
				die ('success_autodetect_api_curl_https');
			}
			// Check CURL HTTP - Port 80
			elseif (oneall_social_login_tools::check_curl (false) === true)
			{
				die ('success_autodetect_api_curl_http');
			}
			// Check FSOCKOPEN HTTPS - Port 443
			elseif (oneall_social_login_tools::check_fsockopen (true) == true)
			{
				die ('success_autodetect_api_fsockopen_https');
			}
			// Check FSOCKOPEN HTTP - Port 80
			elseif (oneall_social_login_tools::check_fsockopen (false) == true)
			{
				die ('success_autodetect_api_fsockopen_http');
			}
			
			// No working handler found
			die ('error_autodetect_api_no_handler');
		break;
		
		// ****** CHECK CONNECTION SETTINGS
		case 'check_api_settings' :
			
			// API Credentials
			$api_subdomain = trim (Tools::getValue ('oasl_api_subdomain'));
			$api_key = trim (Tools::getValue ('oasl_api_key'));
			$api_secret = trim (Tools::getValue ('oasl_api_secret'));
			
			// Full domain entered
			if (preg_match ("/([a-z0-9\-]+)\.api\.oneall\.com/i", $api_subdomain, $matches))
			{
				$api_subdomain = $matches [1];
			}
			
			// API Settings
			$api_connection_port = trim (Tools::getValue ('oasl_api_connection_port'));
			$api_connection_port = ($api_connection_port == 80 ? 80 : 443);
			$api_connection_use_https = ($api_connection_port == 443);
			
			$api_connection_handler = trim (Tools::getValue ('oasl_api_connection_handler'));
			$api_connection_handler = ($api_connection_handler == 'fsockopen' ? 'fsockopen' : 'curl');
			
			// Check if all fields have been filled out
			if (empty ($api_subdomain) or empty ($api_key) or empty ($api_secret))
			{
				die ('error_not_all_fields_filled_out');
			}
			
			// Check FSOCKOPEN
			if ($api_connection_handler == 'fsockopen')
			{
				if (!oneall_social_login_tools::check_fsockopen ($api_connection_use_https))
				{
					die ('error_selected_handler_faulty');
				}
			}
			// Check CURL
			else
			{
				if (!oneall_social_login_tools::check_curl ($api_connection_use_https))
				{
					die ('error_selected_handler_faulty');
				}
			}
			
			// Check subdomain format
			if (!preg_match ("/^[a-z0-9\-]+$/i", $api_subdomain))
			{
				die ('error_subdomain_wrong_syntax');
			}
			
			// Domain
			$api_domain = $api_subdomain . '.api.oneall.com';
			
			// Connection to
			$api_resource_url = ($api_connection_use_https ? 'https' : 'http') . '://' . $api_domain . '/tools/ping.json';
			
			// Get connection details
			$result = oneall_social_login_tools::do_api_request ($api_connection_handler, $api_resource_url, array('api_key' => $api_key, 'api_secret' => $api_secret), 15);
			
			// Parse result
			if (is_object ($result) and property_exists ($result, 'http_code') and property_exists ($result, 'http_data'))
			{
				switch ($result->http_code)
				{
					// Success
					case 200 :
						die ('success');
					break;
					
					// Authentication Error
					case 401 :
						die ('error_authentication_credentials_wrong');
					break;
					
					// Wrong Subdomain
					case 404 :
						die ('error_subdomain_wrong');
					break;
					
					// Other error
					default :
						die ('error_communication');
					break;
				}
			}
			else
			{
				die ('error_communication');
			}
			
			die ('error_unknown_workflow');
		break;
	}
}