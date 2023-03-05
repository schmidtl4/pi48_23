<?php
    //*********************//
    //     AUTHENTICATE    //
    //*********************//
    if (!defined('siteSafetyKey')) {
        header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
        header('Location: /errors/err_master?error=404');
        die;
    }

    //*********************//
    //      FUNCTIONS      //
    //*********************//

    //DBG FUNCTION
    //NOTE - local must be loaded before any calls to this function
    function dbg($msg, $var)
    {
        $dt = new DateTime();

        $UID = 'no UID';
        if (!empty($_SESSION['uid'])) $UID = $_SESSION['uid'];

        error_log($dt->format('Y-m-d H:i:s').' | '.$_SERVER['REMOTE_ADDR'].' | '.$UID.' | '.$msg.': '.var_export($var,
                TRUE).PHP_EOL, 3, ERROR_LOG);
    }


    // REGENERATE SESSIONID TO AVOID session fixation attacks
    function regenerateSessionId(): bool
    {
        $regenerated = FALSE;

        //if session[created] doesn't exist yet, create it
        if (!isset($_SESSION['CREATED'])) {

            $_SESSION['CREATED'] = time();
            //session[created] exists but was started more than DEFAULT_TIMEOUT minutes ago

        } elseif ($_SESSION['timeout'] == 'never') {

            if ((time() - $_SESSION['LAST_ACTIVITY']) > 1800) {
                session_regenerate_id();    // change session ID for the current session and invalidate old session ID
                $regenerated = TRUE;
            }
            dbg(__CLASS__.' | '.__FUNCTION__.' | '.__LINE__.' | time is', time());

        } elseif ( (time() - $_SESSION['LAST_ACTIVITY']) > $_SESSION['timeout'])  {
            session_regenerate_id();
            $regenerated = TRUE;
        }

        return $regenerated;
    }


    //CHEWCK IDELTIME GREATER THAN TIMEOUT VALUE
    function checkIdleTime(): string
    {
        $idleTime = 0;
        if (!empty($_SESSION['LAST_ACTIVITY'])) {
            $idleTime = time() - $_SESSION['LAST_ACTIVITY'];
            $_SESSION['idleTime'] = $idleTime;
        }

        if ($_SESSION['timeout'] != 'never' && $idleTime > $_SESSION['timeout']) {

            //test if the current transaction is a "save" (try to preserve data after the session has exceeded the timeout)
            if (!empty($_POST['action']) && stripos($_POST['action'], 'save') !== FALSE) {
                //complete the save transaction before killing session
            }

            return 'expired';

        } else {
            $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
            return 'OK';
        }
    }


    //SET TIMEOUT
    function setTimeout()
    {
        if (!empty($_SESSION['u_name']) && $_SESSION['u_name'] == '') {
            $_SESSION['timeout'] = 'never';

        } elseif (DEFAULT_TIMEOUT > 0) {
            $_SESSION['timeout'] = DEFAULT_TIMEOUT;

        } else {
            $_SESSION['timeout'] = 60 * 5; //5 minutes allowed to log in
        }

    }


    //CHECK PUBLIC URLS
    function checkPublicUris($host)
    {
        $publicURIs = [
            '/',
            '/index.php'
        ];

        //if not logged in AND page requested is not a public page, destroy session and go to log in
        if (empty($_SESSION['uid']) && !in_array($_SERVER['REQUEST_URI'], $publicURIs)) {  //i.e. not logged in

            dbg('base_config.php | '.__LINE__.' | Request for a non-public page and not logged in. Request was',
                $_SERVER['REQUEST_URI']);

            echo '<script>window.location.href="/";</script>';

            dbg(__CLASS__.' | '.__FUNCTION__.' | '.__LINE__.' | destroying $_SESSION; was', $_SESSION);

            session_destroy();
            session_unset();
            exit();
        }
    }


    //SESSION DESTROY
    function sessionDestroy()
    {
        $_SESSION = [];
        session_destroy();   // destroy session data in storage
        session_unset();     // unset $_SESSION variable for the run-time
        echo '<script>window.location.href="/?expired=true";</script>';
        exit();
    }


    // CLASS AUTOLOADER
    function pi48_autoloader($class)
    {
        if (strpos($class, '\\') === FALSE) {

            $classPath = SERVER_ROOT.'classes/'.$class.'.class.php';

            require($classPath);
        }
    }

    //************************//
    //     PROCESS REQUEST    //
    //************************//


    $host = $_SERVER['HTTP_HOST'];

    switch ($host) {

        case 'rev23.pineisland48.com';
            require_once('local_production.php');
            break;

        default: //local dev
            require_once('local.php');
            break;
    }


    dbg('*******************', '*');
    dbg(' | base_config.php | '.__LINE__.' | *** START CONFIG *** :: page requested was', $_SERVER['REQUEST_URI']);

    date_default_timezone_set('America/Los_Angeles');

    if (!defined('HANDLE_SHUTDOWN')) {
		define('HANDLE_SHUTDOWN', true);
	}

	spl_autoload_register('pi48_autoloader');

	require_once(SERVER_ROOT.'vendor/autoload.php');

    require_once('acct_functions.php');

    require_once('creds.php');

    require_once('includes.php');

    const AES_PHRASE = 'Super secret passphrase';

    //start Zebra_Session
    $sm = new SessionManager();

    if (!defined('SKIP_ERROR_HANDLING')) {  // will be defined after first page of session?
        require_once('error_handling.php');
    }

    setTimeout(); //sets $_SESSION[timeout]

    dbg(' | base_config.php | '.__LINE__.' | timeout is set; $_SESSION[timeout] is', $_SESSION['timeout']);

    $_SESSION['regenerated'] = regenerateSessionId();

    $result = checkIdleTime();

    if($result == 'expired') {
        sessionDestroy();
    }

    checkPublicUris($host);  //exits if URI is not public

    dbg(' | base_config.php | '.__LINE__.' | *** END CONFIG ***  *** SERVING REQUESTED PAGE ***',
    $_SERVER['REQUEST_URI']);
