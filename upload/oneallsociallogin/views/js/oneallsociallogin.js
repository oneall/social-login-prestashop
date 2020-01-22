function oneallsociallogin(_oneall, providers, auth_disable, custom_title, custom_css) {

	var has_logger = !!(window.console && window.console.log);
	var containers = [];
	var elementref = '';
	var contents = '';

	providers = (typeof providers !== 'undefined' ? providers : []);
	auth_disable = (typeof auth_disable !== 'undefined' ? auth_disable : 0);
	custom_title = (typeof custom_title !== 'undefined' ? custom_title : '');
	custom_css = (typeof custom_css !== 'undefined' ? custom_css : '');


	if (auth_disable != 1) {
		if ($("body#authentication").length > 0) {
			elementref = 'oneall_social_login_providers_' + (10000 + Math.floor(Math.random() * 99999 + 1));
			containers.push(elementref);
			contents = '<div class="box oneall_social_login_auth">';
			if (custom_title.length > 0) {
				contents += '<h3 class="page-subheading">' + custom_title + '</h3>';
			}
			contents += '<div class="oneall_social_login_providers" id="' + elementref + '" /></div>';
			var inserted = $(contents).insertAfter(".page-heading:eq(0)");
			/* Try another location (i.e. transformer theme). */
			if (inserted.length == 0) {
				inserted = $(contents).insertAfter(".heading:eq(0)");
			}
			if (has_logger && inserted.length == 0) {
				console.log("Error: (OneAll) could not find the element to add the social icons to.");
			}
		}
	}

	/* <div id="oneall_social_login" /> */
	if ($("#oneall_social_login").length > 0) {
		containers.push("oneall_social_login");
	}

	/* <div id="oneall_social_login_box" /> */
	if ($("#oneall_social_login_box").length > 0) {
		elementref = 'oneall_social_login_providers_' + (10000 + Math.floor(Math.random() * 99999 + 1));
		containers.push(elementref);
		contents = '<div class="box">';
		if (custom_title.length > 0) {
			contents += '<h3 class="page-subheading">' + custom_title + '</h3>';
		}
		contents += '<div class="oneall_social_login_providers" id="' + elementref + '" /></div>';
		$("#oneall_social_login_box").append(contents);
	}

	if (containers.length > 0) {
		_oneall.push(['social_login', 'set_providers', providers]);
		_oneall.push(['social_login', 'set_callback_uri', window.location.href]);
		if (custom_css.length > 0) {
			_oneall.push(['social_login', 'set_custom_css_uri', custom_css]);
		}
		for ( var elementref; elementref = containers.pop();) {
			_oneall.push(['social_login', 'do_render_ui', elementref]);
		}
	}
}


$(document).ready(function() {

	/* OneAll Social Login Library */
	if (oasl_widget_location == 'library'){

		/* OneAll Social Login */
		/* http://docs.oneall.com/plugins/guide/social-login-prestashop/ */

		/* Asynchronous Library */      
		var oa = document.createElement('script');
		oa.type = 'text/javascript'; oa.async = true;
		oa.src = '//'+oasl_subdomain+'.api.oneall.com/socialize/library.js';
		var s = document.getElementsByTagName('script')[0];
		s.parentNode.insertBefore(oa, s);


		if ($('#oneall_social_login').length > 0){
			/* Custom Hooks */      
			var _oneall = _oneall || [];            
			if (typeof oneallsociallogin !== 'undefined') {
				if (oasl_translated_title != ' '){
					oneallsociallogin (_oneall, providers, oasl_auth_disable, oasl_translated_title);
				} else {
					oneallsociallogin (_oneall, providers, oasl_auth_disable);
				}
			} else {
				throw new Error("OneAll Social Login is not correctly installed, the required file oneallsocialogin.js is not included.");
			}
		}
	}

});  