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

//OneAll Social Login Toolbox
class oneall_social_login_tools
{
    const USER_AGENT = 'SocialLogin/4.7.1 PrestaShop/1.7.x.x (+http://www.oneall.com/)';

    /**
     * Logs a given customer in.
     */
    public static function login_customer($id_customer)
    {
        // Make sure that that the customers exists.
        $sql = "SELECT * FROM `" . _DB_PREFIX_ . "customer` WHERE `id_customer` = '" . pSQL($id_customer) . "'";
        $result = Db::getInstance()->GetRow($sql);

        // The user account has been found!
        if (!empty($result['id_customer']))
        {
            // See => CustomerCore::getByEmail
            $customer = new Customer();
            $customer->id = $result['id_customer'];
            foreach ($result as $key => $value)
            {
                if (key_exists($key, $customer))
                {
                    $customer->{$key} = $value;
                }
            }

            // See => AuthControllerCore::processSubmitLogin
            Hook::exec('actionBeforeAuthentication');

            $context = Context::getContext();
            $context->cookie->id_customer = (int) ($customer->id);
            $context->cookie->customer_lastname = $customer->lastname;
            $context->cookie->customer_firstname = $customer->firstname;
            $context->cookie->logged = 1;
            $context->cookie->is_guest = $customer->isGuest();
            $context->cookie->passwd = $customer->passwd;
            $context->cookie->email = $customer->email;

            // Customer is logged in
            $customer->logged = 1;

            // Add customer to the context
            $context->customer = $customer;

            // Used to init session
            $context->updateCustomer($customer);

            if (Configuration::get('PS_CART_FOLLOWING') && (empty($context->cookie->id_cart) || Cart::getNbProducts($context->cookie->id_cart) == 0) && $id_cart = (int) Cart::lastNoneOrderedCart($context->customer->id))
            {
                $context->cart = new Cart($id_cart);
            }
            else
            {
                $context->cart->id_carrier = 0;
                $context->cart->setDeliveryOption(null);
                $context->cart->id_address_delivery = Address::getFirstCustomerAddressId((int) ($customer->id));
                $context->cart->id_address_invoice = Address::getFirstCustomerAddressId((int) ($customer->id));
            }
            $context->cart->id_customer = (int) $customer->id;
            $context->cart->secure_key = $customer->secure_key;
            $context->cart->save();

            $context->cookie->id_cart = (int) $context->cart->id;
            $context->cookie->update();
            $context->cart->autosetProductAddress();

            Hook::exec('actionAuthentication');

            // Login information have changed, so we check if the cart rules still apply
            CartRule::autoRemoveFromCart($context);
            CartRule::autoAddToCart($context);

            // Customer is now logged in.

            return true;
        }

        // Invalid customer specified.

        return false;
    }

