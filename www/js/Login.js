function Login(){

	function login(e){
		var email = document.getElementById('user').value;
		var senha = document.getElementById('password').value;

		if(!email || !senha){
			Dialog.alert('Preencha usuário e senha para prosseguir');
			return false;
		}

		Diplomat.fastRequest(function(server_data){
			window.sessionStorage['nm_denied_permissions'] = JSON.stringify(server_data.nm_denied_permissions);
			window.location = 'Home.php';
		}, {
			__action: 'login', 
			email: email,
			senha: senha
		});
	}

	function gotoSite(e) {
		window.open("index.php", "_self");
	}

	function reenviarSenha(){
		alert('Em breve! =)');
	}

	function submitLogin(e) {
		if (e.keyCode == 13)
			if (!login())
				return false;
	}

	function handleEvents(){
		document.getElementById('btnEntrar').onclick = login;
		document.getElementById('btnSite').onclick = gotoSite;
		// document.getElementById('btnEsqueciSenha').onclick = reenviarSenha;
		document.getElementById('user').onkeypress = submitLogin;
		document.getElementById('password').onkeypress = submitLogin;
	}

	this.init = function(){
		handleEvents();
		document.getElementById("user").focus();
	}

}