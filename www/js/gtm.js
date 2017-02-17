function gtmPageView(url) {
	if (typeof dataLayer !== 'undefined') {
		pageTitle = pageMap[url];
		console.log("Acesso a " + pageTitle);
		dataLayer.push({
			'event':'VirtualPageview',
			'virtualPageURL':url,
			'virtualPageTitle' : pageTitle
		});
	}
}

function gtmEvent(eventVars) {
	if (typeof dataLayer !== 'undefined') {
		dataLayer.push(eventVars);
	}
}

var pageMap = {
	"pnl_home": "Home",
	"pnl_regulamento": "Regulamento",
	"pnl_cupons": "Meus cupons",
	"pnl_cadastro": "Cadastro",
	"pnl_novo_cupom": "Novo cupom",
	"pnl_mecanica": "Como funciona",
	"pnl_esqueci_senha": "Esqueci a senha",
	"pnl_alterar_senha": "Alterar senha",
};