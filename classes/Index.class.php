<?php

    dbg('blank | '.__LINE__.' | *** TOP OF CLASS ***', '*');

	class Index extends BasePage {

		//__CONSTRUCT
		function __construct() {
            dbg(__CLASS__.' | '.__FUNCTION__.' | '.__LINE__.' | BasePage loaded', '*');

		    $inputs=[];
            $action = '';
            $pc = new PineIslandCrud();

			if (!empty($_POST['action'])) {

			    $inputs = sanitize_strings($_POST);
                $action = $inputs['action'];
                unset($inputs['action']);
            }

            switch ($action) {

                case 'login':
                    $result = self::startLogin($inputs);
                    echo $result;
                    break;

                case 'serve_page':
                    //for dev only
                    $_SESSION['loggedin'] = TRUE;
                    $_SESSION['uid'] = 99;

                    if (empty($_SESSION['loggedin'])) {
                        $result = self::startLogin($inputs);
                        echo $result;
                    }

                    //if(empty($inputs['pageName']))

                    echo '/'.$inputs['pageName'].'.php';
                    exit();

                default:
                    //$data = self::get_data($crud, $inputs);
                    $baseParts = parent::setBaseParts();
                    //$this->body_content = self::build_report();
                    $pageParts = self::setPageParts();
                    $this->page = parent::setPage($baseParts, $pageParts);
                    break;
            }
		}


		//GET_DATA
		static function get_data ($crud, $inputs): array
        {
            return $crud->getSomeData();
		}


        //SET_PAGE_PARTS
        private static function setPageParts(): array
        {
            return [
                //'sub_nav' => parent::set_subnav('foundation_program'),
                'page_body' => self::setBody(),
                'closing_js' => self::setClosingJs(),
            ];
        }


		//BUILD_REPORT
		static function setBody(): string
        {

			return '
            <div class="container text-center">
            
                <!-- START: for md to large displays -->
                <div class="row d-none d-md-block" >
                    <div class="col-12 px-0">
                        <h1 class="page-title">Pine Island 48 - "Oz"<div id="login" style="float: right; font-size: 30px;">Login</div>
                        </h1>
                    </div>
                    <div class="print-title" id="printTitle"></div>
                </div>
                <!-- END: med to large displays only -->
                
                <!-- START: for xs and sm displays only -->
                <div class="row d-md-none">
                    <div class="col-12 text-center">
                        <h1 class="page-title" style="text-align: center;">Pine 
                        Island 48 - "Oz"</h1> 
                    </div>
                </div>
                <div class="row d-md-none">
                    <div class="col pb-2 pe-0 ">
                        <div id="login" style="float: right; margin-top: 20px; font-size: 30px; padding: 0 7px 5px; margin-right: 6px; background-color: lightgray;">Login</div>
                    </div>
                </div>
                <!-- END: small displays only -->
                
                <div class="row row-eq-height">

                    <div class="tile col-sm-4 col-lg-3" data-name="whatsNew">
                        <div class="tileText">What\'s new?</div>
                    </div>
              
                    <div class="tile col-sm-4 col-lg-3 " data-name="internet"><div class="tileText">Wifi & Internet</div></div>                    
                    <div class="tile col-sm-4 col-lg-3 " data-name="watchTv"><div class="tileText">Watch<br>live TV</div></div>
              
                    <div class="tile col-sm-4 col-lg-3 " data-name="streaming"><div class="tileText">Stream music / video</div> </div>                   
                    <div class="tile col-sm-4 col-lg-3 " data-name="recycle"><div class="tileText">Recycling & trash</div></div>
              
                    <div class="tile col-sm-4 col-lg-3 " data-name="hvac"><div class="tileText">Cooling & heating</div></div>
                    <div class="tile col-sm-4 col-lg-3 " data-name="elevator"><div class="tileText">Elevator</div></div>
              
                    <div class="tile col-sm-4 col-lg-3 " data-name="pool"><div class="tileText">Spa & pool</div></div>

                    <div class="tile col-sm-4 col-lg-3 " data-name="hurricane"><div class="tileText">Hurricane evacuation</div></div>                    
                    <div class="tile col-sm-4 col-lg-3 " data-name="brindley"><div class="tileText">Brindley<br>Beach</div></div>
              
                    <div class="tile col-sm-4 col-lg-3 " data-name="kitchen"><div class="tileText">Kitchen</div></div>
                    <div class="tile col-sm-4 col-lg-3 " data-name="fixIt"><div class="tileText">Get it fixed</div></div>
              
                    <div class="tile col-sm-4 col-lg-3 " data-name="weather"><div class="tileText">Weather & ocean conditions</div></div>
                    <div class="tile col-sm-4 col-lg-3 " data-name="sportCourt"><div class="tileText">Sport / bocce court</div></div>
                    <div class="tile col-sm-4 col-lg-3 " data-name="history"><div class="tileText">OZ history</div></div>
                    <div class="tile col-sm-4 col-lg-3 " data-name="comment"><div class="tileText">Leave a comment</div></div>
                </div>
            </div>		         			
			';
		}

        private static function startLogin($inputs): string
        {
            //get guestLastName
            //get rentalStartDate
            //store both in Session for comments
            //return 'Sorry, you need to login first.';
            $loginForm = parent::setLoginForm();
            return parent::setModalHtml('loginForm', 'Login', $loginForm, 'modal-sm');
        }


        private static function setClosingJs(): string
        {
            return ' 
            <script src="/js/pi48.js"></script>           
            <script src="/js/Index.js"></script>  ';
        }

	}