    /**
     * Creates a new customer based on the given data.
     */
    public static function create_customer_from_data(array $data, $send_email_to_admin = false, $send_email_to_customer = false)
    {
        if (is_array($data) && !empty($data['user_token']) && !empty($data['identity_token']))
        {
            $password = Tools::passwdGen();

            // Build customer fields.
            $customer = new CustomerCore();
            $customer->firstname = $data['user_first_name'];
            $customer->lastname = $data['user_last_name'];
            $customer->id_gender = $data['user_gender'];
            $customer->birthday = $data['user_birthdate'];
            $customer->active = true;
            $customer->deleted = false;
            $customer->is_guest = false;
            $customer->passwd = Tools::encrypt($password);

            //Opted for the newsletter?
            if (!empty($data['user_newsletter']))
            {
                $customer->ip_registration_newsletter = pSQL(Tools::getRemoteAddr());
                $customer->newsletter_date_add = pSQL(date('Y-m-d H:i:s'));
                $customer->newsletter = true;
            }
            else
            {
                $customer->newsletter = false;
            }

            // We could get the email.
            if (!empty($data['user_email']))
            {
                // It already exists.
                if (self::get_id_customer_for_email_address($data['user_email']) !== false)
                {
                    // Create a new one.
                    $customer->email = self::generate_random_email_address();
                    $customer->newsletter = false;
                }
                else
                {
                    $customer->email = $data['user_email'];
                }
            }
            // We could not get the email.
            else
            {
                // Create a new one.
                $customer->email = self::generate_random_email_address();
                $customer->newsletter = false;
            }

            // Create a new user account.
            if ($customer->add())
            {
                // Tie the tokens to the newly created member.
                if (self::link_tokens_to_id_customer($customer->id, $data['user_token'], $data['identity_token'], $data['identity_provider']))
                {
                    //Send an email to the customer.
                    if ($send_email_to_customer === true)
                    {
                        self::send_confirmation_to_customer($customer, $password, $data['identity_provider']);
                    }

                    //Send an email to the administrators
                    if ($send_email_to_admin === true)
                    {
                        self::send_confirmation_to_administrators($customer, $data['identity_provider']);
                    }

                    //Process the newletter settings
                    if ($customer->newsletter === true)
                    {
                        if ($module_newsletter = Module::getInstanceByName('blocknewsletter'))
                        {
                            if ($module_newsletter->active)
                            {
                                $module_newsletter->confirmSubscription($customer->email);
                            }
                        }
                    }

                    //Done

                    return $customer->id;
                }
            }
        }

        //Error

        return false;
    }

    /**
     * Generates a random email address
     */
    public static function generate_random_email_address()
    {
        do
        {
            $email_address = md5(uniqid(mt_rand(10000, 99000))) . "@example.com";
        } while (self::get_id_customer_for_email_address($email_address) !== false);

        return $email_address;
    }

    /**
     * Links the user/identity tokens to a customer
     */
    public static function link_tokens_to_id_customer($id_customer, $user_token, $identity_token, $identity_provider)
    {
        // Make sure that that the customers exists.
        $sql = "SELECT `id_customer` FROM `" . _DB_PREFIX_ . "customer` WHERE `id_customer` = '" . pSQL($id_customer) . "'";
        $row_customer = Db::getInstance()->GetRow($sql);

        // The user account has been found!
        if (!empty($row_customer['id_customer']))
        {
            // Read the entry for the given user_token.
            $sql = "SELECT `id_oasl_user`, `id_customer` FROM `" . _DB_PREFIX_ . "oasl_user` WHERE `user_token` = '" . pSQL($user_token) . "'";
            $row_oasl_user = Db::getInstance()->GetRow($sql);

            // The user_token exists but is linked to another user.
            if (!empty($row_oasl_user['id_oasl_user']) and $row_oasl_user['id_customer'] != $id_customer)
            {
                // Delete the wrongly linked user_token.
                $sql = "DELETE FROM `" . _DB_PREFIX_ . "oasl_user` WHERE `user_token` = '" . pSQL($user_token) . "' LIMIT 1";
                $result = Db::getInstance()->execute($sql);

                // Delete the wrongly linked identity_token.
                $sql = "DELETE FROM `" . _DB_PREFIX_ . "oasl_identity` WHERE `id_oasl_user` = '" . pSQL($row_oasl_user['id_oasl_user']) . "'";
                $result = Db::getInstance()->execute($sql);

                // Reset the identifier to create a new one.
                $row_oasl_user['id_oasl_user'] = null;
            }

            // The user_token either does not exist or has been reset.
            if (empty($row_oasl_user['id_oasl_user']))
            {
                // Add new link.
                $sql = "INSERT INTO `" . _DB_PREFIX_ . "oasl_user` SET `id_customer` = '" . pSQL($id_customer) . "', `user_token` = '" . pSQL($user_token) . "', `date_add`='" . date('Y-m-d H:i:s') . "'";
                $result = Db::getInstance()->execute($sql);

                // Identifier of the newly created user_token entry.
                $row_oasl_user['id_oasl_user'] = Db::getInstance()->Insert_ID();
            }

            // Read the entry for the given identity_token.
            $sql = "SELECT `id_oasl_identity`, `id_oasl_user`, `identity_token` FROM `" . _DB_PREFIX_ . "oasl_identity` WHERE `identity_token` = '" . pSQL($identity_token) . "'";
            $row_oasl_identity = Db::getInstance()->GetRow($sql);

            // The identity_token exists but is linked to another user_token.
            if (!empty($row_oasl_identity['id_oasl_identity']) and $row_oasl_identity['id_oasl_user'] != $row_oasl_user['id_oasl_user'])
            {
                // Delete the wrongly linked user_token.
                $sql = "DELETE FROM `" . _DB_PREFIX_ . "oasl_identity` WHERE `id_oasl_identity` = '" . pSQL($row_oasl_identity['id_oasl_identity']) . "' LIMIT 1";
                $result = Db::getInstance()->execute($sql);

                // Reset the identifier to create a new one.
                $row_oasl_identity['id_oasl_identity'] = null;
            }

            // The identity_token either does not exist or has been reset.
            if (empty($row_oasl_identity['id_oasl_identity']))
            {
                // Add new link.
                $sql = "INSERT INTO `" . _DB_PREFIX_ . "oasl_identity` SET `id_oasl_user` = '" . pSQL($row_oasl_user['id_oasl_user']) . "', `identity_token` = '" . pSQL($identity_token) . "', `identity_provider` = '" . pSQL($identity_provider) . "', `num_logins`=1, `date_add`='" . date('Y-m-d H:i:s') . "', `date_upd`='" . date('Y-m-d H:i:s') . "'";
                $result = Db::getInstance()->execute($sql);

                // Identifier of the newly created identity_token entry.
                $row_oasl_identity['id_oasl_identity'] = Db::getInstance()->Insert_ID();
            }

            // Done.

            return true;
        }

        // An error occured.

        return false;
    }

