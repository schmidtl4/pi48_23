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

    const ROOT = 'http://rev23.pineisland48.com';
	define('SERVER_ROOT', $_SERVER['DOCUMENT_ROOT'].'/');

    const JSON_ROOT = SERVER_ROOT.'data/';
    const DATA_ROOT = '/home/pineisla/Data_and_logs/';
    const DB_ROOT = DATA_ROOT;
    //const REMOTEIP = '184.175.68.18';

    const SEND_EMAILS = 'on'; //'on' or 'off'

    const MYSQL_SUFFIX = '';

    const DEFAULT_DB = '';

    const MYSQL_HOST = '';

    const MYSQL_PORT = 3306;
    const IP_ROOT = DATA_ROOT;
    const ERROR_LOG = DATA_ROOT.'logs/pi48_23.log';

    const DEFAULT_RECIPIENT = 'les@briia.io';
    const DEFAULT_NAME = 'Les Schmidt (BRIIA)';
    const SMTP_DEBUG_LEVEL = 0;
    const SMTP_PORT = 465;
    const SMTP_SECURE = 'ssl';
    const SMTP_AUTH = TRUE;

    const DEFAULT_TIMEOUT = 'never';

    const ORIGIN = 'HOSTEK';
