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

{capture name=path}{l s='Create an account' mod='oneallsociallogin'}{/capture}

{extends file='page.tpl'}

{block name="page_content"}
 
<h1 class="page-heading bottom-indent">{l s='You have connected with %s!' sprintf=[$identity_provider] mod='oneallsociallogin'}</h1>
<p>
	{l s='Please take a minute to review and complete your account information.' mod='oneallsociallogin'}
	{l s='Once you have reviewed your details, your account is ready to use and you can sign in with %s.' sprintf=[$identity_provider] mod='oneallsociallogin'}
</p>

<div id="oneallsociallogin">
	<form id="account-creation_form" action="{$oasl_register}" method="post" class="box">
		<fieldset>
			<div class="form_content clearfix">
				<div class="form-group">
						<label for="oasl_firstname">{l s='First name' mod='oneallsociallogin'} <sup>*</sup></label> 
						<input type="text" class="is_required form-control" id="oasl_firstname" name="oasl_firstname" value="{if isset($smarty.post.oasl_firstname)}{$smarty.post.oasl_firstname|stripslashes}{elseif $oasl_populate == '1'}{$oasl_first_name}{/if}" />
				</div>
				<div class="form-group">					
						<label for="oasl_lastname">{l s='Last name' mod='oneallsociallogin'} <sup>*</sup></label>
						<input type="text" class="is_required form-control" id="oasl_lastname" name="oasl_lastname" value="{if isset($smarty.post.oasl_lastname)}{$smarty.post.oasl_lastname|stripslashes}{elseif $oasl_populate == '1'}{$oasl_last_name}{/if}" />
				</div>
				<div class="form-group">				
						<label for="oasl_email">{l s='Email' mod='oneallsociallogin'} <sup>*</sup></label>
						<input type="text" class="is_required form-control" id="oasl_email" name="oasl_email" value="{if isset($smarty.post.oasl_email)}{$smarty.post.oasl_email|stripslashes}{elseif $oasl_populate == '1'}{$oasl_email}{/if}" />
				</div>				
				<div class="checkbox">
					<label for="oasl_newsletter">
						<input type="checkbox" id="oasl_newsletter" name="oasl_newsletter" value="1" {if isset($smarty.post.oasl_newsletter) && $smarty.post.oasl_newsletter == '1'}checked="checked"{elseif isset($oasl_newsletter) && $oasl_newsletter == '1'}checked="checked"{/if} />
						{l s='Sign up for our newsletter!' mod='oneallsociallogin'}
					</label>
				</div>			
				<hr />
				<div class="submit">
					<button name="submit" id="submit" type="submit" class="btn btn-default button button-medium"><span>{l s='Confirm' mod='oneallsociallogin'}<i class="icon-chevron-right right"></i></span></button>
				</div>
			</div>
		</fieldset>
	</form>
</div>

{/block}
