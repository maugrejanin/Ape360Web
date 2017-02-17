<?php

const
	CONFIG_DB_HOST = "db-erkr-small-01.chdryemrlq7b.sa-east-1.rds.amazonaws.com",
	CONFIG_DB_NAME = "db_hering_dev",
	CONFIG_DB_USER = "hering2016_site",
	CONFIG_DB_PWD = "H3r1ng2016!",

	// CONFIG_DB_HOST = "localhost",
	// CONFIG_DB_NAME = "db_hering_dev",
	// CONFIG_DB_USER = "root",
	// CONFIG_DB_PWD = "EuS2Eurekaria123$",

	CONFIG_DB_CHARSET = "utf8",
	CONFIG_DB_PERSISTENT = false,
	CONFIG_SMTP_AUTH = true,
	CONFIG_SMTP_SERVER = "smtplw.com.br",
	CONFIG_SMTP_USER = "eurekaria",
	CONFIG_SMTP_PWD = "EAVCvWpd8817",
	CONFIG_SMTP_PORT = 587,
	CONFIG_SMTP_SECURE = 'tls',
	CONFIG_SENDMAIL_PATH = 'usr/sbin/sendmail/',
	CONFIG_MAIL_FROM = "naoresponda@amigosecretoenahering.com.br",
	CONFIG_MAIL_FROM_NAME = "Amigo Secreto é na Hering",
	CONFIG_MAIL_REPLY_TO = "naoresponda@amigosecretoenahering.com.br",
	CONFIG_MAIL_REPLY_TO_NAME = "Amigo Secreto é na Hering",
	CONFIG_MAIL_HTML = true,
	CONFIG_MAIL_LANGUAGE = "br",
	CONFIG_MAIL_CHARSET = 'utf-8',
	CONFIG_MAIL_TEST = true,
	CONFIG_MAIL_TEST_TO = "leonardo@eurekaria.com",
	CONFIG_MAIL_TEST_TO_NAME = "Amigo Secreto é na Hering",
	CONFIG_WEBSITE_NAME = "Amigo Secreto é na Hering",
	CONFIG_WEBSITE_NAME_UTF = "Amigo Secreto é na Hering",
	CONFIG_J_ALERT_TITLE = "",
	CONFIG_OWNER_NAME = "Amigo Secreto é na Hering",
	CONFIG_CLIENT_GENDER = "o",
	CONFIG_CLIENT_NAME = "Amigo Secreto é na Hering",
	CONFIG_YEAR = "2016",
	CONFIG_DIRETORIO_ANEXOS = "anexos",
	CONFIG_CRON_DIRETORIO_LOG = "logs/cron/",
	CONFIG_CRON_ECHO = true,
	CONFIG_ACTION_DEFAULT = 'init',
	CONFIG_SALT_TOKEN = '?4f8{W7hf8*&uf90PD',
	VALOR_NUMERO_DA_SORTE = 50,
	VALOR_MAXIMO_CUPOM_APROVADO = 2000,
	TAMANHO_MAXIMO_UPLOAD_MB = 5,
	CONFIG_COOKIE_LIFETIME = 86400,
	CONFIG_PROTOCOL = "http",
	CONFIG_DOMAIN = 'eurekaria.git',
	CONFIG_ROOT_PATH = 'hering/www/';
	// CONFIG_PROTOCOL = "https",
	// CONFIG_DOMAIN = 'apps.eurekaria.com',
	// CONFIG_ROOT_PATH = 'hering/www/';

	define('CONFIG_SERVER_PATH', CONFIG_ROOT_PATH . 'server/');
	define('CONFIG_BASEURL', CONFIG_PROTOCOL . '://' . CONFIG_DOMAIN . '/' . CONFIG_SERVER_PATH);
	define('CONFIG_BASEURL_CLIENT', CONFIG_PROTOCOL . '://' . CONFIG_DOMAIN . '/' . CONFIG_ROOT_PATH);