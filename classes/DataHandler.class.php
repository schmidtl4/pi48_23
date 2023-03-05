<?php
	
	class DataHandler {
	
	    public $qry;
	    public $lastRowid;
	    public $debug;
	    public $qry_result;
	    public $errMsg;

        /**
         * @param mixed $debug
         */
        public function setDebug($debug)
        {
            $this->debug = $debug;
        }
	    
		//__CONSTRUCT
		function __construct(string $db, $debug = FALSE)
        {
			$this->debug = $debug;
			$this->debug = filter_var($debug,FILTER_VALIDATE_BOOLEAN);

			//adapt db to various environments (dev, staging, production)
			$db_parts = explode("_", $db);
			
			if (count($db_parts) > 2) {
				end($db_parts);
				
				if (empty(MYSQL_SUFFIX)) {
					$db = $db_parts[0].'_'.$db_parts[1];
				
				} elseif ($db_parts[key($db_parts)] == ltrim(MYSQL_SUFFIX,"_")) {
					//do nothing - dbname already has local suffix.
				} else {
					$i=0;
					$db = "";
					while($i < count($db_parts)) {
						$db .= $db_parts[$i].'_';
						$i++;
					}
					$db = rtrim($db,"_");
					$db .= MYSQL_SUFFIX;
				}
			
			} elseif (count($db_parts) == 2 && !empty(MYSQL_SUFFIX)) {
				
				$db .= MYSQL_SUFFIX;
			}
			
			$dbtrim = trim($db);
			
			$this->dbn = $dbtrim;			 
			
			if (empty($dbtrim)) {
				echo '<br>***ERROR*** no database specified.';
				die;
			}
			//dbg(__CLASS__.' | '.__FUNCTION__.' | '.__LINE__.' | this->dbn',$this->dbn);

			$this->qry_result = [];
			$this->errors = [];
				
			$dt = new DateTime();
			$this->today = $dt->format('Y-m-d');
			$this->nowtime = $dt->format('Hi');
			$this->now_str = $this->today.' '.$this->nowtime;			
			
		}
			
		
		/*** FETCH_DATA ***/
		/*
		returnData argument:
			if set to true (default): returns the values or empty array if no data
			if set to false: returns true (data found) or false (no data) but data is not returned
			groupData uses PDO:FETCH_GROUP in fetchAll method
		4/11/15: error handling now built in: display_messages if errors not empty
		*/
		function fetch_data($returnData = true, $groupData=false)
        {
			
			$this->lastRowid = '';
			$this->rowCount = '';		
			echo '<br>this->dbn: '.$this->dbn;

			try {
				$this->set_db_connection($this->dbn);
			}
			catch (Throwable $e) {
				if (!empty($dbh->errors) && $this->debug) {
					$err_msg = 'data-fetch msg: errors when establishing connection ';
					echo $err_msg;
					pp('dbh->errors', $dbh->errors);
					echo '<br>'.$db;
					die;
				}
			}
			
			//execute query
			try {
				$dbh_stmt = $this->db->prepare($this->qry);
			} 
			catch (Throwable $e) {
					
				if ($this->debug === true) {
					pp('exception', $e);
					echo '<br>'.$this->qry;
				}
				
				if (!empty($dbh_stmt->errors) and $this->debug === true) {
					echo '<br>QUERY PREPARATION ERROR: '.$this->err_msg;
					die;
				}
				
				$errmsgs = $this->handle_error_msg('', $e);
					
				foreach ($errmsgs as $kn => $str) {
					$this->errors[] = $str;
				}
				
				/* if ($this->debug === false) {
					display_messages($_SESSION['msgs']);  //default = die;
					unset($_SESSION['msgs']);
				} */
				
			}
			
			if (!empty($this->params)) {

				foreach($this->params as $ky => &$val) {

					if (is_numeric($ky)) {
						$dbh_stmt->bindValue($ky+1, $val);
					} else {
						$dbh_stmt->bindParam($ky, $val);
					}
				}
			}
			
			if ($this->debug) {
				echo '<br>*** BEGIN dump for diagnostic purposes only ***<br>the error msg for this query is: '.$this->err_msg;
				echo '<br><br>***INFO***<br>DB: '.$this->dbn.'<br>QUERY: '.$this->qry.'<br>';
				echo '<br>debugDumpParams: <br> ';			
				$dbh_stmt->debugDumpParams();
				echo '<br>*** END diagnostic dump ***<br>';
			}
						
			//store result
			try {
				$dbh_stmt->execute();				

			} catch (Throwable $e) {
				
				$this->errors[] = $this->handle_error_msg($dbh_stmt, $e);
				echo "<br>STATEMENT EXECUTION error";
				if ($this->debug) {
					echo("<br>err_msg: ".$this->err_msg."<br>");
					echo '<br>'.$e->getMessage();	
					die;
				}
				
			}
					
			$dbh_stmt->setFetchMode(PDO::FETCH_ASSOC);
			
			if ($groupData) {
				$data = $dbh_stmt->fetchAll(PDO::FETCH_GROUP);
				
			} else {				
				$data = $dbh_stmt->fetchAll();				
			}
			
			if (isset($dbh_stmt->errors)) {
				pp(__LINE__.' - '.strtoupper(__CLASS__).'::'.__FUNCTION__.' || errors ', $dbh_stmt->errors);
			}
				
			//check for errors
			
			if (empty($dbh_stmt->errors)) {
		
				//report existence of results only
				if ($returnData === FALSE) {
					if (count($data) == 0) {
						$data = false;
					} else {
						$data = true;
					}
				}
				
				$this->qry_result = $data;
				
				//get primary key of last addition and number of rows changed
				$this->lastRowid = $this->db->lastInsertId();
				$this->rowCount = $dbh_stmt->rowCount();
				$this->columnCount = $dbh_stmt->columnCount();
				
				//echo '<br>'.__LINE__.' - '.strtoupper(__CLASS__).'::'.__FUNCTION__.' &#8226 $this->columnCount: '.$this->columnCount;
				
				if ($this->debug) {
					echo '<br>*** INFO *** QUERY COMPLETED WITHOUT ERRORS.';
					//pp('this', $this);
					echo '<br>*** end of diagnostic dump ***<br>';
				}
								
				return $data;
			
			}
			
			//close connection
			$this->db = NULL;
		}
		

		/* SET_DB_CONNECTION */
		//db is in the form: 'sqlite:{path}'
		function set_db_connection($db) {
			
			$this->db = NULL;
		
			try {
				$this->db = new PDO($db);  
				$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			} catch (PDOException $e) {
				
				if (isset($this->db)) {
					$err_msg = $this->handle_error_msg($this->db, $e);
					if ($err_msg) {
						foreach($err_msg as $k=>$msg) {
							$this->errors[] = $msg;
						}
					}
					if ($this->debug) {
						echo '<br>this->db is set to: '.$this->db.'<br>';
						var_dump($this->errors);
					}
					$this->db = null;
					die;
							
				} else {
					$this->errors = $this->handle_error_msg('', $e);
					$this->proceed = false;
					if ($this->debug) {
						echo '<br>this->db is not set: '.$this->db.'<br>input was: '.$db.'<br>';
						var_dump($this->errors);
					}
					$this->db = null;
					die;		
				}
			}
					
		}
		
		
		/*** FETCH_DATA_MYSQL ***/
		/*
		returnData argument:
			if set to true (default): returns the values or empty array if no data
			if set to false: returns true (data found) or false (no data) but data is not returned
			groupData uses PDO:FETCH_GROUP in fetchAll method
		4/11/15: error handling now built in: display_messages if errors not empty
		*/
		function fetch_data_mysql($returnData = true, $groupData=false) {
			//dbg('data_handler | '.__LINE__.' | this->dbn', $this->dbn);
			//die ('<br>DIE at line: '.__LINE__.' - '.strtoupper(__CLASS__));
			
			$this->lastRowid = '';
			$this->rowCount = '';		
			
			try {
            if (defined('MYSQL_PORT') && !empty(MYSQL_PORT)) {
					$this->set_mysql_connection(MYSQL_HOST, $this->dbn, MYSQL_PORT);
				} else {
					$this->set_mysql_connection(MYSQL_HOST, $this->dbn);

				}
			}
			catch (Throwable $e) {
                /*echo '<br><pre>';
                var_dump($e);
                echo '</pre><br>';*/

				if (!empty($dbh->errors) && $this->debug) {
					$err_msg = "data-fetch msg: errors when establishing connection ";
					echo $err_msg;
					pp('dbh->errors', $dbh->errors);
					echo '<br>'.$db;
                    $this->db = NULL;
					die;
				} else {
                    $this->db = NULL;
					echo "Sorry. We're having difficulty connecting at the moment. Please try again later.";
					exit();
				}
			}
            //dbg('data_handler | '.__LINE__.' | this->db', $this->db);
			//echo '<br>'.__LINE__.' - '.strtoupper(__CLASS__).'::'.__FUNCTION__.' &#8226 this->dbn: '.$this->db;
			//die ('<br>DIE at line: '.__LINE__.' - '.strtoupper(__CLASS__));
			
			//execute query
			try {
				//echo '<br>'.__LINE__.' - '.strtoupper(__CLASS__).'::'.__FUNCTION__.' &#8226 prepping stmt';
				$dbh_stmt = $this->db->prepare($this->qry);
                //dbg('data_handler | '.__LINE__.' | dbh_stmt', $dbh_stmt);

			} 
			catch (Throwable $e) {
					
				if ($this->debug === true) {
					pp('exception', $e);
					echo '<br>'.$this->qry;
				}
				
				if (!empty($dbh_stmt->errors) and $this->debug === true) {
					echo '<br>QUERY PREPARATION ERROR: '.$this->err_msg;
					die;
				}
				
				$errmsgs = $this->handle_error_msg('', $e);
					
				foreach ($errmsgs as $kn => $str) {
					//$_SESSION['msgs'][] = $str;
					$this->errors[] = $str;
				}
                $this->db = NULL;

                /* if ($this->debug === false) {
                    display_messages($_SESSION['msgs']);  //default = die;
                } */
				
			}
			
			if (!empty($this->params)) {
				//pp('params - dh', $this->params);
				
				//echo '<br>'.__LINE__.' - '.strtoupper(__CLASS__).'::'.__FUNCTION__.' &#8226 setting parameters';
				foreach($this->params as $ky => &$val) {
					//$dbh_stmt->bindValue($ky+1, $val);
					//pp('val - data_handler', $ky);
					//die;
					if (is_numeric($ky)) {
						$dbh_stmt->bindValue($ky+1, $val);
					} else {
						$dbh_stmt->bindParam($ky, $val);
					}
				}
			}
			
			/*if ($this->debug) {
				echo '<br>*** BEGIN dump for diagnostic purposes only ***<br>the error msg for this query is: '.$this->errMsg;
				echo '<br><br>***INFO***<br>DB: '.$this->dbn.'<br>QUERY: '.$this->qry.'<br>';
				echo '<br>debugDumpParams: <br> ';			
				$dbh_stmt->debugDumpParams();
				echo '<br>*** END diagnostic dump ***<br>';
			}*/
						
			//store result
			try {
			    //dbg('data_handler | '.__LINE__.' | dbh_stmt', $dbh_stmt);

				$dbh_stmt->execute();

			} catch (Throwable $e) {
				
				$this->errors[] = $this->handle_error_msg($dbh_stmt, $e);
				if ($this->debug) {
					echo("<br>err_msg: ".$this->err_msg."<br>");
					echo '<br>'.$e->getMessage();
                    $this->db = NULL;
                    die;
				}
				
			}
			
			$this->lastRowid = $this->db->lastInsertId();
			$this->columnCount = $dbh_stmt->columnCount();
			$this->col_meta = [];
			$i = 0;
			while($i <= $this->columnCount) {
				$this->col_meta[$i] = $dbh_stmt->getColumnMeta($i);
				$i++;
			}
				
			//echo '<br>'.__LINE__.' - '.strtoupper(__CLASS__).'::'.__FUNCTION__.' &#8226 lastRowid: '.$this->lastRowid;
							
			$dbh_stmt->setFetchMode(PDO::FETCH_ASSOC);
			
			$data = [];
			if ($groupData) {
				//echo '<br>'.__LINE__.' - '.strtoupper(__CLASS__).'::'.__FUNCTION__.' &#8226 so far so good.';
				
				$data = $dbh_stmt->fetchAll(PDO::FETCH_GROUP);

			} elseif (substr($this->qry,0,6) == 'SELECT') {
				//echo '<br>'.__LINE__.' - '.strtoupper(__CLASS__).'::'.__FUNCTION__.' &#8226 so far so good.';
				try {
					$data = $dbh_stmt->fetchAll();
					
				} catch (PDOException $e) {
					
					pp(__LINE__.' - '.strtoupper(__CLASS__).'::'.__FUNCTION__.' || $e ', $e);
                    $this->db = NULL;
                    die ('<br>DIE at line: '.__LINE__.' - '.strtoupper(__CLASS__));
					
				}
			}
			
			//echo '<br>'.__LINE__.' - '.strtoupper(__CLASS__).'::'.__FUNCTION__.' &#8226 so far so good.';
			//pp(__LINE__.' - '.strtoupper(__CLASS__).'::'.__FUNCTION__.' || $data ', $data);
			//die ('<br>DIE at line: '.__LINE__.' - '.strtoupper(__CLASS__));
			
			if (isset($dbh_stmt->errors)) {
				pp(__LINE__.' - '.strtoupper(__CLASS__).'::'.__FUNCTION__.' || errors ', $dbh_stmt->errors);
                $this->db = NULL;
                exit();
            }
				
			//check for errors
			if (empty($dbh_stmt->errors)) {
		
				//report existence of results only
				/*if ($returnData === FALSE) {
					if (count($data) == 0) {
						$data = false;
					} else {
						$data = true;
					}
				}*/
				
				$this->qry_result = $data;
				
				//$dbh_stmt = $this->db->prepare("SELECT ROW_COUNT()");
				//$dbh_stmt->execute();
				//$this->rowCount = $dbh_stmt->rowCount();
				
				if ($this->debug) {
					echo '<br>*** INFO *** QUERY COMPLETED. Diagnostic dump of this:';
					//pp('this', $this);
					echo '*** end of diagnostic dump ***<br>';
				}

                if (is_array($data)) {
                    return $data;
                } else {
                    return [];
                }
			
			}
			
			//close connection
			$this->db = NULL;
		}
		
		
		
		/* SET_MYSQL_CONNECTION */
		//db is db name only'
		function set_mysql_connection($host='', $db, $port=3306) {
			
			$this->db = NULL;
			if (empty($db)) {
				
				echo '<br>'.__LINE__.' - '.strtoupper(__CLASS__).'::'.__FUNCTION__.' &#8226 Database not specified.';
			}
		
			if (empty($host)) {
				$host = 'localhost';
			}

			$dsn = "mysql:host=".$host.";port=".$port.";dbname=".$db.";charset=utf8mb4";

			try {				
				$this->db = PDOPool::getPDOConnection($dsn);

			} catch (PDOException $e) {
				
				if (isset($this->db)) {
					$err_msg = $this->handle_error_msg($this->db, $e);
					if ($err_msg) {
						foreach($err_msg as $k=>$msg) {
							$this->errors[] = $msg;
						}
					}
					if ($this->debug) {
						echo '<br>this->db is set to: '.$this->db.'<br>';
						var_dump($this->errors);
					}
					die;
							
				} else {
					$this->errors = $this->handle_error_msg('', $e);
					$this->proceed = false;
					if ($this->debug) {
						echo '<br>this->db is not set: '.$this->db.'<br>input was: '.$db.'<br>';
						var_dump($this->errors);
					}
					die;		
				}
			}
					
		}
		
		
		/*** HANDLE_ERROR_MSG ***/
		function handle_error_msg ($db_obj, $err_obj) {
			
			$db_errs = [];
			$this->qry_result = [];
			
			if ($this->debug == TRUE) {
				pp('db_obj', $db_obj);
				pp('err_obj', $err_obj);
				die ('<br>DIE at line: '.__LINE__.' - '.strtoupper(__CLASS__));
			}
			
			if ($err_obj != NULL) {
				
				$msg = $err_obj->getMessage();
				$_SESSION['msgs'][] = $msg;
				
				//echo '<br>'.__LINE__.' - '.strtoupper(__CLASS__).'::'.__FUNCTION__.' &#8226 $msg: '.$msg;
				//pp(__LINE__.' - '.strtoupper(__CLASS__).'::'.__FUNCTION__.' || ', $msg);
				//die;
				//$this->debug = TRUE;
				
				/* if ($this->debug) {
					if (is_array($msg)) {
						//display cumulative messages if any
						display_messages($_SESSION['msgs'],false); //false = don't die
					} elseif (!empty($msg)) {
						echo '<br>data_handler error<br>'.$msg;
						//pp(__LINE__.' - '.strtoupper(__CLASS__).'::'.__FUNCTION__.' ||$_SESSION ', $_SESSION);
						die;
					} else {
						$_SESSION['msgs'] = '';
					}
				} */
				
				if (stripos($msg,'primary key must be unique') !== FALSE
					|| stripos($msg,'constraint failed') !== FALSE
				) {
					$db_err = '**INFO** No record was inserted because an item was not unique (and is required to be unique). Entry skipped.';
				
				} elseif (stripos($msg,'invalid data source name') !== false) {
					$db_err = 'Database does not exist!';
				
				} elseif (stripos($msg,'already exists') !== false) {
					$db_err = '**INFO** Table already exists. No need to create a new one.';
					
				} elseif (stripos($msg,'no such table') !== false) {
					$db_err = '**INFO** Table does not exist. Need to create a new one.';
					
				} elseif (stripos($msg, 'syntax error') !== false ) {
					//$trace = $err_obj->getTrace();
					//pp(__LINE__.' - '.strtoupper(__CLASS__).'::'.__FUNCTION__.' || $trace', $trace);
					//die ('<br>DIE at line: '.__LINE__.' - '.strtoupper(__CLASS__));
					
					$db_err = $msg;
					
				} elseif ( stripos($msg,'duplicate entry') ) {
					$db_err = 'Duplicate entry';

				} else {
					$this->proceed = false;
					//$db_errs[] = 'Database query did not succeed: "'.$msg.'". Trace follows: ';
					//pp(__LINE__.' - '.strtoupper(__CLASS__).'::'.__FUNCTION__.' || errorInfo', $err_obj->errorInfo);

					$db_err = 'Database query did not succeed.';
					if (!empty($err_obj->errorInfo)) {
						$db_err .= '  Error number [ '.$err_obj->errorInfo[0].' ]. ';
						foreach($err_obj->errorInfo as $i => $oneItem) {
							$db_err .= '<br>'.$i.': '.$err_obj->errorInfo[$i];
						}
						
					} elseif (!empty($msg)){
						$db_err .= 'Error message: '.$msg;
						
					} else {
						$db_err .= '  Error [ '.print_r($err_obj, TRUE).' ]. ';
					}

				}
			}
					
			//$trace = $err_obj->getTrace();
			/*foreach($trace as $step ) {
				$db_errs[] = 'zzzfile: '.$step['file'];
				$db_errs[] = 'zzzline: '.$step['line']; 
				$db_errs[] = 'zzzfunction: '.$step['function'];
				$db_errs[] = 'zzzclass: '.$step['class'];
				$db_errs[] = 'zzztype: '.$step['type'];
				foreach($step['args'] as $k => $arg) {
					$db_errs[] = 'zzz    arg: '.$k.' '.$arg;
				}
				$db_errs[] = 'zzz----------------------';
			}*/			
			if (is_object($db_obj)) {
				$db_err .= 'Query: '.$db_obj->queryString;
			}
			
			if (isset($db_err)) {
				trigger_error($db_err);
			}
						
		}

		
		//ARG_RESET
		function arg_reset() {
			$this->qry = '';
			$this->params = [];
			$this->err_msg = '';
		}
				

		//SEARCH_SUBARRAY
		// search subarray for specific key = value
		public function search_SubArray(Array $array, $key, $value) {   
			foreach ($array as $ky=>$subarray){  
				if (isset($subarray[$key]) && $subarray[$key] == $value)
				  return [$ky, $subarray];
			} 
		}
		

		/* === BUILD_FIELD_VALUE_LISTS === */
		//builds two lists (field names and values) from array, only adding values that 
		//  are not empty; only works with simple key=>value arrays; trims values;
		//  if value is not numeric, quotes are added to surround value
		// this function is configured for INSERTS (or INSERT or REPLACE)
		function build_field_value_lists ($arr, $record0 = FALSE) {
			
			$f_list = '';
			$f_ph_list = '';
			$param_arr = [];
			$v_list = '';

			foreach($arr as $ky=>$val) {

				//eliminate all '-','--','---', etc. as field values
				if (is_object($val)) {
					$result = validate_date($val);
					if ($result[0]) {
						$val = $result[1]->format('Y-m-d');
					} else {
						
						echo '<br>non recognizable object submitted as a string';
						die ('<br>DIE at line: '.__LINE__.' - '.strtoupper(__CLASS__));
					}
				}
				
				if ($record0 === FALSE) {
					$val = preg_replace('/^(-+)$/','', $val);
					$val = trim($val);
				}	
				
				if (!empty($val)
					|| ($record0 === TRUE && $val == 0)
				) {
					
					//make sure all times have hh:mm format
					if (stripos(strtolower($ky),'time') !== false) {
												
						//add leading 0 if missing to time
						if ((strlen(trim($val)) == 4 and preg_match('/^.*:.*$/', $val) == 1)) {
							$val = '0'.trim($val);
						}
						
						//delete seconds if present
						if (strlen(trim($val)) > 5 and substr($val,2,1) == ':') {
							$val = substr($val,0,5);
						}
						
					}

					//format all values (numeric vs. string)
					if (is_numeric($val)) {
						$nu_val = trim($val);
					} elseif (!is_array($val)) {
						$nu_val = rtrim($val,"'");
						$nu_val = ltrim($nu_val,"'");
					}
					
					$v_list .= trim($nu_val).",";

					$f_list .= trim($ky).',';
					
					$ky = rtrim($ky,"`");
					$ky = ltrim($ky,"`");
					
					$ky = str_replace(" ", "", $ky);
					
					$f_ph_list .= ':'.trim($ky).',';
					
					$param_arr[':'.$ky] = trim($nu_val);

				} elseif ($record0 === TRUE
					&& (empty($val) || is_null($val))
				) {
					
					$v_list .= NULL;
					$v_list .= ",";
					
					$f_list  .= trim($ky).',';
					
					$ky = rtrim($ky,"`");
					$ky = ltrim($ky,"`");
					$ky = str_replace(" ", "", $ky);
											
					$f_ph_list .= ':'.trim($ky).',';

					$param_arr[':'.$ky] = NULL;
					
				}
			}
			
			$f_list = rtrim($f_list,",");
			$f_ph_list = rtrim($f_ph_list,",");
			$v_list = rtrim($v_list,",");
			
			$result = [
				"fields" =>$f_list,
				"values" => $v_list,
				"params" => $param_arr,
				"placeholders" => $f_ph_list
			];

			//dbg(__CLASS__.' | '.__FUNCTION__.' | '.__LINE__.' | field value array', $result);

			return $result;
		}
		
		
		//BUILD_UPDATE_SET
		function build_update_set($upd_arr, $record0 = FALSE) {
			
			$params = [];
			
			$set = 'SET ';
			
			foreach($upd_arr as $ky=>$val) {
				
				$pholder = rtrim($ky,"`");
				$pholder = ltrim($pholder,"`");
				$pholder = str_replace(" ", "", $pholder);
										
				if (!empty($val)) {
					//echo '<br>'.$ky.' has a value';
					$set .= $ky.'=:'.$pholder.',';
					$params[':'.$pholder] = $val;
				} elseif ($record0 && $val == "0") {
					//echo '<br>'.$ky.' is zero and record0 is true';
					$set .= $ky.'=:'.$pholder.',';
					$params[':'.$pholder] = $val;
				} elseif ($record0 && empty($val) || is_null($val)) {
					//echo '<br>'.$ky.' is not zero and record0 is true';
					$set .= $ky.'=:'.$pholder.',';
					$params[':'.$pholder] = NULL;
					//echo '<br> * pholder is explicitly set to NULL';
				}
			}
			$set = rtrim($set,",");
            //dbg(__CLASS__.' | '.__FUNCTION__.' | '.__LINE__.' | update set', $set);

			return [$set, $params];
		}
		
		
		/*** GET_COL_NAMES ***/
		function get_col_names ($tbl) {
			
			$this->qry = "PRAGMA table_info(".$tbl.")";
			$this->params = [];
			$this->err_msg = "Error getting column names.";
			$data = $this->fetch_data_mysql();
						
			return array_reduce(
				$data,
  				function($rV, $cV) {
					$rV[]=$cV['name']; 
					return $rV; 
				},
				[]
			);
		}
	}
