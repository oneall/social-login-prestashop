{*
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
*}
 
{if {$oasl_widget_location} eq 'library'}
	<script type="text/javascript">
    
		/* OneAll Social Login */
		/* http://docs.oneall.com/plugins/guide/social-login-prestashop/ */
        
		/* Asynchronous Library */      
		var oa = document.createElement('script');
		oa.type = 'text/javascript'; oa.async = true;
		oa.src = '//{$oasl_subdomain}.api.oneall.com/socialize/library.js';
		var s = document.getElementsByTagName('script')[0];
		s.parentNode.insertBefore(oa, s);
        
		/* Custom Hooks */      
		var _oneall = _oneall || [];                
		$(document).ready(function() {  
			if (typeof oneallsociallogin !== 'undefined') {
				oneallsociallogin (_oneall, [{$oasl_widget_providers}], '{$oasl_auth_disable}', '{$oasl_auth_title}', '{$oasl_custom_title}');
			} else {
				throw new Error("OneAll Social Login is not correctly installed, the required file oneallsocialogin.js is not included.");
			}
		});         
	</script>
{/if}


{if {$oasl_widget_location} eq 'customer_account_form'}
	<div class="block oneall_social_login_block" id="oneall_social_login_block_left">
		{if {$oasl_widget_title} neq ''}
			<p class="title_block">{$oasl_widget_title}</p>
		{/if}
		<p class="block_content">
			<div class="oneall_social_login_providers" id="oneall_social_login_providers_{$oasl_widget_rnd}"></div>
			<script type="text/javascript">
			  var _oneall = _oneall || [];
				_oneall.push(['social_login', 'set_providers', [{$oasl_widget_providers}]]);
				_oneall.push(['social_login', 'set_callback_uri', window.location.href]);
				_oneall.push(['social_login', 'set_custom_css_uri', '{$oasl_widget_css}']);
  			_oneall.push(['social_login', 'do_render_ui', 'oneall_social_login_providers_{$oasl_widget_rnd}']);			
			</script>
		</p>
	</div>
{/if}

{if {$oasl_widget_location} eq 'left'}
	<div class="block oneall_social_login_block" id="oneall_social_login_block_left">
		{if {$oasl_widget_title} neq ''}
			<p class="title_block">{$oasl_widget_title}</p>
		{/if}
		<p class="block_content">
			<div class="oneall_social_login_providers" id="oneall_social_login_providers_{$oasl_widget_rnd}"></div>
			<script type="text/javascript">
			  var _oneall = _oneall || [];
				_oneall.push(['social_login', 'set_providers', [{$oasl_widget_providers}]]);
				_oneall.push(['social_login', 'set_callback_uri', window.location.href]);
				_oneall.push(['social_login', 'set_custom_css_uri', '{$oasl_widget_css}']);
  			_oneall.push(['social_login', 'do_render_ui', 'oneall_social_login_providers_{$oasl_widget_rnd}']);			
			</script>
		</p>
	</div>
{/if}

{if {$oasl_widget_location} eq 'right'}
	<div class="block oneall_social_login_block" id="oneall_social_login_block_right">
		{if {$oasl_widget_title} neq ''}
			<p class="title_block">{$oasl_widget_title}</p>
		{/if}
		<p class="block_content">
			<div class="oneall_social_login_providers" id="oneall_social_login_providers_{$oasl_widget_rnd}"></div>
			<script type="text/javascript">
				var _oneall = _oneall || [];
				_oneall.push(['social_login', 'set_providers', [{$oasl_widget_providers}]]);
				_oneall.push(['social_login', 'set_callback_uri', window.location.href]);
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
			_oneall.push(['social_login', 'set_providers', [{$oasl_widget_providers}]]);
			_oneall.push(['social_login', 'set_callback_uri', window.location.href]);
			_oneall.push(['social_login', 'set_custom_css_uri', '{$oasl_widget_css}']);
  		_oneall.push(['social_login', 'do_render_ui', 'oneall_social_login_providers_{$oasl_widget_rnd}']);		
		</script>
	</div>
{/if}