<?php

	if (!defined('siteSafetyKey')) {
		
		header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
		header('Location: /errors/err_master?error=404');
		die;
	}
	
	//set domain-specific environment
	ini_set('log_errors',0); //logging handled by error_handler
	ini_set('display_errors',0);  //should be 0; display (echo)  is handled by error_handler below
	ini_set('error_reporting',E_ALL);

    const ROOT = 'http://pi48_23/';
	define('SERVER_ROOT', $_SERVER['DOCUMENT_ROOT'].'/');

    const JSON_ROOT = SERVER_ROOT.'data/';
    const DATA_ROOT = 'c:/Apache24/Data_and_logs/';
    const DB_ROOT = DATA_ROOT.'pi48/';
    const REMOTEIP = '184.175.68.18';

    const SEND_EMAILS = 'on'; //'on' or 'off'

    const MYSQL_SUFFIX = '_dev';

    const DEFAULT_DB = '';

    const MYSQL_HOST = "127.0.0.1";

    const MYSQL_PORT = 3308;
    const IP_ROOT = DATA_ROOT.'ip/';
    const ERROR_LOG = DATA_ROOT.'logs/pi48_23_dev.log';

    const DEFAULT_RECIPIENT = 'les@briia.io';
    const DEFAULT_NAME = 'Les Schmidt (BRIIA)';
    const SMTP_DEBUG_LEVEL = 0;
    const SMTP_PORT = 465;
    const SMTP_SECURE = 'ssl';
    const SMTP_AUTH = TRUE;

    const DEFAULT_TIMEOUT = 'never';

    const ORIGIN = 'MUNCHER';
