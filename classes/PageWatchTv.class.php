<?php

    dbg('blank | '.__LINE__.' | *** TOP OF CLASS ***', '*');

	class PageWatchTv extends BasePage {
		
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
                <div class="row">
                    <div class="row" >
                        <div class="col p-0">
                            <img src="/_images/tv_remote_icons_750x200.png">
                        </div>
                    </div>
                
                    <div class="col-xs-12">
                        <div class="web-title">
                            <h2 style="margin: 10px 0; padding-top: 5px;">Watch live TV
                            </h2>
                        </div>
                        <div class="print-title" id="printTitle"></div>
                    </div>
                </div>

                <div class="row mt-3" >
                    <div class="col">
                        <h4 style="font-weight:bold;">Quick takes</h4>
                        <ul class="quick-takes">
                            <li>items</li>
                       </ul>
                    </div>
                </div>
                
                <hr style="color: #A69256; height: 2px;">
                
                <div class="row mt-3" >
                    <div class="col">
                        <h4 style="font-weight: bold; padding-top: 10px;">The full story</h4>
                        <h5>Overview</h5>
                        <p>preamble</p>
                        
                        <ul class="full-story">
                            <li>item</li>

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