    /**
     * Updates the number of logins for an identity_token.
     */
    public static function update_identity_logins($identity_token)
    {
        // Make sure it is not empty.
        $identity_token = trim($identity_token);
        if (strlen($identity_token) == 0)
        {
            return false;
        }

        //Update
        $sql = "UPDATE `" . _DB_PREFIX_ . "oasl_identity` SET `num_logins`=`num_logins`+1, `date_upd`='" . date('Y-m-d H:i:s') . "' WHERE `identity_token`='" . pSQL($identity_token) . "' LIMIT 1";
        $result = Db::getInstance()->execute($sql);

        //Done

        return $result;
    }

    /**
     * Sends a confirmation to the administrators.
     */
    public static function send_confirmation_to_administrators($customer, $identity_provider)
    {
        // Get the language identifier.
        $context = Context::getContext();
        $language_id = $context->language->id;

        // Setup the mail title.
        $mail_title = "A new customer has registered with Social Login";

        // Setup the mail vars.
        $mail_vars = array();
        $mail_vars['{message}'] = "Customer Details:<br />";
        $mail_vars['{message}'] .= " Identifier: " . $customer->id . "<br />";
        $mail_vars['{message}'] .= " First name: " . $customer->firstname . "<br />";
        $mail_vars['{message}'] .= " Last name: " . $customer->lastname . "<br />";
        $mail_vars['{message}'] .= " Email: " . $customer->email . "<br />";
        $mail_vars['{message}'] .= " Signed up with: " . $identity_provider . "<br />";
        $mail_vars['{email}'] = $customer->email;

        //Read the first employe - should be the board owner
        $employees = Employee::getEmployeesByProfile(_PS_ADMIN_PROFILE_, true);
        foreach ($employees as $employee)
        {
            //Employee Details
            $mail_vars['{firstname}'] = $employee['firstname'];
            $mail_vars['{lastname}'] = $employee['lastname'];

            //Send Mail
            @Mail::Send($language_id, 'contact', $mail_title, $mail_vars, $employee['email'], $employee['firstname'] . ' ' . $employee['lastname']);
        }

        //Done

        return true;
    }

