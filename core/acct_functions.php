<?php

	//CONFIG
	function config() {
		/***************
			limit to 10-20 characters each to avoid collisions. 
		****************/
		//define('SALT1', '24859f@#$#@$');
		//define('SALT2', '^&@#_-=+Afda$#%');
		
		// default the error variable to empty.
		//$_SESSION['msg'] = "";
	}
	

	//CHECK_LOGIN 
	function check_login ($postdata) {

		//$_SESSION = NULL;
		$msg = [];
		//$msg[0] = 'error'; //default

		switch (strtolower($postdata['action'])) {

		    case 'login':

			    //dbg(__CLASS__.' | '.__FUNCTION__.' | '.__LINE__.' | check login', '*');
				if (isset($postdata['u_name']) && isset($postdata['u_pw'])) {

					// We have both variables. Pass them to our validation function
					if (!validateUser($postdata['u_name'], $postdata['u_pw'])) {
						// Well there was an error. Set the message and unset
						// the action so the normal form appears.
						$_SESSION = NULL;
                        $msg[0] = 'error';
                        $msg[1] = "<span class='alert alert-danger'>Username or password are invalid.</span>";

					}  else { 
						$_SESSION['u_name'] = $postdata['u_name'];
						$_SESSION['loggedin'] = true;
                        //$msg[0] = 'valid';
					}
					
				} else {
					$msg[1] = "<span class='alert alert-danger'>Username and Password are required to login.</span>";
				}
                dbg(__CLASS__.' | '.__FUNCTION__.' | '.__LINE__.' | msg', $msg);
			break;
			
			case 'logout':
				// If they are logged in log them out.
				// If they are not logged in, well nothing needs to be done.
				if (loggedIn()) {
					logoutUser();
					$msg[0] = '';
					$msg[1] = 'You have been successfully logged out.';
				} else {
					// unset the action to display the login form.
					unset($_GET['action']);
				}
			break;

			case 'register':
				// If the form was submitted lets try to create the account.
				// note: core.js checks for empty values before submitting & email field is html5 validated
				if (isset($postdata['u_name']) && isset($postdata['u_pw'])) {
					
					$nu_acct = createAccount($postdata['u_name'], $postdata['u_pw']);
					
					if ( !is_array($nu_acct)) {
						$_SESSION['uid'] = $nu_acct;
						$msg[0] = '';
						$msg[1] = '<span class="alert-success">Welcome '.$postdata['u_name'].'!<br>A new account has been created.<br>You can now <b><a href="/index.php" style="color:#333">login here</a></b></span>.';
											
					} else {
						$msg = $nu_acct;
						// unset the action to display the registration form.
						unset($_GET['action']);
					}
					 				
				} else {
					
					$msg[1] = '<span class="alert-warning">"Username and/or Password was missing.</span>';
				}
			break;
		}

        if (!empty($msg)) {
            echo json_encode($msg);
        }
	
	}
	
	
	//CREATEACCOUNT
	function createAccount($pUsername, $pPassword) {
		$msg = array();
		$msg[0] = 'error';
		
		$pc = new main_crud();
		
		// First check we have data passed in.
		if (!empty($pUsername) && !empty($pPassword)) {
			$uLen = strlen($pUsername);
			$pLen = strlen($pPassword);
			
			$eUsername = filter_var($pUsername,FILTER_SANITIZE_STRING);
			
			$result = $pc->get_user_cred($eUsername);
			
			// Note the use of trigdger_error instead of or die.
			//$query = mysql_query($sql) or trigger_error("Query Failed: " . mysql_error());
	
			// Error checks 
			if ($uLen < 3) {
				$msg[1] = "Username must be at least 3 characters.";
			
			} elseif ($pLen < 6) {
				$msg[1] = "Password must be longer then 6 characters.";
			
			}elseif (count($result) == 1) {
				$msg[1] = "Username already exists.";
			
			} else {
				$msg[0] = '';
				$msg[1] = 'Account sucessfully created.';
				// All errors passed lets
				// Create our insert SQL by hashing the password and using the escaped Username.
				$hashPw = hashpassword($pPassword);
				
				$pc->create_account($eUsername,$hashPw);

				if (!empty($pc->lastRowid)) {
					return $pc->lastRowid;
				} else {
					return $msg;
				}
			}
		}
	}
	
	
	//HASHPASSWORD
	function hashPassword($pPassword, $pSalt1="2345#$%@3e", $pSalt2="taesa%#@2%^#") {
		return sha1(md5(SALT2 . $pPassword . SALT1));
	}
	
	
	//LOGGEDIN
	function loggedIn() {
		
		//echo 'loggedin???';
		//pp('session acct functions loggedin',$_SESSION);
		//die;
		
		// check both loggedin and username to verify user.
		if (isset($_SESSION['loggedin']) && isset($_SESSION['u_name'])) {
			return true;
		}
		
		return false;
	}


	//LOGOUTUSER
	function logoutUser() {
		// using unset will remove the variable
		// and thus logging off the user.
		unset($_SESSION['u_name']);
		unset($_SESSION['loggedin']);
		
		return true;
	}
	

	//VALIDATEUSER
	function validateUser($pUsername, $pPassword) {
		
		$ac = new admin_crud();

        $data = $ac->check_credentials($pUsername, $pPassword);

        dbg(__CLASS__.' | '.__FUNCTION__.' | '.__LINE__.' | user account(s) found', $data);
				
		// If one row was returned, the user is now logged in!
		if (count($data) == 1) {
			$row = $data[0];
			$_SESSION['u_name'] = $row['u_name'];
			$_SESSION['uid'] = $row['UID'];
			$_SESSION['loggedin'] = true;

			return true;
		}
		
		
		return false;
	}
