<?php

    dbg('blank | '.__LINE__.' | *** TOP OF CLASS ***', '*');

	class BLANK extends BasePage {
		
		//__CONSTRUCT
		function __construct() {

		    $inputs=[];
            $action = '';
            $crud = new crud();
			parent::__construct();

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
		static function get_data ($crud, $inputs): array
        {
            return $crud->getSomeData();
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
		static function setBody($data): string
        {
            $styles = '
            <style>
            </style>
            ';

			return $styles.'
            <div id="modal_div"></div>
            <div class="container" 
                style="font-size: 12px; margin-top: 50px;">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="web-title">
                            <h2 style="margin: 10px 0; border-top: thin solid silver; padding-top: 5px;">Page Title
                            </h2>
                        </div>
                        <div class="print-title" id="printTitle"></div>
                    </div>
                </div>

            </div>		
            
            <script src="/js/blank.js"></script>            			
			';
		}
	}
