<?php
/**
 * @package   	OneAll Social Login
 * @copyright 	Copyright 2012 http://www.oneall.com - All rights reserved.
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

//OneAll Social Login Provider List
class oneall_social_login_providers
{
	/**
	 * Checks if a given provider key is valid.
	 */
	public static function is_valid_key ($key)
	{
		$providers = self::get_list ();
		return isset ($providers [$key]);
	}

	/**
	 * Returns a list of providers that are available.
	 */
	public static function get_list ()
	{
		return array (
			'facebook' => array (
				'name' => 'Facebook'
			),
			'twitter' => array (
				'name' => 'Twitter'
			),
			'google' => array (
				'name' => 'Google'
			),
			'linkedin' => array (
				'name' => 'LinkedIn'
			),
			'yahoo' => array (
				'name' => 'Yahoo'
			),
			'github' => array (
				'name' => 'Github.com'
			),
			'foursquare' => array (
				'name' => 'Foursquare'
			),
			'youtube' => array (
				'name' => 'YouTube'
			),
			'skyrock' => array (
				'name' => 'Skyrock.com'
			),
			'openid' => array (
				'name' => 'OpenID'
			),
			'wordpress' => array (
				'name' => 'Wordpress.com'
			),
			'hyves' => array (
				'name' => 'Hyves'
			),
			'paypal' => array (
				'name' => 'PayPal'
			),
			'livejournal' => array (
				'name' => 'LiveJournal'
			),
			'steam' => array (
				'name' => 'Steam Community'
			),
			'windowslive' => array (
				'name' => 'Windows Live'
			),
			'blogger' => array (
				'name' => 'Blogger'
			),
			'disqus' => array (
				'name' => 'Disqus'
			),
			'stackexchange' => array (
				'name' => 'StackExchange'
			),
			'vkontakte' => array (
				'name' => 'VKontakte (Вконтакте)'
			),
			'odnoklassniki' => array (
				'name' => 'Odnoklassniki.ru'
			),
			'mailru' => array (
				'name' => 'Mail.ru'
			)
		);
	}
}
