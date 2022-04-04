<?php
/**
 * @package       OneAll Social Login
 * @copyright     Copyright 2011-2017 http://www.oneall.com
 * @license       GNU/GPL 2 or later
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

if (!defined('_PS_VERSION_'))
{
    exit();
}

class OneallSocialLogin extends Module
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->name = 'oneallsociallogin';
        $this->tab = 'administration';
        $this->version = '4.7.1';
        $this->author = 'OneAll LLC';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array(
            'min' => '1.7'
        );
        $this->module_key = '2571f9dab09af193a8ca375a09133873';
        $this->secure_key = Tools::encrypt($this->name);

        parent::__construct();

        $this->displayName = $this->l('OneAll Social Login');
        $this->description = $this->l('Professionally developed and free module that allows your users to register and login to PrestaShop with their Social Network account (Twitter, Facebook, LinkedIn, Google ...)');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall Social Login?');

        // This is the first time that the class is used
        if (!Configuration::get('OASL_FIRST_INSTALL'))
        {
            // Setup default values
            Configuration::updateValue('OASL_FIRST_INSTALL', '1');
            Configuration::updateValue('OASL_API_HANDLER', 'curl');
            Configuration::updateValue('OASL_API_PORT', '443');
            Configuration::updateValue('OASL_PROVIDERS', 'facebook,twitter,google,linkedin');
            Configuration::updateValue('OASL_LINK_ACCOUNT_DISABLE', 0);
            Configuration::updateValue('OASL_JS_HOOK_AUTH_DISABLE', 0);
            Configuration::updateValue('OASL_JS_HOOK_LOGIN_DISABLE', 0);
            Configuration::updateValue('OASL_HOOK_LEFT_DISABLE', 0);
            Configuration::updateValue('OASL_HOOK_RIGHT_DISABLE', 0);
            Configuration::updateValue('OASL_DATA_HANDLING', 'verify');
            Configuration::updateValue('OASL_EMAIL_CUSTOMER_DISABLE', '0');
            Configuration::updateValue('OASL_EMAIL_ADMIN_DISABLE', '0');
        }

        // Requires includes
        require_once dirname(__FILE__) . "/includes/tools.php";
        require_once dirname(__FILE__) . "/includes/providers.php";
    }

    /**
     * **************************************************************************
     * Administration Area
     * **************************************************************************
     */

    /**
     * Display Admin Menu
     */
    public function getContent()
    {
        // Compute the form url.
        $form_url = 'index.php?';
        foreach ($_GET as $key => $value)
        {
            if (strtolower($key) != 'submit')
            {
                $form_url .= $key . '=' . $value . '&';
            }
        }
        $form_url = rtrim($form_url, '&');

        // Add external files.
        $this->context = Context::getContext();
        $this->context->controller->addCSS($this->_path . 'views/css/admin.css');
        $this->context->controller->addJS($this->_path . 'views/js/admin.js');

        // This is what is being displayed.
        $html = '';

        // Submit Button Clicked
        if (Tools::isSubmit('submit'))
        {
            // Read API Credentials
            $api_key = trim(Tools::getValue('OASL_API_KEY'));
            $api_password = trim(Tools::getValue('OASL_API_PASSWORD'));
            $api_subdomain = strtolower(trim(Tools::getValue('OASL_API_SUBDOMAIN')));

            // Check for full domain
            if (preg_match("/([a-z0-9\-]+)\.api\.oneall\.com/i", $api_subdomain, $matches))
            {
                $api_subdomain = $matches[1];
            }

            // Read API Connection Settings
            $api_handler = Tools::getValue('OASL_API_HANDLER');
            $api_handler = ($api_handler == 'fsockopen' ? 'fsockopen' : 'curl');
            $api_port = Tools::getValue('OASL_API_PORT');
            $api_port = ($api_port == 80 ? 80 : 443);

            // Read Providers
            $use_provider_keys = array();
            $tmp_provider_keys = Tools::getValue('OASL_PROVIDERS');
            if (is_array($tmp_provider_keys) and count($tmp_provider_keys) > 0)
            {
                foreach ($tmp_provider_keys as $tmp_provider_key)
                {
                    $tmp_provider_key = trim(strtolower($tmp_provider_key));
                    if (oneall_social_login_providers::is_valid_key($tmp_provider_key))
                    {
                        $use_provider_keys[] = $tmp_provider_key;
                    }
                }
            }

            // Hook Left
            $hook_left_disable = (Tools::getValue('OASL_HOOK_LEFT_DISABLE') == 1 ? 1 : 0);

            // Hook Right
            $hook_right_disable = (Tools::getValue('OASL_HOOK_RIGHT_DISABLE') == 1 ? 1 : 0);

            // JavaScript Hook for Authentication
            $js_hook_auth_disable = (Tools::getValue('OASL_JS_HOOK_AUTH_DISABLE') == 1 ? 1 : 0);

            // JavaScript Hook for Login Page
            $js_hook_login_disable = (Tools::getValue('OASL_JS_HOOK_LOGIN_DISABLE') == 1 ? 1 : 0);

            // Settings
            $link_account_disable = (Tools::getValue('OASL_LINK_ACCOUNT_DISABLE') == 1 ? 1 : 0);
            $data_handling = Tools::getValue('OASL_DATA_HANDLING');
            $data_handling = (in_array($data_handling, array(
                'ask',
                'verify',
                'auto'
            )) ? $data_handling : 'verify');

            // Email Settins
            $email_customer_disable = (Tools::getValue('OASL_EMAIL_CUSTOMER_DISABLE') == 1 ? 1 : 0);
            $email_admin_disable = (Tools::getValue('OASL_EMAIL_ADMIN_DISABLE') == 1 ? 1 : 0);

            // Save Values
            Configuration::updateValue('OASL_API_KEY', $api_key);
            Configuration::updateValue('OASL_API_PASSWORD', $api_password);
            Configuration::updateValue('OASL_API_SUBDOMAIN', $api_subdomain);
            Configuration::updateValue('OASL_API_HANDLER', $api_handler);
            Configuration::updateValue('OASL_API_PORT', $api_port);
            Configuration::updateValue('OASL_PROVIDERS', implode(',', $use_provider_keys));
            Configuration::updateValue('OASL_JS_HOOK_AUTH_DISABLE', $js_hook_auth_disable);
            Configuration::updateValue('OASL_JS_HOOK_LOGIN_DISABLE', $js_hook_login_disable);
            Configuration::updateValue('OASL_HOOK_LEFT_DISABLE', $hook_left_disable);
            Configuration::updateValue('OASL_HOOK_RIGHT_DISABLE', $hook_right_disable);
            Configuration::updateValue('OASL_LINK_ACCOUNT_DISABLE', $link_account_disable);
            Configuration::updateValue('OASL_DATA_HANDLING', $data_handling);
            Configuration::updateValue('OASL_EMAIL_ADMIN_DISABLE', $email_admin_disable);
            Configuration::updateValue('OASL_EMAIL_CUSTOMER_DISABLE', $email_customer_disable);
        }

        // Read API Credentials
        $api_key = Configuration::get('OASL_API_KEY');
        $api_password = Configuration::get('OASL_API_PASSWORD');
        $api_subdomain = Configuration::get('OASL_API_SUBDOMAIN');

        // Read API Connection Settings
        $api_handler = Configuration::get('OASL_API_HANDLER');
        $api_handler = ($api_handler == 'fsockopen' ? 'fsockopen' : 'curl');
        $api_port = Configuration::get('OASL_API_PORT');
        $api_port = ($api_port == 80 ? 80 : 443);

        // Hook Left
        $hook_left_disable = Configuration::get('OASL_HOOK_LEFT_DISABLE') == 1 ? 1 : 0;

        // Hook Right
        $hook_right_disable = Configuration::get('OASL_HOOK_RIGHT_DISABLE') == 1 ? 1 : 0;

        // JavaScript Hook for Authentication
        $js_hook_auth_disable = Configuration::get('OASL_JS_HOOK_AUTH_DISABLE') == 1 ? 1 : 0;

        // JavaScript Hook for Login Page
        $js_hook_login_disable = Configuration::get('OASL_JS_HOOK_LOGIN_DISABLE') == 1 ? 1 : 0;

        // Settings
        $link_account_disable = Configuration::get('OASL_LINK_ACCOUNT_DISABLE') == 1 ? 1 : 0;
        $data_handling = Configuration::get('OASL_DATA_HANDLING');
        $data_handling = (in_array($data_handling, array(
            'ask',
            'verify',
            'auto'
        )) ? $data_handling : 'verify');
        $email_customer_disable = Configuration::get('OASL_EMAIL_CUSTOMER_DISABLE') == 1 ? 1 : 0;
        $email_admin_disable = Configuration::get('OASL_EMAIL_ADMIN_DISABLE') == 1 ? 1 : 0;

        // Read providers
        $use_provider_keys = array();
        $tmp_provider_keys = explode(',', Configuration::get('OASL_PROVIDERS'));
        if (is_array($tmp_provider_keys) and count($tmp_provider_keys) > 0)
        {
            foreach ($tmp_provider_keys as $tmp_provider_key)
            {
                if (oneall_social_login_providers::is_valid_key($tmp_provider_key))
                {
                    $use_provider_keys[] = $tmp_provider_key;
                }
            }
        }

        $html .= '
			<h2>' . $this->l('OneAll Social Login') . ' ' . $this->version . '</h2>
			<p>
				' . $this->l('Allow your visitors to comment, login and register with 25+ Social Networks like for example Twitter, Facebook, LinkedIn, Hyves, VKontakte, Google or Yahoo.') . '
				' . $this->l('Draw a larger audience and increase your user engagement and conversion rates in a few simple steps.') . '
			</p>
		<div class="oasl_box">
			<div class="oasl_box_title">' . $this->l('The setup takes only a couple of minutes!') . '</div>
				<p>To be able to use this plugin you first of all need to create a free account at <a href="http://app.oneall.com" target="_blank">http://www.oneall.com</a> and setup a Site.</p>
				<p>After having created your account and setup your Site, please enter the Site settings in the form <strong>API Settings</strong> below.
				<p><a href="http://app.oneall.com" target="_blank" class="button">Click here to setup your free account</a></p>
		</div>
		<form action="' . Tools::safeOutput($form_url) . '" method="post">
			<fieldset>
				<legend>' . $this->l('API Connection') . '</legend>
				<label>' . $this->l('API Connection Handler:') . '</label>
				<div class="margin-form" style="margin-bottom: 20px;">
					<input type="radio" name="OASL_API_HANDLER" id="OASL_API_HANDLER_CURL" value="curl" ' . ($api_handler != 'fsockopen' ? 'checked="checked"' : '') . ' /> ' . $this->l('Use PHP CURL to communicate with the API') . ' <strong>(' . $this->l('Default') . ')</strong>
					<p>' . $this->l('Using CURL is recommended but it might be disabled on some servers.') . '</p><br />
					<input type="radio" name="OASL_API_HANDLER" id="OASL_API_HANDLER_FSOCKOPEN" value="fsockopen" ' . ($api_handler == 'fsockopen' ? 'checked="checked"' : '') . ' /> ' . $this->l('Use PHP FSOCKOPEN to communicate with the API') . '
					<p>' . $this->l('Try using FSOCKOPEN if you encounter any problems with CURL.') . '</p>
				</div>
				<label>' . $this->l('API Connection Port:') . '</label>
				<div class="margin-form">
					<input type="radio" name="OASL_API_PORT" value="443" id="OASL_API_PORT_443" ' . ($api_port != 80 ? 'checked="checked"' : '') . ' /> ' . $this->l('Communication via HTTPS on port 443') . ' <strong>(' . $this->l('Default') . ')</strong>
					<p>' . $this->l('Using port 443 is secure but you might need OpenSSL.') . '</p><br />
					<input type="radio" name="OASL_API_PORT" value="80" id="OASL_API_PORT_80" ' . ($api_port == 80 ? 'checked="checked"' : '') . ' /> ' . $this->l('Communication via HTTP on port 80') . '
					<p>' . $this->l('Using port 80 is a bit faster, does not need OpenSSL but is also less secure.') . '</p>
				</div>
				<div class="margin-form">
					<input type="button" id="OASL_VERIFY_CONNECTION_HANDLER" value="' . $this->l('AutoDetect the best API Connection Settings') . '" class="button" />
				</div>
				<div class="margin-form">
					<span class="oasl_message" id="OASL_VERIFY_CONNECTION_HANDLER_RESULT"></span>
				</div>
			</fieldset>

			<fieldset style="margin-top:20px">
				<div class="oasl_notice">
					<a target="_blank" href="https://app.oneall.com/applications/">' . $this->l('Click here to create and view your API Credentials') . '</a>
				</div>
				<legend>' . $this->l('API Settings') . '</legend>
				<label>' . $this->l('API Subdomain') . ':</label>
				<div class="margin-form">
					<input type="text" name="OASL_API_SUBDOMAIN" id="OASL_API_SUBDOMAIN" size="60" value="' . htmlspecialchars($api_subdomain) . '" />
				</div>
				<label>' . $this->l('API Public Key') . ':</label>
				<div class="margin-form">
					<input type="text" name="OASL_API_KEY" id="OASL_API_KEY" size="60" value="' . htmlspecialchars($api_key) . '" />
				</div>
				<label>' . $this->l('API Private Key') . ':</label>
				<div class="margin-form">
					<input type="text" name="OASL_API_PASSWORD" id="OASL_API_PASSWORD" size="60" value="' . htmlspecialchars($api_password) . '" />
				</div>
				<div class="margin-form">
					<input type="button" id="OASL_VERIFY_CONNECTION_SETTINGS" value="' . $this->l('Verify the API Settings') . '" class="button" />
				</div>
				<div class="margin-form">
					<span class="oasl_message" id="OASL_VERIFY_CONNECTION_SETTINGS_RESULT"></span>
				</div>
			</fieldset>

			<fieldset style="margin-top:20px">
				<legend>' . $this->l('Custom Embedding') . '</legend>
				<div class="oasl_notice">' . $this->l('You can manually embed Social Login by adding this code to a .tpl file of your PrestaShop:') . '</div>
				<label style="width:300px;text-align:left"><code>{$HOOK_OASL_CUSTOM nofilter}</code></label>
				<div style="margin-bottom: 20px;">
						' . $this->l('Simply copy the code and add it to any .tpl file in your /themes directory.') . '
				</div>
			</fieldset>

			<fieldset style="margin-top:20px">
				<legend>' . $this->l('Registration Page') . '</legend>
				<div class="oasl_notice">' . $this->l('Displays Social Login on the create account page of your shop') . '</div>
				<label>' . $this->l('Enable Registration Page Hook?') . '</label>
				<div class="margin-form" style="margin-bottom: 20px;">
					<input type="radio" name="OASL_JS_HOOK_AUTH_DISABLE" id="OASL_JS_HOOK_AUTH_DISABLE_0" value="0" ' . ($js_hook_auth_disable != 1 ? 'checked="checked"' : '') . ' /> ' . $this->l('Enable') . '&nbsp;
					<input type="radio" name="OASL_JS_HOOK_AUTH_DISABLE" id="OASL_JS_HOOK_AUTH_DISABLE_1" value="1" ' . ($js_hook_auth_disable == 1 ? 'checked="checked"' : '') . ' /> ' . $this->l('Disable') . '<br />
				</div>
			</fieldset>

			<fieldset style="margin-top:20px">
				<legend>' . $this->l('Authentication Page') . '</legend>
				<div class="oasl_notice">' . $this->l('Displays Social Login on the sign in page of your shop') . '</div>
				<label>' . $this->l('Enable Authentication Page Hook?') . '</label>
				<div class="margin-form" style="margin-bottom: 20px;">
					<input type="radio" name="OASL_JS_HOOK_LOGIN_DISABLE" id="OASL_JS_HOOK_LOGIN_DISABLE_0" value="0" ' . ($js_hook_login_disable != 1 ? 'checked="checked"' : '') . ' /> ' . $this->l('Enable') . '&nbsp;
					<input type="radio" name="OASL_JS_HOOK_LOGIN_DISABLE" id="OASL_JS_HOOK_LOGIN_DISABLE_1" value="1" ' . ($js_hook_login_disable == 1 ? 'checked="checked"' : '') . ' /> ' . $this->l('Disable') . '<br />
				</div>
			</fieldset>

			<fieldset style="margin-top:20px">
				<legend>' . $this->l('Hook: Left Side') . '</legend>
				<div class="oasl_notice">' . $this->l('Displays Social Login on the left side of your shop') . '</div>
				<label>' . $this->l('Enable Left Side Hook?') . '</label>
				<div class="margin-form" style="margin-bottom: 20px;">
					<input type="radio" name="OASL_HOOK_LEFT_DISABLE" id="OASL_HOOK_LEFT_DISABLE_0" value="0" ' . ($hook_left_disable != 1 ? 'checked="checked"' : '') . ' /> ' . $this->l('Enable') . '&nbsp;
					<input type="radio" name="OASL_HOOK_LEFT_DISABLE" id="OASL_HOOK_LEFT_DISABLE_1" value="1" ' . ($hook_left_disable == 1 ? 'checked="checked"' : '') . ' /> ' . $this->l('Disable') . '<br />
				</div>
			</fieldset>

			<fieldset style="margin-top:20px">
				<legend>' . $this->l('Hook: Right Side') . '</legend>
				<div class="oasl_notice">' . $this->l('Displays Social Login on the right side of your shop') . '</div>
				<label>' . $this->l('Enable Right Side Hook?') . '</label>
				<div class="margin-form" style="margin-bottom: 20px;">
					<input type="radio" name="OASL_HOOK_RIGHT_DISABLE" id="OASL_HOOK_RIGHT_DISABLE_0" value="0" ' . ($hook_right_disable != 1 ? 'checked="checked"' : '') . ' /> ' . $this->l('Enable') . '&nbsp;
					<input type="radio" name="OASL_HOOK_RIGHT_DISABLE" id="OASL_HOOK_RIGHT_DISABLE_1" value="1" ' . ($hook_right_disable == 1 ? 'checked="checked"' : '') . ' /> ' . $this->l('Disable') . '<br />
				</div>
			</fieldset>

			<fieldset style="margin-top:20px">
				<legend>' . $this->l('Settings') . '</legend>

				<label>' . $this->l('Enable Administrator Emails?') . '</label>
				<div class="margin-form" style="margin-bottom: 20px;">
					<input type="radio" name="OASL_EMAIL_ADMIN_DISABLE" id="OASL_EMAIL_ADMIN_DISABLE_0" value="0" ' . ($email_admin_disable != 1 ? 'checked="checked"' : '') . ' /> ' . $this->l('Enable') . ' <strong>(' . $this->l('Default') . ')</strong>
					<p>' . $this->l("Tick to have the module send an email to the administrators for each customer that registers with Social Login") . '</p><br />
					<input type="radio" name="OASL_EMAIL_ADMIN_DISABLE" id="OASL_EMAIL_ADMIN_DISABLE_1" value="1" ' . ($email_admin_disable == 1 ? 'checked="checked"' : '') . ' /> ' . $this->l('Disable') . '
					<p>' . $this->l('Tick to disable the emails send to administrators.') . '</p>
				</div>

				<label>' . $this->l('Enable Customer Emails?') . '</label>
				<div class="margin-form" style="margin-bottom: 20px;">
					<input type="radio" name="OASL_EMAIL_CUSTOMER_DISABLE" id="OASL_EMAIL_CUSTOMER_DISABLE_0" value="0" ' . ($email_customer_disable != 1 ? 'checked="checked"' : '') . ' /> ' . $this->l('Enable') . ' <strong>(' . $this->l('Default') . ')</strong>
					<p>' . $this->l("Tick to have the module send an email to each new customer that registers with Social Login") . '</p><br />
					<input type="radio" name="OASL_EMAIL_CUSTOMER_DISABLE" id="OASL_EMAIL_CUSTOMER_DISABLE_0" value="1" ' . ($email_customer_disable == 1 ? 'checked="checked"' : '') . ' /> ' . $this->l('Disable') . '
					<p>' . $this->l('Tick to disable the emails send to customer.') . '</p>
				</div>


				<label>' . $this->l('Enable Account Linking?') . '</label>
				<div class="margin-form" style="margin-bottom: 20px;">
					<input type="radio" name="OASL_LINK_ACCOUNT_DISABLE" id="OASL_LINK_ACCOUNT_DISABLE_0" value="0" ' . ($link_account_disable != 1 ? 'checked="checked"' : '') . ' /> ' . $this->l('Enable Account Linking') . ' <strong>(' . $this->l('Default') . ')</strong>
					<p>' . $this->l("If the user's social network profile provides a verified email address, the plugin will try to link the profile to an existing account.") . '</p><br />
					<input type="radio" name="OASL_LINK_ACCOUNT_DISABLE" id="OASL_LINK_ACCOUNT_DISABLE_1" value="1" ' . ($link_account_disable == 1 ? 'checked="checked"' : '') . ' /> ' . $this->l('Disable Account Linking') . '
					<p>' . $this->l('Social network profiles will never be linked automatically to existing users.') . '</p>
				</div>

				<label>' . $this->l('User Data Completion?') . '</label>
				<div class="margin-form" style="margin-bottom: 20px;">
					<p>' . $this->l('To create an account PrestaShop requires a firstname, a lastname and an email address. Some social networks (i.e. Twitter) however do not provide this data.') . '</p><br />
					<input type="radio" name="OASL_DATA_HANDLING" id="OASL_DATA_HANDLING_VERIFY" value="verify" ' . (!in_array($data_handling,
            array(
                'auto, ask'
            )) ? 'checked="checked"' : '') . ' /> ' . $this->l('Always ask users to verify their data when they sign up with a social network') . ' <strong>(' . $this->l('Default') . ')</strong>
					<p>' . $this->l('Tick this option to have the users always verify the data retrieved from their social network profiles.') . '</p><br />
					<input type="radio" name="OASL_DATA_HANDLING" id="OASL_DATA_HANDLING_ASK" value="ask" ' . ($data_handling == 'ask' ? 'checked="checked"' : '') . ' /> ' . $this->l('Only ask for missing values') . ' (' . $this->l('Faster Registration') . ')
					<p>' . $this->l('Tick this option to have the users verify their data manually only in case there are required fields that are not provided by the social network.') . '</p><br />
					<input type="radio" name="OASL_DATA_HANDLING" id="OASL_DATA_HANDLING_AUTO" value="auto" ' . ($data_handling == 'auto' ? 'checked="checked"' : '') . ' /> ' . $this->l('Never ask, create placeholders for missing fields') . ' (' . $this->l('Fastests Registration, Not recommended') . ')
					<p>' . $this->l('Tick this option to have the plugin automatically create placeholder values for fields that are not provided by the social network.') . '</p>
				</div>
			</fieldset>


			<fieldset style="margin-top:20px">
					<legend>' . $this->l('Tick the social networks to enable') . '</legend>';

        // Add providers
        $providers = oneall_social_login_providers::get_list();
        foreach ($providers as $key => $data)
        {
            $provider_key = 'OASL_PROVIDER_' . strtoupper($key);

            $html .= '<div class="oasl_provider_row">
									<label for="' . $provider_key . '">' . $data['name'] . '</label>
										<div class="margin-form">
											<div class="oasl_provider_block">
												<span class="oasl_provider oasl_provider_' . $key . '" data-for="' . $provider_key . '"></span>
												<input type="checkbox" id="' . $provider_key . '" name="OASL_PROVIDERS[]" value="' . $key . '" ' . (in_array($key,
                $use_provider_keys) ? 'checked="checked"' : '') . ' />
											</div>
									</div>
								</div>';
        }
        $html .= '</fieldset>';

        $html .= '
			<fieldset style="margin-top:20px">
				<legend>' . $this->l('Save API Settings') . '</legend>
				<div class="margin-form">
					<input type="submit" class="button" name="submit" value="' . $this->l('Save Social Login Settings') . '">
				</div>
			</fieldset>
			<script type="text/javascript">
				<!--
					var OASL_AJAX_TOKEN = \'' . sha1(_COOKIE_KEY_ . 'ONEALLSOCIALLOGIN') . '\';
					var OASL_AJAX_PATH  = \'' . Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/oneallsociallogin/assets/ajax/admin.php\';
				//-->
			</script>
		</form>';

        return $html;
    }

    /**
     * **************************************************************************
     * INSTALLATION
     * **************************************************************************
     */

    /**
     * Moves a hook to the given position
     */
    protected function move_hook_position($hook_name, $position)
    {
        // Get the hook identifier.
        if (($id_hook = Hook::getIdByName($hook_name)) !== false)
        {
            // Load the module.
            if (($module = Module::getInstanceByName($this->name)) !== false)
            {
                // Get the max position of this hook.
                $sql = "SELECT MAX(position) AS position FROM `" . _DB_PREFIX_ . "hook_module` WHERE `id_hook` = '" . intval($id_hook) . "'";
                $result = Db::getInstance()->GetRow($sql);
                if (is_array($result) and isset($result['position']))
                {
                    $way = (($result['position'] >= $position) ? 0 : 1);

                    return $module->updatePosition($id_hook, $way, $position);
                }
            }
        }

        // An error occurred.

        return false;
    }

    /**
     * Returns a list of files to install
     */
    protected function get_files_to_install()
    {
        // Read current language.
        $language = strtolower(trim(strval(Language::getIsoById($this->context->language->id))));

        // All languages to be installed for.
        $languages = array_unique(array('en', $language));

        // Install email templates
        foreach ($languages as $language)
        {
            // Make sure the directory exists
            if (is_dir(_PS_MAIL_DIR_ . $language . '/'))
            {
                // oneallsociallogin_account.html
                $files[] = array(
                    'name' => 'oneallsociallogin_account.html',
                    'source' => _PS_MODULE_DIR_ . $this->name . '/upload/mails/en/',
                    'target' => _PS_MAIL_DIR_ . $language . '/'
                );

                // oneallsociallogin_account.txt
                $files[] = array(
                    'name' => 'oneallsociallogin_account.txt',
                    'source' => _PS_MODULE_DIR_ . $this->name . '/upload/mails/en/',
                    'target' => _PS_MAIL_DIR_ . $language . '/'
                );
            }
        }

        // Done

        return $files;
    }

    /**
     * Returns a list of hooks to install
     */
    protected function get_hooks_to_install()
    {
        return array(

            // Widget
            'displayLeftColumn' => array(
                'pos' => 1
            ),

            // Widget
            'displayRightColumn' => array(
                'pos' => 1
            ),

            // Create account
            'displayCustomerAccountFormTop' => array(
                'pos' => 1
            ),

            // Create account
            'displayCustomerAccountForm' => array(
                'pos' => 1
            ),

            // Login
            'displayCustomerLoginFormAfter' => array(
                'pos' => 1
            ),

            // Callback
            'displayTop' => array(),

            // Library
            'displayHeader' => array()
        );
    }

    /**
     * Install
     */
    public function install()
    {
        // Load context
        $this->context = Context::getContext();

        // Start Installation
        if (!parent::install())
        {
            return false;
        }

        // Store the added files
        $files_added = array();

        // Get files to install.
        $files = $this->get_files_to_install();

        // Install files.
        foreach ($files as $file_data)
        {
            if (is_array($file_data) && !empty($file_data['name']) && !empty($file_data['source']) && !empty($file_data['target']))
            {
                if (!file_exists($file_data['target'] . $file_data['name']))
                {
                    if (!copy($file_data['source'] . $file_data['name'], $file_data['target'] . $file_data['name']))
                    {
                        // Add Error
                        $this->context->controller->errors[] = 'Could not copy the file ' . $file_data['source'] . $file_data['name'] . ' to ' . $file_data['target'] . $file_data['name'];

                        // Rollback the copied files in case of an error
                        foreach ($files_added as $file_added)
                        {
                            if (file_exists($file_added))
                            {
                                @unlink($file_added);
                            }
                        }

                        // Abort Installation.

                        return false;
                    }
                    else
                    {
                        $files_added[] = $file_data['target'] . $file_data['name'];
                    }
                }
            }
        }

        // Install our hooks.
        $hooks = $this->get_hooks_to_install();
        foreach ($hooks as $hook_name => $hook_data)
        {
            if (!$this->registerHook($hook_name))
            {
                $this->context->controller->errors[] = 'Could not register the hook ' . $hook_name;

                return false;
            }
            else
            {
                if (is_array($hook_data) and isset($hook_data['pos']))
                {
                    $this->move_hook_position($hook_name, $hook_data['pos']);
                }
            }
        }

        // Create user_token table.
        $query = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'oasl_user` (`id_oasl_user` int(10) unsigned NOT NULL AUTO_INCREMENT, `id_customer` int(10) unsigned NOT NULL, `user_token` varchar(48) NOT NULL, `date_add` datetime NOT NULL, PRIMARY KEY (`id_oasl_user`))';
        if (!Db::getInstance()->execute($query))
        {
            $this->context->controller->errors[] = "Could not create the table " . _DB_PREFIX_ . "oasl_user";

            return false;
        }

        // Create identity_token table.
        $query = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'oasl_identity` (`id_oasl_identity` int(10) unsigned NOT NULL AUTO_INCREMENT, `id_oasl_user` int(10) unsigned NOT NULL, `identity_token` varchar(48) NOT NULL, `identity_provider` varchar(64) NOT NULL, `num_logins` int(10) unsigned NOT NULL, `date_add` datetime NOT NULL, `date_upd` datetime NOT NULL, PRIMARY KEY (`id_oasl_identity`))';
        if (!Db::getInstance()->execute($query))
        {
            $this->context->controller->errors[] = "Could not create the table " . _DB_PREFIX_ . "oasl_identity";

            return false;
        }

        // Clean class cache.
        $class_cache = _PS_CACHE_DIR_ . 'class_index.php';
        if (file_exists($class_cache))
        {
            @unlink($class_cache);
        }

        // Done

        return true;
    }

    /**
     * Uninstall
     */
    public function uninstall()
    {
        // UnInstall
        if (!parent::uninstall())
        {
            return false;
        }

        // Drop user_token table
        $query = 'DROP table IF EXISTS `' . _DB_PREFIX_ . 'oasl_user`';
        Db::getInstance()->execute($query);

        // Drop identity_token table
        $query = 'DROP table IF EXISTS `' . _DB_PREFIX_ . 'oasl_identity`';
        Db::getInstance()->execute($query);

        // Get files to remove.
        $files = $this->get_files_to_install();

        // Remove files
        foreach ($files as $file_data)
        {
            if (is_array($file_data) && !empty($file_data['name']) && !empty($file_data['source']) && !empty($file_data['target']))
            {
                if (file_exists($file_data['target'] . $file_data['name']))
                {
                    @unlink($file_data['target'] . $file_data['name']);
                }
            }
        }

        return true;
    }

    /**
     * **************************************************************************
     * HOOKS
     * **************************************************************************
     */

    /**
     * Generic Hook
     */
    protected function hookGeneric($params, $target)
    {
        global $smarty;

        // Load context
        $this->context = Context::getContext();

        // Do not display for users that are logged in, or for users that are using our controller
        if (!$this->context->customer->isLogged() and $this->context->controller->php_self != 'oneallsociallogin')
        {
            // Default
            $widget_enable = false;
            $widget_location = 'unspecified';

            // Check what has to be done
            switch ($target)
            {
                // Customer Account Form
                case 'customer_account_form':
                    if (Configuration::get('OASL_JS_HOOK_AUTH_DISABLE') != 1)
                    {
                        $widget_enable = true;
                        $widget_location = $target;
                    }
                    break;

                // Left Column
                case 'left_column':
                    if (Configuration::get('OASL_HOOK_LEFT_DISABLE') != 1)
                    {
                        $widget_enable = true;
                        $widget_location = $target;
                    }
                    break;

                // Right Column
                case 'right_column':
                    if (Configuration::get('OASL_HOOK_RIGHT_DISABLE') != 1)
                    {
                        $widget_enable = true;
                        $widget_location = $target;
                    }
                    break;

                // Right Column
                case 'displayCustomerLoginFormAfter':
                    if (Configuration::get('OASL_JS_HOOK_LOGIN_DISABLE') != 1)
                    {
                        $widget_enable = true;
                        $widget_location = $target;
                    }
                    break;

                case 'custom':
                    $widget_enable = true;
                    $widget_location = $target;
                    break;
            }

            // Enable this widget?
            if ($widget_enable)
            {
                // Read Settings
                $providers = explode(',', trim(Configuration::get('OASL_PROVIDERS')));
                if (is_array($providers) and count($providers) > 0)
                {
                    // Setup placeholders
                    $smarty->assign('oasl_widget_location', $widget_location);
                    $smarty->assign('oasl_widget_rnd', mt_rand(99999, 9999999));
                    $smarty->assign('oasl_widget_callback', oneall_social_login_tools::get_callback_uri(true));
                    $smarty->assign('oasl_widget_css', '');
                    $smarty->assign('oasl_widget_providers', '"' . implode('","', $providers) . '"');

                    // Display template

                    return $this->display(__FILE__, 'oneallsociallogin_widget.tpl');
                }
            }
        }
    }

    /**
     * Hook: Customer Account Form Top
     */
    public function hookDisplayCustomerAccountFormTop($params)
    {
        return $this->hookGeneric($params, 'customer_account_form');
    }

    /**
     * Hook: Left Column
     */
    public function hookDisplayLeftColumn($params)
    {
        return $this->hookGeneric($params, 'left_column');
    }

    /**
     * Hook: Right Column
     */
    public function hookDisplayRightColumn($params)
    {
        return $this->hookGeneric($params, 'right_column');
    }

    /**
     * Hook: Right Column
     */
    public function hookDisplayCustomerLoginFormAfter($params)
    {
        return $this->hookGeneric($params, 'displayCustomerLoginFormAfter');
    }

    /**
     * Hook: Header (Library)
     */
    public function hookDisplayHeader($params)
    {
        // Output
        $output = '';

        // Read API Credentials
        $api_subdomain = Configuration::get('OASL_API_SUBDOMAIN');

        // Subdomain is required
        if (!empty($api_subdomain))
        {
            global $smarty;

            // Add a shortcut.
            $smarty->assign('HOOK_OASL_CUSTOM', $this->hookGeneric($params, 'custom'));

            // For multiple plugin in the same page
            for ($i = 1; $i < 11; $i++)
            {
                $smarty->assign('HOOK_OASL_CUSTOM_' . $i, $this->hookGeneric($params, 'custom'));
            }

            // Add the OneAll Social Library.
            $smarty->assign('oasl_widget_location', 'library');

            $smarty->assign('oasl_auth_disable', (Configuration::get('OASL_JS_HOOK_AUTH_DISABLE') == 1 ? 1 : 0));

            $smarty->assign('oasl_subdomain', $api_subdomain);
            $smarty->assign('oasl_widget_rnd', mt_rand(99999, 9999999));

            // Store providers
            $providers = explode(',', trim(Configuration::get('OASL_PROVIDERS')));
            if (is_array($providers) and count($providers) > 0)
            {
                $smarty->assign('oasl_widget_providers', "'" . implode(",", $providers) . "'");
                $smarty->assign('oasl_widget_providers_array', $providers);
            }

            // Add Our JavaScript/CSS
            $this->context->controller->registerJavascript($this->name . '.js',
                $this->_path . 'views/js/' . $this->name . '.js',
                ['position' => 'bottom', 'priority' => 8000]);
            $this->context->controller->registerStylesheet($this->name . '.css',
                $this->_path . 'views/css/' . $this->name . '.css',
                ['position' => 'bottom', 'priority' => 8000]);

            // Read library
            $output .= $this->display(__FILE__, 'oneallsociallogin_widget.tpl');
        }
        else
        {
            Logger::addLog('OneAll Plugin is misconfigured. Please, fix the plugin administration panel.', 2);
        }

        return $output;
    }

    /**
     * Hook: Page Top (Callback)
     */
    public function hookDisplayTop()
    {
        // Load the context.
        $this->context = Context::getContext();

        // Only of the user is not logged in.
        if ($this->context->customer->isLogged())
        {
            return null;
        }

        // Check for callback arguments.
        if (Tools::getIsset('oa_action') !== true || Tools::getIsset('connection_token') !== true)
        {
            return null;
        }

        // Extract the callback arguments.
        $oa_action = trim(Tools::getValue('oa_action'));
        $connection_token = trim(Tools::getValue('connection_token'));

        // Verify arguments
        if ($oa_action != 'social_login')
        {
            Logger::addLog('OneAll no "social_login" action detected. Skipped.', 1);

            return null;
        }

        if (strlen($connection_token) == 0)
        {
            Logger::addLog('Oneall : connection token is empty ! ', 2);

            return null;
        }

        // Read the API credentials.
        $api_key = Configuration::get('OASL_API_KEY');
        $api_password = Configuration::get('OASL_API_PASSWORD');
        $api_subdomain = Configuration::get('OASL_API_SUBDOMAIN');

        // Read the API settings.
        $api_handler = Configuration::get('OASL_API_HANDLER');
        $api_handler = ($api_handler == 'fsockopen' ? 'fsockopen' : 'curl');
        $api_port = Configuration::get('OASL_API_PORT');
        $api_port = ($api_port == 80 ? 80 : 443);

        // Set API resource uri.
        $api_resource = (($api_port === 443) ? 'https' : 'http') . '://' . $api_subdomain . '.api.oneall.com/connections/' . $connection_token . '.json';

        // Setup API parameters.
        $api_params = array();
        $api_params['api_key'] = $api_key;
        $api_params['api_secret'] = $api_password;

        // Retrieve connection details.
        $result = oneall_social_login_tools::do_api_request($api_handler, $api_resource, $api_params, 15);

        // Parse data.
        $data = oneall_social_login_tools::extract_social_network_profile($result);

        // Handle data.
        if (!is_array($data))
        {
            Logger::addLog('OneAll: Unable to extract data from ' . $api_resource . ' :: ' . json_encode($result), 2);

            return null;
        }

        // Get the customer identifier for a given token.
        $id_customer_tmp = oneall_social_login_tools::get_id_customer_for_user_token($data['user_token']);

        // This customer already exists.
        if (is_numeric($id_customer_tmp))
        {
            // Update the identity.
            oneall_social_login_tools::update_identity_logins($data['identity_token']);

            // Login this customer.
            $id_customer = $id_customer_tmp;
        }
        // This is a new customer.
        else
        {
            // Account linking is enabled.
            if (Configuration::get('OASL_LINK_ACCOUNT_DISABLE') != 1)
            {
                // Account linking only works if the email address has been verified.
                if (!empty($data['user_email']) && $data['user_email_is_verified'] === true)
                {
                    // Try to read the existing customer account.
                    if (($id_customer_tmp = oneall_social_login_tools::get_id_customer_for_email_address($data['user_email'])) !== false)
                    {
                        // Tie the user_token to the customer.
                        if (oneall_social_login_tools::link_tokens_to_id_customer($id_customer_tmp,
                            $data['user_token'],
                            $data['identity_token'],
                            $data['identity_provider']) === true)
                        {
                            // Update the identity.
                            oneall_social_login_tools::update_identity_logins($data['identity_token']);

                            // Login this customer.
                            $id_customer = $id_customer_tmp;
                        }
                    }
                }
            }
        }

        // Create a new user account.
        if (empty($id_customer))
        {
            // Notify the customer ?
            $customer_email_notify = true;

            // How do we have to proceed?
            switch (Configuration::get('OASL_DATA_HANDLING'))
            {
                // Automatic Completion.
                case 'auto':
                    // Generate a random email if none is provided or if it's already taken.
                    if (empty($data['user_email']) or oneall_social_login_tools::get_id_customer_for_email_address($data['user_email']) !== false)
                    {
                        // Generate a random email.
                        $data['user_email'] = oneall_social_login_tools::generate_random_email_address();

                        // But do not send notifications to this email
                        $customer_email_notify = false;
                    }

                    // Generate a lastname if none is provided.
                    if (empty($data['user_last_name']))
                    {
                        $data['user_last_name'] = 'Doe';
                    }

                    // Generate a firstname if none is provided.
                    if (empty($data['user_first_name']))
                    {
                        $data['user_first_name'] = 'John';
                    }
                    break;

                // Ask for manual completion if any of the fields is empty or if the email is already taken.
                case 'ask':
                    if (empty($data['user_email']) || empty($data['user_first_name']) || empty($data['user_last_name']) || oneall_social_login_tools::get_id_customer_for_email_address($data['user_email']) !== false)
                    {
                        // To which URL shall the user return to?
                        $return_to = trim(Tools::getValue('return_to'));
                        if (empty($return_to))
                        {
                            $return_to = oneall_social_login_tools::get_current_url();
                        }

                        // Add to cookie data
                        $data['return_to'] = $return_to;

                        // Save the data in the session.
                        $this->context->cookie->oasl_data = json_encode($data);
                        $this->context->cookie->write();

                        // Redirect to the Social Login registration form
                        header('Location: ' . $this->context->link->getModuleLink($this->name, 'register'));
                        exit();
                    }
                    break;

                // Always verify the fields
                default:

                    // To which URL shall the user return to?
                    $return_to = trim(Tools::getValue('return_to'));
                    if (empty($return_to))
                    {
                        $return_to = oneall_social_login_tools::get_current_url();
                    }

                    // Add to cookie data
                    $data['return_to'] = $return_to;

                    // Save the data in the session.
                    $this->context->cookie->oasl_data = json_encode($data);
                    $this->context->cookie->write();

                    // Redirect to the Social Login registration form
                    header('Location: ' . $this->context->link->getModuleLink($this->name, 'register'));
                    exit();
                    break;
            }

            // Email flags.
            $send_email_to_admin = ((Configuration::get('OASL_EMAIL_ADMIN_DISABLE') != 1) ? true : false);
            $send_email_to_customer = ($customer_email_notify == true and Configuration::get('OASL_EMAIL_CUSTOMER_DISABLE') != 1);

            // Create a new account.
            $id_customer = oneall_social_login_tools::create_customer_from_data($data, $send_email_to_admin,
                $send_email_to_customer);
        }

        // Login.
        if (!empty($id_customer) && oneall_social_login_tools::login_customer($id_customer))
        {
            // To which URL shall the user return to?
            $return_to = trim(Tools::getValue('return_to'));
            if (empty($return_to))
            {
                $return_to = oneall_social_login_tools::get_current_url();
            }

            // Remove Social Login Cookie
            if (isset($this->context->cookie->oasl_data))
            {
                unset($this->context->cookie->oasl_data);
            }

            // Redirect
            Tools::redirect($return_to);
        }

        Logger::addLog('OneAll: Login failed for ' . json_encode($data), 1);
    }
}
