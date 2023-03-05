<?php

    //SANITIZE_STRINGS
    function sanitize_strings($arr) {

        foreach($arr as $ky=>$val) {
            $arr[$ky] = trim($val);
            $arr[$ky] = filter_var($arr[$ky],FILTER_SANITIZE_STRING);
        }

        return $arr;
    }

    // === FILE_GET_CONTENTS_CURL ===
	function file_get_contents_curl($url) {
		$ch = curl_init();
	 
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
		curl_setopt($ch, CURLOPT_URL, $url);
		 
		$data = curl_exec($ch);
		curl_close($ch);
		 
		return $data;
		
	}


	// === DISPLAY ACCUMULATED MESSAGES ===
	function display_messages($msg_arr,$die=true) {
		echo '<h3>Cumulative messages:  </h3>';
		
		foreach($msg_arr as $msg) {
			echo '<p>'.$msg.'</p>';
		}
		
		if ($die) {
			die;
		}
	}

	
	// === Pretty Print ===
	function pp ($label,$var) {
		echo '<br>'.$label.'<pre>';
		var_dump($var);
		echo '</pre><br>';
	}


    // === PRINT_VAL ===
	function print_val($val,$dec='',$color=''){
		if (empty($dec) && $dec != 0) {
			$dec = 2;
		} 
				
		$fmt_str = number_format(abs($val),$dec);
		
		if (empty($color)) {
			$color = false;
		}
		
		if ($fmt_str == 0) {
			$zeroPlaceholder = '-';
			return $zeroPlaceholder;
		
		} else {
			if ($color === true) {
				return ($val < 0 
					? '<span style="color:#FF0000">('.$fmt_str.')</span>'
					: sprintf($fmt_str.'&nbsp;', abs($val)));
			} else {
				return ($val < 0 
					? sprintf('('.$fmt_str.')', abs($val)) 
					: sprintf($fmt_str.'&nbsp;',abs($val)));
			}
		}
	}


    //=== FATAL_HANDLER ===
    function fatal_handler() {
        $errfile = "unknown file";
        $errstr  = "shutdown";
        $errno   = E_CORE_ERROR;
        $errline = 0;

        $error = error_get_last();

        if ( $error !== NULL) {
            $errno   = $error["type"];
            $errfile = $error["file"];
            $errline = $error["line"];
            $errstr  = $error["message"];

            format_error( $errno, $errstr, $errfile, $errline);
        }
    }

    register_shutdown_function( "fatal_handler" );


    //=== FORMAT_ERROR ===
	function format_error( $errno, $errstr, $errfile, $errline ) {
		$trace = print_r( debug_backtrace( false ), true );
		
		$content  = "<table><thead style='background-color: #c8c8c8'><th>Item</th><th>Description</th></thead><tbody>";
		$content .= "<tr><td><b>Error</b></td><td><pre>$errstr</pre></td></tr>";
		$content .= "<tr><td><b>Errno</b></td><td><pre>$errno</pre></td></tr>";
		$content .= "<tr><td><b>File</b></td><td>$errfile</td></tr>";
		$content .= "<tr><td><b>Line</b></td><td>$errline</td></tr>";
		$content .= "<tr><td><b>Trace</b></td><td><pre>$trace</pre></td></tr>";
		$content .= '</tbody></table>';
	
	    pp('FATAL ERROR: ',$content);
	    die;
	    //return $content;
	}
