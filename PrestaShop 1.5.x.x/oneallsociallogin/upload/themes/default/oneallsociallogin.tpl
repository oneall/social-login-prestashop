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

{capture name=path}{l s='Create an account' mod='oneallsociallogin'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h1>{l s='You have connected with %s !' sprintf=$identity_provider mod='oneallsociallogin'}</h1>
<p>
	{l s='Please take a minute to review and complete your account information.' mod='oneallsociallogin'}
	{l s='Once you have reviewed your details, your account is ready to use and you can sign in with %s.' sprintf=$identity_provider mod='oneallsociallogin'}
</p>


{include file="$tpl_dir./errors.tpl"}

<div id="oneallsociallogin">
	<p class="required"><sup>*</sup>{l s='Required field' mod='oneallsociallogin'}</p>
	<form action="{$link->getPageLink('oneallsociallogin', true)}" method="post" class="std">
		<fieldset>
			<div class="form_content clearfix">
				<p class="required text">
					<label for="firstname">{l s='First name' mod='oneallsociallogin'} <sup>*</sup></label>
					 <span><input type="text" id="oasl_firstname" name="oasl_firstname" value="{if isset($smarty.post.oasl_firstname)}{$smarty.post.oasl_firstname|stripslashes}{elseif $oasl_populate == '1'}{$oasl_first_name}{/if}" /></span>
				</p>
				<p class="required text">
					<label for="email">{l s='Last name' mod='oneallsociallogin'} <sup>*</sup></label>
					<span><input type="text" id="oasl_lastname" name="oasl_lastname" value="{if isset($smarty.post.oasl_lastname)}{$smarty.post.oasl_lastname|stripslashes}{elseif $oasl_populate == '1'}{$oasl_last_name}{/if}" /></span>
				</p>
				<p class="required text">
					<label for="email">{l s='Email' mod='oneallsociallogin'} <sup>*</sup></label>
					<span><input type="text" id="oasl_email" name="oasl_email" value="{if isset($smarty.post.oasl_email)}{$smarty.post.oasl_email|stripslashes}{elseif $oasl_populate == '1'}{$oasl_email}{/if}" /></span>	
				</p>
				<p class="submit">
					{if isset($back)}<input type="hidden" class="hidden" name="back" value="{$back|escape:'htmlall':'UTF-8'}" />{/if}
					<input type="submit" id="submit" name="submit" class="button" value="{l s='Confirm' mod='oneallsociallogin'}" />
				</p>
			</div>
		</fieldset>
	</form>
</div>
