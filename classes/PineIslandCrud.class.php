<?php

	require_once $_SERVER['DOCUMENT_ROOT'].'/core/includes.php';
	
	class PineIslandCrud extends DataHandler {
		
		//__CONSTRUCT
		function __construct($dbn='', $debug=FALSE) {

            $host = $_SERVER['HTTP_HOST'];

			if (empty($dbn)) {
                $dbn = 'pineisla_main'.MYSQL_SUFFIX;
			}

			if ($debug == '') {
				$debug = FALSE;
			}

			parent::__construct($dbn, $debug);

			$this->errmsg_base = '***ERROR*** ';
			$this->params = [];
			//dbg(__CLASS__.' | '.__FUNCTION__.' | '.__LINE__.' | this->dbn',$this->dbn);

		}


		//EXECUTE
		function execute ($qry, $errmsg, $params): array
		{
			//$this->debug = true;

			$this->qry = $qry;
			$this->errMsg = $this->errmsg_base.$errmsg;
			$this->params = $params;

			$data = $this->fetch_data_mysql();

			if (empty($this->errors)) {
				return $this->qry_result;
			} else {
				display_messages($this->errors);
			}

			return [];
		}
		
		
		//==================//
		//===== CREATE =====//
		//==================//
		
		
		// ADD_PROJECT
		function add_project($fv_arr) {
			
			//set up 
			$errMsg = 'adding new project.';

			$lists = $this->build_field_value_lists($fv_arr);

			$params = $lists['params'];
			
			$qry = 'INSERT INTO projectdb ('.$lists['fields'].') VALUES ('.$lists['placeholders'].')';
			
			$this->execute($qry, $errMsg, $params);
			
			return $this->lastRowid;

		}
		
		
		//==================//
		//====== READ ======//
		//==================//

        //GET_USER_CRED
		function get_user_cred($uname) {
			$qry = "SELECT u_name FROM user_credentials WHERE u_name = :u_name LIMIT 1";
			$params = array(
				':u_name' => $uname
			);
			$errMsg = '***ERROR*** checking user_credentials.';
			
			$data = $this->execute($qry, $errMsg, $params);
			
			return $data;
		}
	
				
		// GET_PROJECTS
		function get_projects($scope='', $sortKey='', $sortOrder='', $date='', $searchStr='')
		{
            dbg(__CLASS__.' | '.__FUNCTION__.' | '.__LINE__.' | with scope, sortKey,
             sortOrder, date and searchStr', $scope.', '.$sortKey.', '.$sortOrder.', '.$date.', '.$searchStr);

            $w_clause = '';
            $params = [];

            //set scope (WHERE clause)
            if ($scope == 'Open') {
                $w_clause .= ' P.Status <> :Status AND';
                $params[':Status'] = "Completed";

            } elseif ($scope == 'Completed') {
                $w_clause .= ' P.Status = :Status AND';
                $params[':Status'] = "Completed";
            }

            if (!empty($searchStr)) {
                $w_clause .= ' (ProjTitle LIKE "%'.$searchStr.'%" OR PDescription LIKE "%'.$searchStr.'%")';
            }

            if (!empty($w_clause)) {
                $w_clause = ' WHERE '.trim($w_clause);
            }

            if (substr($w_clause,-4) == ' AND') {
                $w_clause = substr($w_clause, 0, (strlen($w_clause)-4));
            }

            //set ORDER clause (should already be a legitimate field name (except for 'followup'))
            $o_clause = ' ORDER BY';
            if(!empty($sortKey)) {
                $o_clause .= ' '.$sortKey;
            }

            if(!empty($sortOrder)) {
                $o_clause .= ' '.$sortOrder;
            }

            if($o_clause == ' ORDER BY') {
                $o_clause = '';
            }

            $qry = "SELECT P.*, d.Domain,
                           (Select ph.DocID from documents ph WHERE ph.projID = P.ProjID AND ph.DocOrder = 1) as topPhotoID,
                           (Select ph.DocTitle from documents ph WHERE ph.projID = P.ProjID AND ph.DocOrder = 1) as DocTitle,
                           (Select ph.DocDescrip from documents ph WHERE ph.projID = P.ProjID AND ph.DocOrder = 1) as DocDescrip,
                           (Select ph.DocFilename from documents ph WHERE ph.projID = P.ProjID AND ph.DocOrder = 1) as DocFilename
                    FROM projectdb P
                    JOIN domains d on d.DomainID = P.domainID"
                    .$w_clause
                    .$o_clause;

            $errMsg = "getting projects.";

			return $this->execute($qry, $errMsg, $params);
		}
        
        
        //GET_DOMAIN_PROJECTS
        function get_domain_projects($domainId)
        {
            $qry = 'SELECT ProjTitle, ProjID, Priority, Status, p.Domain, p.DomainID
            FROM projectdb p
            JOIN domains d on d.DomainID = p.DomainID
            WHERE p.DomainID = :DomainID
            ORDER BY ProjTitle';

            $params = [
                ':DomainID' => $domainId
            ];

            $errMsg = 'getting projects.';

            return $this->execute($qry, $errMsg, $params);
        }


		//GET INACTIVE PROJECTS
        function get_inactive_projects() {

		    $qry = "SELECT *
                FROM projectdb p
                WHERE p.ProjID NOT IN (Select DISTINCT ProjID FROM activitydb a WHERE ToDo = 1)
                    AND Status = 'Open'
                ORDER BY ProjTitle;";

		    $params = [];

		    $errMsg = "getting inactive projects";

		    return $this->execute($qry, $errMsg, $params);
        }


		/* GET_PROJECT_BY_ID */
		function get_project_by_id($proj_id) {

			$qry = 'SELECT p.*
                FROM projectdb p
                    LEFT JOIN documents ph on ph.projID = p.ProjID
                WHERE p.ProjID = :projID';

			$errMsg = "getting specific project.";
			$params = array(':projID'=>$proj_id);
			
			$result = $this->execute($qry, $errMsg, $params);
			return $result;

		}
		

		/* GET_ACTIVITIES */
		function get_activities($proj_id, $onlyTodo=FALSE, $startDate='', $endDate='')
        {
            $params = [
                ':ProjID' => $proj_id
            ];

            $addlWhere = '';
		    if ($onlyTodo === TRUE) {
		        $addlWhere .= " AND ToDo = 1";
            }

            if (!empty($startDate)) {
                $addlWhere .= " AND ActivityDate >= :startDate";
                $params[':startDate'] = $startDate;
            }

            if (!empty($endDate)) {
                $addlWhere .= ' AND ActivityDate <= :endDate';
                $params[':endDate'] = $endDate;
            }

            $qry = 'SELECT * FROM activitydb
				WHERE ProjID = :ProjID'.$addlWhere.'
			 	Order by ActivityDate desc';

			$errMsg = "getting activities";

			return $this->execute($qry, $errMsg, $params);
		}


		//GET_SUMMARY_PROJ_INFO
		//for Summary Project & Activity Report
		function get_summary_proj_info($beginDate, $endDate) {
		
			$bDt = new DateTime('1900-01-01');
			if (!empty($beginDate)) {
				
				$bDt = new DateTime($beginDate);
			}
			
			$eDt = new DateTime();
			if (!empty($endDate)) {
				
				$eDt = new DateTime($endDate);
			} 
			
			$params = array(
				':bDate' => $bDt->format('Y-m-d'),
				':eDate' => $eDt->format('Y-m-d')
			);
					
			$qry = 'SELECT p.ProjID,
				p.ProjTitle,
				p.DateStarted,
				p.Status,
       			count(a.updateNo) AS ct_activities,
		       	(SELECT ActivityDate FROM activitydb ac 
		       		WHERE ac.ProjID = p.projID
		       		ORDER BY ActivityDate limit 1 ) as first_date,
		       	(SELECT ActivityDate FROM activitydb ac
		       		WHERE ac.ProjID = p.projID
		       		ORDER BY ActivityDate desc limit 1) as last_date
				FROM activitydb a
			       JOIN projectdb p ON p.ProjID = a.ProjID
			 	WHERE ActivityDate >= :bDate AND ActivityDate <= :eDate
			 		
			 	GROUP BY p.ProjTitle;';

			$errMsg = 'getting summary report data';
						 	
			return $this->execute($qry, $errMsg, $params);
			 	
		}
		
		
		//GET DAILY ACTIVITIES
		//for Daily Activities Report	
		function get_daily_activities($beginDate, $endDate,  $todo="", $domain=""): array
        {

			$bDt = new DateTime('1900-01-01');
			if (!empty($beginDate)) {
				$bDt = new DateTime($beginDate);
			}
			
			$eDt = new DateTime();
			if (!empty($endDate)) {
				$eDt = new DateTime($endDate);
			}
			
			$params = array(
				':bDate' => $bDt->format('Y-m-d 00:00:00'),
				':eDate' => $eDt->format('Y-m-d 23:59:59')
			);

			$addWhere = '';
			if (!empty($domain)) {
			    $addWhere = ' AND Domain = :Domain ';
			    $params[':Domain'] = $domain;
            }

			$params[':ToDo'] = 0;
			if (!empty($todo)) {
			    $params[':ToDo'] = 1;
            }
					
			$qry = 'SELECT p.ProjID,
		        a.ActivityDate,
		        p.ProjTitle,
		        a.ADescription,
                d.Domain,
                p.Priority,
                a.UpdateNo
		        FROM activitydb a
		        JOIN projectdb p ON p.ProjID = a.ProjID
                JOIN domains d on p.DomainID = d.DomainID
			 	WHERE ActivityDate >= :bDate AND ActivityDate <= :eDate
			 	    AND ToDo = :ToDo
                    '.$addWhere.'
			 	ORDER BY a.ActivityDate asc, FIELD(Priority, 
		         "High",
		         "Med",
		         "Low"), ProjTitle';

			$errMsg = 'getting daily activities report data';
								
			return $this->execute($qry, $errMsg, $params);
		}


		//GET_ACTIVITY_TODOS
        function get_activity_todos($domain='', $priority='', $searchStr='', $beforeDate='', $afterDate='')
        {
            $searchStr = strtolower($searchStr);

            $where = 'WHERE a.ToDo = 1 ';
            $params = [];
            
            if(!empty($domain)
                && $domain != "All") {
                $where .=  "AND Domain = :Domain ";
                $params[':Domain'] = $domain;
            }

            if (!empty($priority)
                && $priority != "All") {

                if ($priority != "HighMed") {
                    $where .= 'AND Priority = :Priority ';
                    $params[':Priority'] = $priority;
                } else {
                    $where .= "AND Priority IN ('High', 'Med') ";
                }
            }

            if (!empty($searchStr)) {
                $where .= ' AND (LOWER(ADescription) LIKE "%'.$searchStr.'%" 
                    OR LOWER(ProjTitle) LIKE "%'.$searchStr.'%"
                    OR LOWER(PDescription) LIKE "%'.$searchStr.'%") ';
            }

            if (!empty($beforeDate) && !empty($afterDate)) {
                $where .= ' AND (a.ActivityDate < :beforeDate AND a.ActivityDate > :afterDate';

            } elseif (!empty($beforeDate)) {
                $where .= ' AND a.ActivityDate < :beforeDate';
                $params[':beforeDate'] = $beforeDate;

            } elseif (!empty($afterDate)) {
                $where .= ' AND a.ActivityDate > :afterDate';
                $params[':afterDate'] = $afterDate;
            }

            $qry = "SELECT a.ActivityDate, a.ProjID as ItemID, a.ADescription, a.ToDo, p.Priority, p.ProjTitle, p.Domain 
		        FROM activitydb a
                    JOIN projectdb p on a.ProjID = p.ProjID
		        ".$where."
		        ORDER BY ActivityDate, FIELD(Priority, 
		         'High',
		         'Med',
		         'Low'), ProjTitle";

		    $errMsg = "getting project to dos.";

		    return $this->execute($qry, $errMsg, $params);

        }


        //GET_PROJECT_TIME
        function get_project_time($projId)
        {
            $qry = 'SELECT SUM(ADuration) as ProjectTime
            FROM activitydb
            WHERE ProjID = :ProjID';

            $params = [
                ':ProjID' => $projId
            ];

            $errMsg = 'getting project time data';

            $result = $this->execute($qry, $errMsg, $params);

            if (!empty($result)) {
                return $result[0]['ProjectTime'];
            }

            return 0;
        }


        //GET_PROJECT_COST
        function get_project_cost($projId)
        {
            $qry = 'SELECT SUM(ACost) as ProjectCost
            FROM activitydb
            WHERE ProjID = :ProjID';

            $params = [
                ':ProjID' => $projId
            ];

            $errMsg = 'getting project cost data';

            $result = $this->execute($qry, $errMsg, $params);

            if (!empty($result)) {
                return $result[0]['ProjectCost'];
            }

            return 0;
        }


        //GET LIST ITEMS

        /**
         * @param $date object if NULL, get all dates
         * @param $status string any of 'open', 'done', 'all'
         *
         * @return array|string|void
         */
        public function getListItems(object $date=NULL, string $status='open')
        {
            $where = '';
            $params = [];
            if(!empty($date)) {
                $where .= 'ItemDate = :date AND ';
                $params[':date'] = $date->format('Y-m-d');
            }

            if($status != 'all') {
                $where .= 'ItemStatus = :status';
                $params[':status'] = $status;
            }

            if (substr($where,-4) == 'AND ') {
                $where = substr($where, 0, (strlen($where)-5));
            }
            $where = 'WHERE '.$where;

            $qry = 'SELECT * FROM quick_list '
                .$where.'
                ORDER BY ListOrder';

            $errMsg = 'getting items';

            return $this->execute($qry, $errMsg, $params);

        }


        //GET LIST ITEM BY ID
        public function getListItemById($QuickId)
        {
            $params = [
                ':QuickID' => $QuickId
            ];

            $qry = 'SELECT * FROM quick_list
                WHERE QuickID = :QuickID';

            $errMsg = 'getting a quick list item';

            return $this->execute($qry, $errMsg, $params);
        }


        //GET PRIOR LIST ITEMS
        /**
         * @param string $before format YY-mm-dd
         * @param string $status 'open' or 'done'
         *
         * @return array|string|void
         */
        public function getPriorItems(string $before, string $status='open'): array
        {
            $params = [
                ':ItemDate' => $before,
                ':ItemStatus' => $status
            ];

            $qry = 'SELECT QuickID
                FROM quick_list
                WHERE ItemDate < :ItemDate
                   AND ItemStatus = :ItemStatus';

            $errMsg = 'getting prior open items';

            return $this->execute($qry, $errMsg, $params);
        }


        public function getUnbilledActivities(): array
        {
            $qry = 'SELECT p.ProjTitle, p.Domain, p.DomainID, a.*, round(ARate * ADuration / 60, 2) as AFee
                FROM activitydb a
                JOIN projectdb p ON p.ProjID = a.ProjID
                WHERE (Billed = 0 OR Billed IS NULL)
                   AND (ARate IS NOT NULL AND ARate > 0)
                   AND ADuration > 0
                ORDER BY ProjID, ActivityDate';

            $params = [];

            return $this->execute($qry, 'getting billable activities', $params);
        }
		
		/* ============================== */
		/* ========== UPDATING ========== */
		/* ============================== */
					
		
		//UPDATE_STATUS 
		/*function update_status($projID, $status) {
			
			$dt = new DateTime();
			$dt_val = $dt->format('Y-m-d');
			
			//handle "completed" condition
			if ($status != "Completed") {
				$dt_val = '';
			}
			
			$dh = new data_handler($this->dbn, $this->debug);
			$qry = 'UPDATE projectdb
				SET Status = :Status, DateCompleted = :DateCompleted
				WHERE ProjID = :ProjID';
			
			$errMsg = 'updating project status.';
			$params = array(
				":Status" => $status,
				":DateCompleted" => $dt_val,
				":ProjID" => $projID
			);
			
			$dh->fetch_data();
			
			$this->handle_result($qry_result, $dh->errors, $errMsg);
						
		}*/

		
		//UPDATE_PROJECT 
		function update_project($projID, $proj_chgs, $recordempty=FALSE) {
			
			//process proj_chgs array
			$fv_arr = [];

			$set_params = $this->build_update_set($proj_chgs, $recordempty);
			
			$qry = 'UPDATE projectdb '.
				$set_params[0].' WHERE ProjID = :ProjID';
			
			$errMsg = 'updating project.';
			$params = $set_params[1];
			$params[':ProjID']= $projID;
			
			$this->execute($qry, $errMsg, $params);
			//dies if errors, so continue as there are no errors
		}

		
		// REACTIVATE_PROJECT
		function reactivate_project($projID) {

			$qry = "UPDATE projectdb SET Status = :status,DateCompleted = '' WHERE ProjID = :projID";
			$params = array(
				':status'=>"Vendor in process",
				':projID'=>$projID
			);
			$errMsg =  "reactivating project failed.";
			
			$result = $this->execute($qry, $errMsg, $params);
			return $result;
		}

		
		/*** UPDATE_FLOOR ***/
		function update_floor ($flr_chgs) {
			
			$flr_name = trim($flr_chgs[0]['cur']);
			
			$found = $this->floor_check($flr_name,'name');
			
			//if not empty, something found
			if (!empty($found)) {			
				$nu_flrID = $found;
			
			//nothing found - create new	
			} else {
				//add floor; get id of new floor
				$nu_flrID = $this->add_floor($flr_name); 					
			}
			
			return $nu_flrID;

		}


		// UPDATE_TOP_PIC
		/*function update_top_pic($projID, $imgID) {
			
			$dh = new data_handler($this->dbn);
			
			$errMsg = 'updating top pic.';
			
			//test for any project-related img is marked as top pic (position 1)
			$img_data = $pc->get_images($projID,1);
			
			//update found img to blank
			if (!empty($img_data)) {
				foreach($img_data as $i_row) {
					$qry = 'UPDATE documents SET DocOrder = "" WHERE DocID = :DocID';
					
					$params = array(":DocID" => $i_row['DocID']);
					
					$dh->fetch_data();
					
					if (!empty($dh->errors)) {
						echo '<br>'.$errMsg;
						display_messages($dh->errors);
					}
				}
			}
			
			//set selected image to position
			$errMsg = 'setting new top pic.';
			
			$qry = 'UPDATE documents SET DocOrder = 1 WHERE DocID = :DocID';
			
			$params = array(":DocID" => $imgID);
			
			$dh->fetch_data();

			$result =$this->handle_result($qry_result, $dh->errors, $errMsg);
		
		}*/


        //UPDATE LIST ITEM
        public function updateListItem($id, $updArr, $recordempty=FALSE)
        {
            $set_params = $this->build_update_set($updArr, $recordempty);

            $params = $set_params[1];
            $params[':QuickID']= $id;

            $qry = 'UPDATE quick_list '.
                $set_params[0].' WHERE QuickID = :QuickID';

            $errMsg = 'updating item.';

            $this->execute($qry, $errMsg, $params);
            //dies if errors, so continue as there are no errors
        }

		
		/* ============================== */
		/* ========== DELETING ========== */
		/* ============================== */
		
		//DELETE_ACTIVITY
		function delete_activity($actID)
        {
			$errMsg = 'deleting an activity.';
			
			$qry = 'DELETE FROM activitydb WHERE UpdateNo = :updateNo';
			
			$params = array (
				':updateNo' => $actID
			);
			
			$this->execute($qry, $errMsg, $params);
		}


		//DELETE RECURRING
        function delete_recurring($recurGroup)
        {
            $errMsg = 'deleting a recurrence.';

            $qry = 'DELETE FROM recurring WHERE RecurGroup = :RecurGroup';

            $params = array (
                ':RecurGroup' => $recurGroup
            );

            $this->execute($qry, $errMsg, $params);
        }
	
		
		// DELETE_PHOTOS
		/*function delete_photos ($data) {
			
			$dh = new data_handler($this->dbn);
			
			$errMsg = 'deleting a photo.';
			
			$qry = 'DELETE FROM documents WHERE DocID = :fuID';
			
			if (!empty($data['del_img_IDs'])) {
				
				foreach($photoIDs as $ky=>$vl) {
					$params = array (':DocID' => $vl);
					$dh->fetch_data();
					$result =$this->handle_result($qry_result, $dh->errors, $errMsg);
					$pathToFile = SERVER_ROOT.'uploads/'.$data['del_img_files'][$ky];
					unlink($pathToFile);
				}
			} 
		}*/


		//DELETE_ITEM
		function delete_item($projID) {

			//set up db 
			$params = [
				':projID' => $projID
			];
			
			//delete documents
			$errMsg = 'deleting project documents.';
			$qry = 'DELETE FROM documents WHERE ProjID = :projID';
			$this->execute($qry, $errMsg, $params);

			//delete activities
			$errMsg = 'deleting project activities.';
			$qry = 'DELETE FROM activitydb WHERE ProjID = :projID';
			$this->execute($qry, $errMsg, $params);
			
			//delete project
			$errMsg = 'deleting project data.';
			$qry = 'DELETE FROM projectdb WHERE ProjID = :projID';
			 $this->execute($qry, $errMsg, $params);
		}


        //DELETE QUICK ITEM
        public function deleteQuickItem($id) {
            $params = [
                ':QuickID' => $id
            ];

            $qry = 'DELETE FROM quick_list
                WHERE QuickID = :QuickID';

            $errMsg = 'deleting quick list item.';
            $this->execute($qry, $errMsg, $params);
        }
		
		/* ============================== */
		/* ==== SUPPORTING FUNCTIONS ==== */
		/* ============================== */

		//HANDLE_RESULT
		function handle_result($result, $errors, $errMsg) {
			if (empty($errors)) {
				return $result;
			} else {
				echo '<br>'.$errMsg;
				display_messages($errors);
			}
            return [];
		}
		
		
		// CHECK_FILE_SPECS
		function check_file_specs($f_name, $f_type, $f_size) {

			$allowedExts = array("gif","jpeg","jpg","png","pdf","doc","docx","xls","xlsx","ppt","pptx","bmp","txt");
			
			//check for valid extension
			$temp = explode(".", $f_name);
			$extension = strtolower(end($temp));
			
			if ((($f_type == "image/gif")
				|| ($f_type == "image/jpeg")
				|| ($f_type == "image/jpg")
				|| ($f_type == "image/pjpeg")
				|| ($f_type == "image/x-png")
				|| ($f_type == "image/png")
				|| ($f_type == "image/bmp")
				|| ($f_type == "text/plain")
				|| ($f_type == "binary/octet-stream")
				|| ($f_type == "application/pdf")
				|| ($f_type == "application/vnd.ms-powerpoint")
				|| ($f_type == "application/msword")
				|| ($f_type == "application/vnd.openxmlformats-officedocument.wordprocessingml.document")
				)
				&& ($f_size/1024 < 5000)
				&& in_array($extension, $allowedExts)
			) {
				return true;
			} else {
				return false;
			}
		}


		//CHECK_CREDENTIALS
		function check_credentials($uname, $pw) {
			// See if the username and password are valid.
			$qry = "SELECT u_name, UID FROM user_credentials 
			WHERE u_name = :u_name AND u_pw = :u_pw LIMIT 1";
			//$query = mysql_query($sql) or trigger_error("Query Failed: " . mysql_error());
			$params = [
				':u_name' => filter_var($uname,FILTER_SANITIZE_STRING),
				':u_pw' => hashPassword($pw, SALT1, SALT2)
			];
			$errMsg = "***ERROR*** checking username and password";
			return $this->execute($qry, $errMsg, $params);
		}
    }