    /**
     * Sends a confirmation to the given customer.
     */
    public static function send_confirmation_to_customer($customer, $password, $identity_provider)
    {
        // Get the language identifier.
        $context = Context::getContext();
        $language_id = $context->language->id;

        // Setup the mail vars.
        $mail_vars = array();
        $mail_vars['{firstname}'] = $customer->firstname;
        $mail_vars['{lastname}'] = $customer->lastname;
        $mail_vars['{email}'] = $customer->email;
        $mail_vars['{passwd}'] = $password;
        $mail_vars['{identity_provider}'] = $identity_provider;

        // Send mail to customer.

        return @Mail::Send($language_id, 'oneallsociallogin_account', Mail::l('Welcome!'), $mail_vars, $customer->email, $customer->firstname . ' ' . $customer->lastname);
    }

    /**
     * Returns the customer identifier for a given email address.
     */
    public static function get_id_customer_for_email_address($email_address)
    {
        // Make sure it is not empty.
        $email_address = trim($email_address);
        if (strlen($email_address) == 0)
        {
            return false;
        }

        // Check if the user account exists.
        $sql = "SELECT * FROM `" . _DB_PREFIX_ . "customer` WHERE `email` = '" . pSQL($email_address) . "' AND `deleted` = 0 AND `is_guest` = 0";
        $result = Db::getInstance()->getRow($sql);

        // Either return the id_customer or false if none has been found.

        return (!empty($result['id_customer']) ? $result['id_customer'] : false);
    }

    /**
     * Returns the customer identifier for a given token.
     */
    public static function get_id_customer_for_user_token($user_token)
    {
        // Make sure it is not empty.
        $user_token = trim($user_token);
        if (strlen($user_token) == 0)
        {
            return false;
        }

        // Read the id_customer for this user_token.
        $sql = "SELECT `id_oasl_user`, `id_customer` FROM `" . _DB_PREFIX_ . "oasl_user` WHERE `user_token` = '" . pSQL($user_token) . "'";
        $row_oasl_user = Db::getInstance()->GetRow($sql);

        // We have found an entry for this customers.
        if (!empty($row_oasl_user['id_customer']))
        {
            $id_customer = intval($row_oasl_user['id_customer']);
            $id_oasl_user = intval($row_oasl_user['id_oasl_user']);

            // Check if the user account exists.
            $sql = "SELECT `id_customer` FROM `" . _DB_PREFIX_ . "customer` WHERE `id_customer` = '" . pSQL($id_customer) . "'";
            $row_customer = Db::getInstance()->GetRow($sql);

            // The user account exists, return it's identifier.
            if (!empty($row_customer['id_customer']))
            {
                return $row_customer['id_customer'];
            }

            // Delete the wrongly linked user_token.
            $sql = "DELETE FROM `" . _DB_PREFIX_ . "oasl_user` WHERE `user_token` = '" . pSQL($user_token) . "' LIMIT 1";
            $result = Db::getInstance()->execute($sql);

            // Delete the wrongly linked identity_token.
            $sql = "DELETE FROM `" . _DB_PREFIX_ . "oasl_identity` WHERE `id_oasl_user` = '" . pSQL($id_oasl_user) . "'";
            $result = Db::getInstance()->execute($sql);
        }

        // No entry found.

        return false;
    }

