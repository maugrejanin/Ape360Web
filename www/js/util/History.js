// begin history manipulator
// Este arquivo depende do PageMap.js para funcionar!
var __bar = [initial_page], __history = [ {'pageUrl' : initial_page + '.html', 'panelId' : 'pnlContent', 'postData' : {}, 'current': true} ],
HISTORY_LIMIT = 10;

window.history.pushState(0, null, './#' + initial_page);

jQuery(document).ready(function($) {

  if (window.history && window.history.pushState) {

    $(window).on('popstate', function(event) {
    	//console.log("popstate caller is ", arguments.callee.caller.toString());
		var hashLocation = location.hash;
		var hashSplit = hashLocation.split("#!/");
		var hashName = hashSplit[hashSplit.length - 1];
		if (hashName !== '') {
			var hash = window.location.hash;
			var state = event.originalEvent.state;
			var target_page;
			var back = false, forward = false;

			if(state === null){
				//alert('state null');
				//window.history.go(-1);//Vamos pra Home.php
				window.history.go(-1);//pulamos a parte que a Home.php aparece na url
				return true;
			}else if(!__history.hasOwnProperty(state)){//nesse caso o usuário segurou o botão back e "pulou" para uma página não contida no sistema
				//alert('fora da range');
				return true;//deixe o browser fazer o trabalho dele sozinho
			}

			for(key in __history){

				if(__history[key].current){
					__history[key].current = false;
					__history[state].current = true;//moving forward
					target_page = __history[state];

					if( key > state )
						back = true;
					else
						forward = true;

					manageBar(target_page.pageUrl, target_page.postData, back, forward);

					break;
				}
			}

			Loader.pageLoad(target_page.pageUrl, target_page.panelId, target_page.postData, back, forward);
		}
		else {
			//window.history.go(-1);
			//delete __history[__history.length - 1];
			debug.log("Popstate indevido. Habilite o log para verificar.");
		}
    });

  }

});

function manageBar(pageUrl, postData, back, forward){
	back = back || false;
	forward = forward || false;

	var hash = pageUrl.split('/').pop().split('.').shift();
	var selected_crumb_index = $(".my-breadcrumb li.active").index(".my-breadcrumb li");
	var natural_call = !back && !forward;
	var data_post = !(typeof postData === "undefined" || postData.length === 0 || postData.__break_crumb);

	if( (!data_post && natural_call) || (back && selected_crumb_index === 0) || (forward && !data_post)){
		__bar = [hash];
	}else if( (data_post && natural_call) || (forward && $(".my-breadcrumb li.active").is('.my-breadcrumb li:last')) ){
		__bar  = __bar.slice(0, selected_crumb_index+1);
		__bar.push(hash);
	}
}

function pushHistory(pageUrl, panelId, postData){
	var hash = pageUrl.split('/').pop().split('.').shift();

	manageBar(pageUrl, postData);

	for(key in __history){
		if(__history[key].current){
			__history = __history.slice(0, parseInt(key)+1);//destruindo tudo que foi armazenado à frente.(forward button must be desabled)
			break;
		}
	}
	//console.log("Pushhistory. Hash: ", hash);
	window.history.pushState(__history.length, null, './#'+hash);

	__history[__history.length - 1].current = false;//atualizando a tela anterior que ela não é mais a tela vigente
	__history.push( {'pageUrl' : pageUrl, 'panelId' : panelId, 'postData' : postData, 'current': true} );// add page to history
	//__history = __history.slice(__history.length > HISTORY_LIMIT? __history.length - HISTORY_LIMIT : 0, __history.length);//mantendo o tamanho máximo do __history em HISTORY_LIMIT
	return true;
}

//constrói o html o bar, a função reconstruct, por sua vez, reconstrói a variável __bar.
function constructBar(isBack){
	$(".my-breadcrumb").html("");

	if(isBack && __bar.length == 1){
		reconstrucBar();
	}

	for(key in __bar){
		var label = (typeof Loader.map[__bar[key]] != "undefined")? Loader.map[__bar[key]] : PHP.ucfirst(__bar[key]);
		var elmnt = (location.hash == "#" + __bar[key])? "<li class='active'>" + label + "</li>" :  "<li><a class='crumb-item'>" + label + "</a></li>";

		$(".my-breadcrumb").append( elmnt );
	}

	$(".my-breadcrumb .crumb-item").click(function(){
		var go = $(this).index( ".my-breadcrumb .crumb-item" ) - $(".my-breadcrumb li.active").index(".my-breadcrumb li");

		if(go >= 0)
			go++;

		window.history.go(go);
	});
}

//quando é executado window.history.go(-1) e é encontrado outro encadeiamento de acesso à páginas, o breadcrumb precisa ser reconstruído para representar esses acessos do início o fim.
function reconstrucBar(){
	begin_set = Math.log(0);
	__bar = [];

	for (var i = __history.length - 1; i >= 0; i--) {

		if(__history[i].current){
			begin_set = i;
			__bar.unshift(__history[i].pageUrl.split('.').shift());

			if(!__history[i].postData || __history[i].postData.__break_crumb)
				break;
		}

		if(begin_set > i){
			if(__history[i].postData && !__history[i].postData.__break_crumb)
				__bar.unshift(__history[i].pageUrl.split('.').shift());
			else{
				__bar.unshift(__history[i].pageUrl.split('.').shift());
				break;
			}
		}

	}
}

//intercept f5

$(document).on("keydown", function (e){
	if ((e.which || e.keyCode) == 116 && !e.ctrlKey){
		e.preventDefault();

		Loader.refreshCurrentPage();
	}else if(((e.which || e.keyCode) == 116 && e.ctrlKey)){
		e.preventDefault();
		
		window.location = CONFIG_ADMIN_URI;
	}
});

//end history manipulator