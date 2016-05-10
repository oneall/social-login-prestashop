{*
* @package   	OneAll Social Login
* @copyright 	Copyright 2011-2015 http://www.oneall.com - All rights reserved
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

{if {$oasl_widget_location} eq 'library'}	
	<script type="text/javascript">
	  var oneall_subdomain = '{$oasl_subdomain}';
	  var oneall_js_protocol = (("https:" == document.location.protocol) ? "https" : "http");
	  document.write(unescape("%3Cscript src='"+oneall_js_protocol+"://"+oneall_subdomain+".api.oneall.com/socialize/library.js' type='text/javascript'%3E%3C/script%3E"));
	</script>
{/if}

{if {$oasl_widget_location} eq 'left'}
	<div class="block oneall_social_login_block" id="oneall_social_login_block_left">
	{if {$oasl_translated_title|strip} neq ' '}
		<p class="title_block">{$oasl_translated_title}</p>
	{/if}
		<p class="block_content">
			<div class="oneall_social_login_providers" id="oneall_social_login_providers_{$oasl_widget_rnd}"></div>
			<script type="text/javascript">
				oneall.api.plugins.social_login.build("oneall_social_login_providers_{$oasl_widget_rnd}", {
					"providers": ["{$oasl_widget_providers}"],
					"callback_uri": window.location.href,
					"css_theme_uri": "{$oasl_widget_css}" 
				});
			</script>
		</p>
	</div>
{/if}

{if {$oasl_widget_location} eq 'custom'}
	<div class="oneall_social_login_providers oneall_social_login_providers_custom" id="oneall_social_login_providers_{$oasl_widget_rnd}"></div>
		<script type="text/javascript">
			oneall.api.plugins.social_login.build("oneall_social_login_providers_{$oasl_widget_rnd}", {
				"providers": ["{$oasl_widget_providers}"],
				"callback_uri": window.location.href,
				"css_theme_uri": "{$oasl_widget_css}" 
			});
		</script>
	</div>
{/if}

{if {$oasl_widget_location} eq 'right'}
	<div class="block oneall_social_login_block" id="oneall_social_login_block_right">
	{if {$oasl_translated_title|strip} neq ' '}
		<p class="title_block">{$oasl_translated_title}</p>
	{/if}
		<p class="block_content">
			<div class="oneall_social_login_providers" id="oneall_social_login_providers_{$oasl_widget_rnd}"></div>
			<script type="text/javascript">
				oneall.api.plugins.social_login.build("oneall_social_login_providers_{$oasl_widget_rnd}", {
					"providers": ["{$oasl_widget_providers}"],
					"callback_uri": window.location.href,
					"css_theme_uri": "{$oasl_widget_css}" 
				});
			</script>
		</p>
	</div>
{/if}