    /**
     * Extracts the social network data from a result-set returned by the OneAll API.
     */
    public static function extract_social_network_profile($social_data)
    {
        // Check API result.
        if (is_object($social_data) && property_exists($social_data, 'http_code') && $social_data->http_code == 200 && property_exists($social_data, 'http_data'))
        {
            // Decode the social network profile Data.
            $social_data = json_decode($social_data->http_data);

            // Make sur that the data has beeen decoded properly
            if (is_object($social_data))
            {
                // Container for user data
                $data = array();

                // Parse Social Profile Data.
                $identity = $social_data->response->result->data->user->identity;

                $data['identity_provider'] = $identity->source->name;
                $data['identity_token'] = $identity->identity_token;
                $data['user_token'] = $social_data->response->result->data->user->user_token;
                $data['user_first_name'] = !empty($identity->name->givenName) ? $identity->name->givenName : '';
                $data['user_last_name'] = !empty($identity->name->familyName) ? $identity->name->familyName : '';
                $data['user_location'] = !empty($identity->currentLocation) ? $identity->currentLocation : '';
                $data['user_constructed_name'] = trim($data['user_first_name'] . ' ' . $data['user_last_name']);
                $data['user_picture'] = !empty($identity->pictureUrl) ? $identity->pictureUrl : '';
                $data['user_thumbnail'] = !empty($identity->thumbnailUrl) ? $identity->thumbnailUrl : '';
                $data['user_about_me'] = !empty($identity->aboutMe) ? $identity->aboutMe : '';

                // Default birthdate
                $data['user_birthdate'] = '0000-00-00';

                // Birthdate
                if (!empty($identity->birthday) && preg_match('/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})$/', $identity->birthday, $matches))
                {
                    //setup birthdate
                    $birthdate = $matches[3];
                    $birthdate .= '-' . str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                    $birthdate .= '-' . str_pad($matches[1], 2, '0', STR_PAD_LEFT);

                    //PrestaShop birthday checker
                    if (Validate::isBirthDate($birthdate))
                    {
                        $data['user_birthdate'] = $birthdate;
                    }
                }

                // Accounts
                if (isset($identity->accounts) and is_array($identity->accounts))
                {
                    $data['accounts'] = array();

                    foreach ($identity->accounts as $identity_account)
                    {
                        $properties = get_object_vars($identity_account);
                        if (is_array($properties) and count($properties) > 0)
                        {
                            $account = array();
                            foreach ($properties as $property => $property_value)
                            {
                                $account[$property] = $property_value;
                            }
                            $data['accounts'][] = $account;
                        }
                    }
                }

                // Fullname.
                if (!empty($identity->name->formatted))
                {
                    $data['user_full_name'] = $identity->name->formatted;
                }
                elseif (!empty($identity->name->displayName))
                {
                    $data['user_full_name'] = $identity->name->displayName;
                }
                else
                {
                    $data['user_full_name'] = $data['user_constructed_name'];
                }

                // Preferred Username.
                if (!empty($identity->preferredUsername))
                {
                    $data['user_login'] = $identity->preferredUsername;
                }
                elseif (!empty($identity->displayName))
                {
                    $data['user_login'] = $identity->displayName;
                }
                else
                {
                    $data['user_login'] = $data['user_full_name'];
                }

                // Email Address.
                $data['user_email'] = '';
                if (property_exists($identity, 'emails') && is_array($identity->emails))
                {
                    $data['user_email_is_verified'] = false;
                    while ($data['user_email_is_verified'] !== true && (list(, $obj) = each($identity->emails)))
                    {
                        $data['user_email'] = $obj->value;
                        $data['user_email_is_verified'] = !empty($obj->is_verified);
                    }
                }

                // Website/Homepage.
                $data['user_website'] = '';
                if (!empty($identity->profileUrl))
                {
                    $data['user_website'] = $identity->profileUrl;
                }
                elseif (!empty($identity->urls[0]->value))
                {
                    $data['user_website'] = $identity->urls[0]->value;
                }

                // Gender
                $data['user_gender'] = 0;
                if (!empty($identity->gender))
                {
                    switch ($identity->gender)
                    {
                        case 'male':
                            $data['user_gender'] = 1;
                            break;

                        case 'female':
                            $data['user_gender'] = 2;
                            break;
                    }
                }

                return $data;
            }
        }

        return false;
    }

