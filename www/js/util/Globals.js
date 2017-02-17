var 
	__hidden;

var 
	CONFIG_CLIENT_URI = 'http://eurekaria.git/hering/www/',
	CONFIG_ADMIN_URI = 'http://eurekaria.git/hering/www/admin',
	CONFIG_SERVER_URI = 'http://eurekaria.git/hering/www/server/',
	// CONFIG_CLIENT_URI = 'https://apps.eurekaria.com/hering/www/',
	// CONFIG_ADMIN_URI = 'https://apps.eurekaria.com/hering/www/admin',
	// CONFIG_SERVER_URI = 'https://apps.eurekaria.com/hering/www/server/',
	CONFIG_TIME_INTERVAL_EVENT_UPDATE = 10000,//microseconds
	CONFIG_TIME_INTERVAL_DASHBOARD_UPDATE = 10000,
	
	FORCE_UPDATE_IMG = false,
	PATH_IMG = "img",
	TIME_TO_LOAD_TOOLTIPS = 500,
	TIME_TO_FADEOUT_EVENTS = 100,
	TIME_TO_FADEIN_EVENTS = 120,

	MIN_WIDTH_COLUMN_TABLE = 140,//pixel
	DATE_EXPIRE_PROMOTION = '01 04 2017',//Mês(1-12) Dia Ano

	//Compartilhado com o PHP ---------------------------------------------------------

	SPECIFIC_ERROR_EVENT_CROWDED = 1,

	ID_PROFILE_ADM = 1,
	ID_PROFILE_PARTICIPANT = 2,

	CONFIG_MODE_DEVELOPMENT = 1,
	CONFIG_MODE_PRODUCTION = 2,
	CURRENT_MODE = 1,
	USER_OBJ = "",
	COMPRAS = {};

var PATH_TEMPLATE = './template/';
var PATH_CUPOM = PATH_IMG + '/cupom/';
var PATH_IMG_NAVIGATOR = PATH_IMG + '/navegador/';