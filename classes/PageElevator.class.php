<?php

    dbg('blank | '.__LINE__.' | *** TOP OF CLASS ***', '*');

	class PageElevator extends BasePage {
		
		//__CONSTRUCT
		function __construct() {

		    $inputs=[];
            $action = '';
            $pc = new PineIslandCrud();

			if (!empty($_POST['action'])) {

			    $inputs = core_functions::sanitize_strings($_POST);
                $action = $inputs['action'];
                unset($inputs['action']);
            }

            switch ($action) {
                // do something when page is posted (via ajax)
                case 'record_comment':
                    echo Comments::recordComment($pc, $inputs);
                    break;

                default:

                    $baseParts = parent::setBaseParts();
                    $pageParts = self::setPageParts();
                    $this->page = parent::setPage($baseParts, $pageParts);
                    break;
            }
		}


		//GET_DATA
		static function get_data ($pc, $inputs): array
        {
            return $pc->getSomeData();
		}


        //SET_PAGE_PARTS
        private static function setPageParts(): array
        {
            return [
                'page_body' => self::setBody(),
                'closing_js' => self::setClosingJs(),
            ];
        }


		//SET BODY
		static function setBody(): string
        {
            $commentForm = Comments::setCommentForm();

			return '
            <div id="modal_div"></div>
            <div class="container site-title">
            
                <div class="row" >
                    <div class="col-12 px-0 text-left">
                        <h1 class="page-title" >Pine Island 48 - "Oz"</h1>
                    </div>
                    <div class="print-title" id="printTitle"></div>
                </div>
            </div>
                              
            <div class="container page-container">
                
                <div class="row" >
                    <div class="col p-0">
                        <img src="/_images/elevator_close_2_750x200.jpg" class="img-fluid">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <div class="web-title">
                            <h2 style="margin: 10px 0; padding-top: 5px;">Elevator
                            </h2>
                        </div>
                        <div class="print-title" id="printTitle"></div>
                    </div>
                </div>
                                
                <div class="row mt-3" >
                    <div class="col">
                        <h4 style="font-weight:bold;">Quick takes</h4>
                        <ul class="quick-takes">
                            <li>fully compliant with North Carolina\'s Weston\'s Law</li>
                            <li>the gate is MANUAL</li>
                            <li>elevator can be called to any floor, but...</li>
                       </ul>
                    </div>
                </div>
                
                <hr style="color: #A69256; height: 2px;">
                
                <div class="row mt-3" >
                    <div class="col">
                        <h4 style="font-weight: bold; padding-top: 10px;">The full story</h4>
                        <h5>Overview</h5>
                        <p>The elevator has dutifully transported luggage, groceries, people and more for decades. In the last two years, residential elevators gained signficant attention because a young child got trapped in the space between the gate and the external door.  To enhance the safety of all elevators, North Carolina mandated (and we supported) adding baffles between the door and the gate and upgrading the gate so that the gate did not allow horizontal flexing.</p>
                        
                        <p>Here are the things you need to know to operate the elevator efficiently:</p>
                        
                        <ul class="full-story">
                            <li>Keep the internal gate and all external doors closed</li>
                            <p>If you pull the gate all the way to the left side of the elevator (as you face it from outside the elevator) and the external doors closed at all times, the elevator can be easily called to any floor by pressing the "Call" button.  If the gate is not closed or an external door is open, the elevator will not move when called.</p>

                        </ul>
                    </div>
                </div>
                
                <hr style="color: #A69256; height: 2px;">
                
                '.$commentForm.'             
                
            </div>		      			
			';
		}


        //SET CLOSING JS
        private static function setClosingJs(): string
        {
            return '
            <script src="/js/page.js"></script>
            <script src="/js/comments.js"></script>';
        }
	}