    /**
     * Send an API request by using the given handler
     */
    public static function do_api_request($handler, $url, $options = array(), $timeout = 15)
    {
        //FSOCKOPEN
        if ($handler == 'fsockopen')
        {
            return self::do_fsockopen_request($url, $options, $timeout);
        }
        //CURL
        else
        {
            return self::do_curl_request($url, $options, $timeout);
        }
    }

    /**
     * Check if fsockopen can be used
     */
    public static function check_fsockopen($secure = true)
    {
        $result = self::do_fsockopen_request(($secure ? 'https' : 'http') . '://www.oneall.com/ping.html');
        if (is_object($result) and property_exists($result, 'http_code') and $result->http_code == 200)
        {
            if (property_exists($result, 'http_data'))
            {
                if (strtolower($result->http_data) == 'ok')
                {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if CURL can be used
     */
    public static function check_curl($secure = true)
    {
        if (in_array('curl', get_loaded_extensions()) and function_exists('curl_exec'))
        {
            $result = self::do_curl_request(($secure ? 'https' : 'http') . '://www.oneall.com/ping.html');
            if (is_object($result) and property_exists($result, 'http_code') and $result->http_code == 200)
            {
                if (property_exists($result, 'http_data'))
                {
                    if (strtolower($result->http_data) == 'ok')
                    {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Sends a CURL request
     */
    public static function do_curl_request($url, $options = array(), $timeout = 15)
    {
        //Store the result
        $result = new stdClass();

        //Send request
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_VERBOSE, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_USERAGENT, self::USER_AGENT);

        // BASIC AUTH?
        if (isset($options['api_key']) and isset($options['api_secret']))
        {
            curl_setopt($curl, CURLOPT_USERPWD, $options['api_key'] . ":" . $options['api_secret']);
        }

        //Make request
        if (($http_data = curl_exec($curl)) !== false)
        {
            $result->http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $result->http_data = $http_data;
            $result->http_error = null;
        }
        else
        {
            $result->http_code = -1;
            $result->http_data = null;
            $result->http_error = curl_error($curl);
        }

        //Done

        return $result;
    }

    /**
     * Sends an fsockopen request
     */
    public static function do_fsockopen_request($url, $options = array(), $timeout = 15)
    {
        //Store the result
        $result = new stdClass();

        //Make that this is a valid URL
        if (($uri = parse_url($url)) == false)
        {
            $result->http_code = -1;
            $result->http_data = null;
            $result->http_error = 'invalid_uri';

            return $result;
        }

        //Make sure we can handle the schema
        switch ($uri['scheme'])
        {
            case 'http':
                $port = (isset($uri['port']) ? $uri['port'] : 80);
                $host = ($uri['host'] . ($port != 80 ? ':' . $port : ''));
                $fp = @fsockopen($uri['host'], $port, $errno, $errstr, $timeout);
                break;

            case 'https':
                $port = (isset($uri['port']) ? $uri['port'] : 443);
                $host = ($uri['host'] . ($port != 443 ? ':' . $port : ''));
                $fp = @fsockopen('ssl://' . $uri['host'], $port, $errno, $errstr, $timeout);
                break;

            default:
                $result->http_code = -1;
                $result->http_data = null;
                $result->http_error = 'invalid_schema';

                return $result;
                break;
        }

        //Make sure the socket opened properly
        if (!$fp)
        {
            $result->http_code = -$errno;
            $result->http_data = null;
            $result->http_error = trim($errstr);

            return $result;
        }

        //Construct the path to act on
        $path = (isset($uri['path']) ? $uri['path'] : '/');
        if (isset($uri['query']))
        {
            $path .= '?' . $uri['query'];
        }

        //Create HTTP request
        $defaults = array(
            'Host' => 'Host: ' . $host,
            'User-Agent' => 'User-Agent: ' . self::USER_AGENT
        );

        // BASIC AUTH?
        if (isset($options['api_key']) and isset($options['api_secret']))
        {
            $defaults['Authorization'] = 'Authorization: Basic ' . base64_encode($options['api_key'] . ":" . $options['api_secret']);
        }

        //Build and send request
        $request = 'GET ' . $path . " HTTP/1.0\r\n";
        $request .= implode("\r\n", $defaults);
        $request .= "\r\n\r\n";
        fwrite($fp, $request);

        //Fetch response
        $response = '';
        while (!feof($fp))
        {
            $response .= fread($fp, 1024);
        }

        //Close connection
        fclose($fp);

        //Parse response
        list($response_header, $response_body) = explode("\r\n\r\n", $response, 2);

        //Parse header
        $response_header = preg_split("/\r\n|\n|\r/", $response_header);
        list($header_protocol, $header_code, $header_status_message) = explode(' ', trim(array_shift($response_header)), 3);

        //Build result
        $result->http_code = $header_code;
        $result->http_data = $response_body;

        //Done

        return $result;
    }

    /**
     * Returns the callback URI
     */
    public static function get_callback_uri($include_return_to_param = false, $remove_back_param = true)
    {
        // Current URL
        $current_url = self::get_current_url();

        // Remove the back parameter?
        if ($remove_back_param)
        {
            if (strpos($current_url, 'back') !== false)
            {
                //Break up url
                list($url_part, $query_part) = array_pad(explode('?', $current_url), 2, '');
                parse_str($query_part, $query_vars);

                //Remove oa_social_login_source argument
                if (is_array($query_vars) && isset($query_vars['back']))
                {
                    unset($query_vars['back']);
                }

                //Build new url
                $current_url = $url_part . ((is_array($query_vars) && count($query_vars) > 0) ? ('?' . http_build_query($query_vars)) : '');
            }
        }

        // Build callback uri
        $callback_uri = Tools::getHttpHost(true) . __PS_BASE_URI__;

        // Add return_to parameter?
        if ($include_return_to_param)
        {
            $callback_uri .= (parse_url($callback_uri, PHP_URL_QUERY) ? '&' : '?');
            $callback_uri .= 'return_to=' . urlencode($current_url);
        }

        // Done

        return $callback_uri;
    }

    /**
     * Returns the current url
     */
    public static function get_current_url()
    {
        //Get request URI - Should work on Apache + IIS
        $request_uri = (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF']);
        $request_protocol = (self::is_https_on() ? 'https' : 'http');
        $request_host = (isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']));

        // Make sure we strip $request_host so we got no double ports un $current_url
        $request_host = preg_replace('/:[0-9]*$/', '', $request_host);

        //We are using a proxy
        if (isset($_SERVER['HTTP_X_FORWARDED_PORT']))
        {
            // SERVER_PORT is usually wrong on proxies, don't use it!
            $request_port = intval($_SERVER['HTTP_X_FORWARDED_PORT']);
        }
        //Does not seem like a proxy
        elseif (isset($_SERVER['SERVER_PORT']))
        {
            $request_port = intval($_SERVER['SERVER_PORT']);
        }

        // Remove standard ports
        $request_port = (!in_array($request_port, array(80, 443)) ? $request_port : '');

        //Build url
        $current_url = $request_protocol . '://' . $request_host . (!empty($request_port) ? (':' . $request_port) : '') . $request_uri;

        //Done

        return $current_url;
    }

    /**
     * Check if the current connection is being made over https
     */
    public static function is_https_on()
    {
        if (!empty($_SERVER['SERVER_PORT']))
        {
            if (trim($_SERVER['SERVER_PORT']) == '443')
            {
                return true;
            }
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']))
        {
            if (strtolower(trim($_SERVER['HTTP_X_FORWARDED_PROTO'])) == 'https')
            {
                return true;
            }
        }

        if (!empty($_SERVER['HTTPS']))
        {
            if (strtolower(trim($_SERVER['HTTPS'])) == 'on' or trim($_SERVER['HTTPS']) == '1')
            {
                return true;
            }
        }

        // HTTPS is off.

        return false;
    }
}
