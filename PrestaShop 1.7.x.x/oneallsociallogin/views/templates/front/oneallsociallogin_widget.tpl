{*
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
*}
 
{capture name='oneallsociallogin_title'}{l s='Connect with:' mod='oneallsociallogin'}{/capture}
{assign var='oasl_translated_title' value=$smarty.capture.oneallsociallogin_title}

{capture name='oneallsociallogin_title_login'}{l s='Log in with:' mod='oneallsociallogin'}{/capture}
{assign var='oasl_translated_title_login' value=$smarty.capture.oneallsociallogin_title_login}

{capture name='oneallsociallogin_title_register'}{l s='Register with:' mod='oneallsociallogin'}{/capture}
{assign var='oasl_translated_title_register' value=$smarty.capture.oneallsociallogin_title_register}

{if {$oasl_widget_location} eq 'library'}
	<script type="text/javascript">

	  	oasl_translated_title = typeof oasl_translated_title != 'undefined' ? oasl_translated_title : '';
	  	oasl_widget_location = "{$oasl_widget_location}";
    	oasl_subdomain = "{$oasl_subdomain}";
	    oasl_auth_disable = '{$oasl_auth_disable}';

		var providers = [];
		{foreach $oasl_widget_providers_array as $provider}
		    providers.push("{$provider}");
		{/foreach}

	</script>
{/if}


{if {$oasl_widget_location} eq 'customer_account_form'}
	<div class="block oneall_social_login_block" id="oneall_social_login_block_customer_account_form">
	{if {$oasl_translated_title_register|strip} neq ' '}
		<p class="title_block">{$oasl_translated_title_register}</p>
	{/if}
		<p class="block_content">
			<div class="oneall_social_login_providers" id="oneall_social_login_providers_{$oasl_widget_rnd}"></div>
			<script type="text/javascript">
			  var _oneall = _oneall || [];
				_oneall.push(['social_login', 'set_providers', providers]);
				_oneall.push(['social_login', 'set_callback_uri', '{$oasl_widget_callback}']);
				_oneall.push(['social_login', 'set_custom_css_uri', '{$oasl_widget_css}']);
  			_oneall.push(['social_login', 'do_render_ui', 'oneall_social_login_providers_{$oasl_widget_rnd}']);			
			</script>
		</p>
	</div>
{/if}


{if {$oasl_widget_location} eq 'left_column'}
	<div class="block oneall_social_login_block oneall_social_login_block_column" id="oneall_social_login_block_left_column">
	{if {$oasl_translated_title|strip} neq ' '}
		<p class="title_block h6">{$oasl_translated_title}</p>
	{/if}
		<p class="block_content">
			<div class="oneall_social_login_providers" id="oneall_social_login_providers_{$oasl_widget_rnd}"></div>
			<script type="text/javascript">
			  var _oneall = _oneall || [];
				_oneall.push(['social_login', 'set_providers', providers]);
				_oneall.push(['social_login', 'set_callback_uri', '{$oasl_widget_callback}']);
				_oneall.push(['social_login', 'set_custom_css_uri', '{$oasl_widget_css}']);
  			_oneall.push(['social_login', 'do_render_ui', 'oneall_social_login_providers_{$oasl_widget_rnd}']);			
			</script>
		</p>
	</div>
{/if}

{if {$oasl_widget_location} eq 'right_column'}
	<div class="block oneall_social_login_block oneall_social_login_block_column" id="oneall_social_login_block_right_column">
	{if {$oasl_translated_title|strip} neq ' '}
		<p class="title_block h6">{$oasl_translated_title}</p>
	{/if}
		<p class="block_content">
			<div class="oneall_social_login_providers" id="oneall_social_login_providers_{$oasl_widget_rnd}"></div>
			<script type="text/javascript">
				var _oneall = _oneall || [];
				_oneall.push(['social_login', 'set_providers', providers]);
				_oneall.push(['social_login', 'set_callback_uri', '{$oasl_widget_callback}']);
				_oneall.push(['social_login', 'set_custom_css_uri', '{$oasl_widget_css}']);
	  		_oneall.push(['social_login', 'do_render_ui', 'oneall_social_login_providers_{$oasl_widget_rnd}']);	
			</script>
		</p>
	</div>
{/if}

{if {$oasl_widget_location} eq 'custom'}
	<div class="oneall_social_login_providers oneall_social_login_providers_custom" id="oneall_social_login_providers_{$oasl_widget_rnd}"></div>
	<script type="text/javascript">
		var _oneall = _oneall || [];
		_oneall.push(['social_login', 'set_providers', providers]);
		_oneall.push(['social_login', 'set_callback_uri', '{$oasl_widget_callback}']);
		_oneall.push(['social_login', 'set_custom_css_uri', '{$oasl_widget_css}']);
  	_oneall.push(['social_login', 'do_render_ui', 'oneall_social_login_providers_{$oasl_widget_rnd}']);		
	</script>	
{/if}

{if {$oasl_widget_location} eq 'displayCustomerLoginFormAfter'}
	<div class="block oneall_social_login_block" id="oneall_social_login_block_customer_login_form">
	{if {$oasl_translated_title_login|strip} neq ' '}
		<p class="title_block">{$oasl_translated_title_login}</p>
	{/if}
		<p class="block_content">
			<div class="oneall_social_login_providers" id="oneall_social_login_providers_{$oasl_widget_rnd}"></div>
			<script type="text/javascript">
			  var _oneall = _oneall || [];
				_oneall.push(['social_login', 'set_providers', providers]);
				_oneall.push(['social_login', 'set_callback_uri', '{$oasl_widget_callback}']);
				_oneall.push(['social_login', 'set_custom_css_uri', '{$oasl_widget_css}']);
  			_oneall.push(['social_login', 'do_render_ui', 'oneall_social_login_providers_{$oasl_widget_rnd}']);			
			</script>
		</p>
	</div>
{/if}
