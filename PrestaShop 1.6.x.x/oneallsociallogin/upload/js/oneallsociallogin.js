function oneallsociallogin(_oneall, providers, auth_disable, auth_title, custom_title, custom_css) {

	var containers = [];
	var elementref = '';
	var contents = '';

	providers = (typeof providers !== 'undefined' ? providers : []);
	auth_disable = (typeof auth_disable !== 'undefined' ? auth_disable : 0);
	auth_title = (typeof auth_title !== 'undefined' ? auth_title : '');
	custom_title = (typeof custom_title !== 'undefined' ? custom_title : '');
	custom_css = (typeof custom_css !== 'undefined' ? custom_css : '');

	if (auth_disable != 1) {
		if ($("body#authentication").length > 0) {
			elementref = 'oneall_social_login_providers_' + (10000 + Math.floor(Math.random() * 99999 + 1));
			containers.push(elementref);
			contents = '<div class="box oneall_social_login_auth">';
			if (auth_title.length > 0) {
				contents += '<h3 class="page-subheading">' + auth_title + '</h3>';
			}
			contents += '<div class="oneall_social_login_providers" id="' + elementref + '" /></div>';
			$(contents).insertAfter(".page-heading:eq(0)");
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
