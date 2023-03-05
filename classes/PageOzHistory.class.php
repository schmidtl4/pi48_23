<?php

    dbg('blank | '.__LINE__.' | *** TOP OF CLASS ***', '*');

	class PageOzHistory extends BasePage {
		
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
                case 'do something':

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
                        <h1 class="page-title">Pine Island 48 - "Oz"</h1>
                    </div>
                    <div class="print-title" id="printTitle"></div>
                </div>
            </div>
            <div class="container page-container">
            
                <div class="row" >
                    <div class="col p-0">
                        <img src="/_images/acropolis_750x200.jpg" class="img-fluid">
                    </div>
                </div>
                            
                <div class="row">
                    <div class="col-xs-12">
                        <div class="web-title">
                            <h2 style="margin: 10px 0; padding-top: 5px;">Oz history
                            </h2>
                        </div>
                        <div class="print-title" id="printTitle"></div>
                    </div>
                </div>
                
                <div class="row mt-3" >
                    <div class="col">
                        <h4 style="font-weight:bold;">Quick takes</h4>
                        <ul class="quick-takes">
                            <li>The folks that designed and built Oz, still own it today</li>
                            <li>First guests welcomed 1994</li>
                            <li>Pool, spa, sport court added 2016</li>
                       </ul>
                    </div>
                </div>
                
                <hr style="color: #A69256; height: 2px;">
                
                <div class="row mt-3" >
                    <div class="col">
                        <h4 style="font-weight: bold; padding-top: 10px;">The full story</h4>
                        <h5>Overview</h5>
                        <p>OK...you\'re right, the history of Oz doesn\'t start in ancient Greek times.  But you\'ll perhaps forgive the similarity?  Hey we have columns too! So now to the real story of Oz...</p>
                        
                        <p>In 1993, the Outer Banks was still a cottage community.  Literally. If you wanted to rent a spot on the Beach, you likely had to bring everything - sheets, towels, pillows, toilet paper, food.  A few pots and pans, and common kitchen items.  That was great if you lived nearby and could load up the family station wagon with everything you needed.</p>
                        
                        <p>But even then, more people were starting to travel farther and expect more.  A rustic cottage wasn\'t exactly what most folks had in mind for a destination. What was the market asking for? The desired destination was one that could accommodate multiple families and be as "all inclusive" as possible. So that\'s what we built!  We wanted Oz to accommodate friend-families and multi-generational gatherings. When you arrived, we wanted the beds made and clean towels for all.  And we wanted the beach chairs ready for a trip to the beach the moment you arrived! </p>
                        
                        <p>Over the years, we\'ve tried to keep up with the expectations of folks renting the house as well as just keep up with the torturous conditions of salt air and high winds. Every year there have been some changes to Oz and here\'s the history!</p>
                        
                        <p class="history-lead">2022</p>
                        <ul class="history">
                            <li>item 1</li>
                            <li>item 1</li>
                        </ul>
                        
                        <p class="history-lead">2022</p>
                        <ul class="history">
                            <li>item 1</li>
                            <li>item 1</li>
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
