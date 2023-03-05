<?php
	//based largely on https://stackoverflow.com/a/7313887 
	set_error_handler("errorHandler");
    if (HANDLE_SHUTDOWN) {
		register_shutdown_function("shutdownHandler");
	}
	//from comments on get_defined_vars() at http://www.php.net/manual/en/function.get-defined-vars.php
	$vars = get_defined_vars();

	//ERRORHANDLER
	function errorHandler($error_level, $error_message, $error_file, $error_line, $error_context) {
		
		$error = '<b>[ERROR]</b>: '.$error_message;

		switch ($error_level) {
			case E_ERROR:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			case E_PARSE:
				$errorType = "FATAL";
				break;
			case E_USER_ERROR:
			case E_RECOVERABLE_ERROR:
				$errorType = "ERROR";
				break;
			case E_WARNING:
			case E_CORE_WARNING:
			case E_COMPILE_WARNING:
			case E_USER_WARNING:
				$errorType = "WARN";
				break;
			case E_NOTICE:
			case E_USER_NOTICE:
				$errorType = "NOTICE";
				break;
			case E_STRICT:
				$errorType = "DEBUG";
				break;
			default:
				$errorType = "WARN";
		}
		
		$host = $_SERVER['HTTP_HOST'];
		$dt = new DateTime();
		$url = $_SERVER['REQUEST_URI'];
		
		$referer = "NONE";
		if (!empty($_SERVER['HTTP_REFERER'])) {
			$referer = $_SERVER['HTTP_REFERER'];
		}
		
		$rem_host = "NOT SET";
		if (!empty($_SERVER['REMOTE_HOST'])) {
			$rem_host = $_SERVER['REMOTE_HOST'];
		}
	
		$trace = stack_trace(FALSE, TRUE, TRUE);			
		$error = $error.'<br><b>[URI]</b>: '.$url;		
		$error .= '<br><b>[TRACE]</b>: '.$trace;
		$error .= '<br><b>[REFERER]:</b> '.$referer;
		$error .= '<br><b>[REMOTE HOST]: </b>'.$rem_host;

		
		if ($host != 'projects.casadiablo.net') {

			echo '<br><br><b>'.$errorType.'</b> '.$error;

			//only to test email functionality - comment out most of the time
			//emailit($error,$errorType,$dt,$host);

			//only to test display functionality - normally comment this line out
			//require_once(SERVER_ROOT.'uhoh.php');
			
		
		} else { //email if error occurs on briia.io

			emailit($error,$errorType,$dt,$host);

			//if (!empty($error) && $errorType == 'FATAL') {
			require_once(SERVER_ROOT.'uhoh.php');
			//}
			
		}
		
		//always log (all hosts)
		logit($error,$errorType,$dt);
		
		die;
	}

	
	//SHUTDOWNHANDLER
	function shutdownHandler()
	{//will be called when php script ends.

		$errorType = "FATAL";
		$url = $_SERVER['REQUEST_URI'];

		$dt = new DateTime();
		$host = $_SERVER['HTTP_HOST'];

		$error = '';
		$lasterror = error_get_last();

		if (!is_null($lasterror)) {
			switch ($lasterror['type']) {
				case E_ERROR:
				case E_CORE_ERROR:
				case E_COMPILE_ERROR:
				case E_USER_ERROR:
				case E_RECOVERABLE_ERROR:
				case E_CORE_WARNING:
				case E_COMPILE_WARNING:
				case E_PARSE:
					$error = "<b>file: " . $lasterror['file'] . "  at line: " . $lasterror['line'] . "</b><br>[SHUTDOWN] lvl: " . $lasterror['type'] . " <br>[MSG]  " . $lasterror['message'];
					break;

				default;
					exit();

			}
		}
		
		if (!empty($error)) {
			$error = '<b>[URI]</b>: '.$url.'<br>'.$error; 
		 
			if ($host != 'todo') {

				echo '<br><br><b>'.$errorType.'</b> ~ '.$error;
				
				//only to test email functionality - comment out most of the time
				//emailit($error,$errorType,$dt,$host);

				//only for test
				//require_once(SERVER_ROOT.'uhoh.php');
				
			} else { //email from staging (to test) and briia.io, not dev

				require_once(SERVER_ROOT.'uhoh.php');

				emailit($error,$errorType,$dt,$host);
			}
		}
		//always log (all hosts)
		if (!empty($error)) {
			logit($error,$errorType,$dt);

		}
		
		exit() ;
	}
	

	//LOGIT
	function logit($error, $errlvl, $dt)
	{
		$error = str_ireplace("<br>","\r\n",$error);
		$error = strip_tags($error);
		$error = str_ireplace("&nbsp;","",$error);
		$error = "*** ".$dt->format('Y-m-d H:i:s')." *** ".$errlvl."\r\n".$error;
		error_log($error,3,ERROR_LOG);
	}
	
	
	//EMAILIT
	function emailit($error,$errlvl,$dt, $host) {
				
		if (!defined('SMTP_SERVER')) {
			mail_handler::define_vars($host);
		}
		
		$recipients =['les@briia.io' => 'Les Schmidt'];
		$cc_s = [];
		$bcc_s = [];
		$from = 'errors@briia.io';
		$subject = "briia.io - ".$errlvl." error! [".$dt->format('Y-m-d H:i:s')."]";
		$email_content = "
				<html>
				<body style='font-size:14px;'>
				
				<p>A ".$errlvl." error occurred on <b>".$host.": </b></p>
				
				<p>".$error."</p>
				
				<p>SERVER[REMOTE_ADDR]: ".$_SERVER['REMOTE_ADDR']."</p>
	        	
	        	</body>
	        	</html>";

		$email = new send_mail($recipients,$cc_s,$bcc_s,$from,$subject,$email_content);
			
		$email->send_it();
		
	}
	
	
	//DEBUG_STRING_BACKTRACE
	//from comment by dany.dylan@gmail.com at http://php.net/manual/en/function.debug-print-backtrace.php
    /* function debug_string_backtrace() {
        ob_start();
        debug_print_backtrace();
        $trace = ob_get_contents();
        ob_end_clean();

        // Remove first item from backtrace as it's this function which
        // is redundant.
        $trace = preg_replace ('/^#0\s+' . __FUNCTION__ . "[^\n]*\n/", '', $trace, 1);

        return $trace;
    } */ 

	/**
	 * Print out a stack trace from entry point to wherever this function was called.
	 * @param bool $show_args Show arguments passed to functions? Default False.
	 * @param bool $for_web Format text for web? Default True.
	 * @param bool $return Return result instead of printing it? Default False.
	 */
	 
	//STACK_TRACE
	//from:  https://gist.github.com/JaggedJax/3837352
	function stack_trace($show_args=false, $for_web=true, $return=false){
		if ($for_web){
			$before = '<b>';
			$after = '</b>';
			$tab = '&nbsp;&nbsp;&nbsp;&nbsp;';
			$newline = '<br>';
		}
		else{
			$before = '<';
			$after = '>';
			$tab = "\t";
			$newline = "\n";
		}
		$output = '';
		$ignore_functions = ['include','include_once','require','require_once'];
		$backtrace = debug_backtrace();
		$length = count($backtrace);
		
		for ($i=0; $i<$length; $i++){
			$function = $line = '';
			$skip_args = false;
			$caller = @$backtrace[$i+1]['function'];
			// Display caller function (if not a require or include)
			if (isset($caller) && !in_array($caller, $ignore_functions)){
				$function = ' in function '.$before.$caller.$after;
			}
			else{
				$skip_args = true;
			}
			
			if (isset($backtrace[$i]['file'])) {
				$line = $before.$backtrace[$i]['file'].$after.$function .' on line: '.$before.$backtrace[$i]['line'].$after.$newline;
			}
			if ($i < $length-1){
				if ($show_args && $backtrace[($i+1)]['args'] && !$skip_args){
					$params = ($for_web) ? htmlentities(print_r($backtrace[($i+1)]['args'], true))
							: print_r($backtrace[($i+1)]['args'], true);

					$line .= $tab.'Called with params: '.preg_replace('/(\n)/',$newline.$tab,trim($params)).$newline.$tab.'By:'.$newline;
					unset($params);
				}
				else{
					$line .= '<b>[CALLED BY]</b>: ';
				}
			}
			if ($return){
				$output .= $line;
			}
			else{
				echo $line;
			}
		}
		
		if ($return){
			return $output;
		}
		
		return $return;
	}
