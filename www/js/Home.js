function Home(paginaInicial, absoluteUrlLocal){

	function handleEvents(){

		$('.menu-item').click(function(){
			$('.menu-item').removeClass('active');
			$(this).addClass('active');
		});

		$('.mobile-item').click(function(){
			$('.mobile-item').parent().removeClass('active');
			$(this).parent().addClass('active');
		});

		$('.menu-container a').click(function(e){
			var target = $(this).attr('href');

			if(target)
				Loader.pageLoad(target);

			e.preventDefault();
			return false;
		});

		$('#logout_mobile, #btn_logout').click(function(){
			Diplomat.request({
				async: false,
				url: 'login/logout',
				success: function(data_server){
					console.log('data_server: logoput', data_server);
					window.location = CONFIG_CLIENT_URI + 'Login.php';
				}
			});
		});
	}

	function manageMenu(){
		$.ajax({
			url: PATH_TEMPLATE + 'Menu.html',
			dataType: "html",
			async: true,
			success: function(menu_html){
				$('#wrapper').prepend(Home.manageHTMLActionAccess(menu_html));

				var $menus = $('.menu-items-panel > *');
				var menus_str = $menus.map(function(){
					return this.outerHTML;
				}).toArray();

				var max_width = 100 / menus_str.length;
				$('.menu-default-panel').html(menus_str.join(''));

				$('.menu-default-panel > *').each(function(){
					$(this).css('max-width', max_width + '%');
				});

				$('.menu-item[href=' + initial_page + ']').addClass('active');
				$('.mobile-item[href=' + initial_page + ']').parent().addClass('active');

				$(log_name_container).html(user_log.nm_usuario);

				handleEvents();
			}
		});
	}

	this.init = function(){
		manageMenu();

		Loader.init(paginaInicial, function(){
			absoluteUrl = absoluteUrlLocal;
			Form.handleEvents();
			constructBar();
			$('#home_loading').remove();
		});
	}

}

Home.manageHTMLActionAccess = function(html, server_name){
	if(server_name)
		server_name = server_name.toLowerCase();

	var $html = getJQueryHTML(html);
	var nm_denied_permissions = JSON.parse(window.sessionStorage['nm_denied_permissions']);
	console.log('nm_denied_permissions', nm_denied_permissions);

	$html.find('[access-action]').each(function(){
		var actions = $(this).attr('access-action').split(' '),
			action;

		for (var i = 0; i < actions.length; i++){
			action = actions[i].indexOf('.') > 0? actions[i]: server_name + '.' + actions[i];

			console.log('action', action);

			if(nm_denied_permissions.indexOf(action) !== -1)
				$(this).remove();
		}
	});

	return $html;
}