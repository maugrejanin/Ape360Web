var Navigator = {
	info: false,

	navigators: [
		'Firefox',
		'Chrome',
		'opera',
		'safari',
		'IE'
	],

	download_links: {
		Firefox: 'https://www.mozilla.org/pt-BR/firefox/new/',
		Chrome: 'https://www.google.com.br/chrome/browser/desktop/',
		opera: 'http://www.opera.com/pt-br/download'
	},

	min_version_navigator: {
		Chrome: 7,
		Firefox: 4,
		IE: 10,
		opera: 12,
		safari: 5
	},

	getInfo: function(){
		function localGetInfo(){
			var ua=navigator.userAgent,tem,M=ua.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*(\d+)/i) || [];

			if(/trident/i.test(M[1])){
				tem=/\brv[ :]+(\d+)/g.exec(ua) || []; 
				return {name:'IE',version:(tem[1]||'')};
			}

			if(M[1]==='chrome'){
				tem=ua.match(/\bOPR\/(\d+)/)
				if(tem!=null)   {return {name:'opera', version:tem[1]};}
			}

			M=M[2]? [M[1], M[2]]: [navigator.appName, navigator.appVersion, '-?'];
			if((tem=ua.match(/version\/(\d+)/i))!=null) {M.splice(1,1,tem[1]);}
				return {
					name: M[0],
					version: M[1]
				};
		}

		if(!Navigator.info)
			Navigator.info = localGetInfo();

		return Navigator.info;
	},

	validNavigator: function(){
		var info = Navigator.getInfo();

		if(Navigator.min_version_navigator[info.name] && parseFloat(info.version) < Navigator.min_version_navigator[info.name])
			Navigator.stopSystemAndSugestAvailableBrowser();
		else
			return;
	},

	stopSystemAndSugestAvailableBrowser: function(){
		var info = Navigator.getInfo();
		var min_version = Navigator.min_version_navigator[info.name];

		var navigator_element_html = '<a class="nav-cell" target="_blank"><div class="nav-icon-container"></div></a>';

		var $container = $('<div id="navigator_denied_list"></div>');
		var $cell;
		for (var i = 0; i < Navigator.navigators.length; i++) {
			if(!Navigator.download_links[Navigator.navigators[i]])
				continue;

			$cell = $(getObjHTML(navigator_element_html));
			$cell.attr('href', Navigator.download_links[Navigator.navigators[i]]);
			$cell.find('.nav-icon-container').attr(
				'title', Navigator.navigators[i]
			).css('background-image', 'url("' + PATH_IMG_NAVIGATOR + Navigator.navigators[i] + '.png")');
			$container.append($cell);
		}

		$.ajax({
			url: 'template/NavigatorDenied.html',
			dataType: 'html',
			success: function(navigator_denied_html){
				$('body').css('margin', '0px').html(navigator_denied_html);
				$('#navigator_denied_container').append($container);

				throw new Error('Aplicação não suportada pelo navegador do cliente (Cliente: ' + info.name + ' ' + info.version + ' - Sistema: ' + info.name + ' ' + min_version + ')');
			}
		});
	}
};