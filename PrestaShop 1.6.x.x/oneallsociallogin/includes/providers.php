<?php
/**
 * @package   	OneAll Social Login
 * @copyright 	Copyright 2014 http://www.oneall.com - All rights reserved.
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
		$providers = array (
			'amazon' => array (
				'name' => 'Amazon'
			),
			'blogger' => array (
				'name' => 'Blogger'
			),
			'disqus' => array (
				'name' => 'Disqus'
			),
			'facebook' => array (
				'name' => 'Facebook'
			),
			'foursquare' => array (
				'name' => 'Foursquare'
			),
			'github' => array (
				'name' => 'Github.com'
			),
			'google' => array (
				'name' => 'Google'
			),
			'instagram' => array (
				'name' => 'Instagram'
			),
			'linkedin' => array (
				'name' => 'LinkedIn'
			),
			'livejournal' => array (
				'name' => 'LiveJournal'
			),
			'mailru' => array (
				'name' => 'Mail.ru'
			),
			'odnoklassniki' => array (
				'name' => 'Odnoklassniki'
			),
			'openid' => array (
				'name' => 'OpenID'
			),
			'paypal' => array (
				'name' => 'PayPal'
			),
			'reddit' => array (
				'name' => 'Reddit'
			),
			'skyrock' => array (
				'name' => 'Skyrock.com'
			),
			'stackexchange' => array (
				'name' => 'StackExchange'
			),
			'steam' => array (
				'name' => 'Steam'
			),
			'twitch' => array (
				'name' => 'Twitch.tv'
			),
			'twitter' => array (
				'name' => 'Twitter'
			),
			'vimeo' => array (
				'name' => 'Vimeo'
			),
			'vkontakte' => array (
				'name' => 'VKontakte'
			),
			'windowslive' => array (
				'name' => 'Windows Live'
			),
			'wordpress' => array (
				'name' => 'WordPress.com'
			),
			'yahoo' => array (
				'name' => 'Yahoo'
			),
			'youtube' => array (
				'name' => 'YouTube'
			),
		);

		return $providers;
	}
}
