<?php
    dbg('base_page_pi48.class.php |  | '.__LINE__.' | *** CLASS LOAD ***','TOP');

	class BasePage
    {
        public $page;

        //SET_PAGE
        public static function setPage($baseParts, $pageParts): string
        {
            return
                $baseParts['top'].
                $baseParts['head'].
                //$pageParts['sub_nav'].
                $pageParts['page_body'].
                $baseParts['modalDiv'].
                $baseParts['footer'].
                $baseParts['bottom_js'].
                $pageParts['closing_js'].
                //$pageParts['smithBot'].
                $baseParts['closing_tags'];
        }

        //SANITIZE POST
        public static function sanitizePost()
        {
            $action = '';
            $inputs = [];

            if (!empty($_POST['action'])) {

                $inputs = admin_functions::sanitize_form_input($_POST);

                if (isset($inputs['form_key'])) {
                    $fk = new form_key();
                    $inputs = $fk->validate_form_key($inputs);
                }

                if (isset($inputs['action'])) {
                    $action = $inputs['action'];
                    unset($inputs['action']);
                }
            }

            return [
                'action' => $action,
                'inputs' => $inputs
            ];
        }


        //SET BASE PARTS
        public static function setBaseParts(string $top_css_js = '', string $subnav = '', array $js_css = null)
        {
            //$bc = new briia_crud ('briiai5_scorecard');

            $js_css = self::setJsCss($js_css);

            $js_css = self::setBootstrap ($js_css);

            $js_css = self::setJQuery ($js_css);

            $js_css = self::setJqueryUi($js_css);

            $js_css = self::setPopper ($js_css);

            $js_css = self::setChartJs ($js_css);

            $js_css = self::setMaterialIcons ($js_css);

            $js_css = self::setFontawesomeFont ($js_css);

            $js_css = self::setStripe ($js_css);

            $navLocation = '';
            if ($js_css['navLocation'] == 'SIDE') {
                $navLocation = self::set_side_nav();
            }

            //$pageInfo = self::set_page_info($bc);
            $pageInfo = '';

            $includeGA = '';
            if (isset($js_css['google_analytics'])
                && ($js_css['google_analytics'] == 'NO'
                    || empty($js_css['google_analytics']))) {
                $includeGA = 'NO';
            }

            $gaArr = self::set_google_analytics($includeGA);

            $top = self::set_top($gaArr, $includeGA, $pageInfo);

            $head = self::set_head($top_css_js, $js_css, $gaArr['gtm_btm']);

            $modalDiv = '<div id="modalDiv"></div>';
            $footer = '';
            if (!empty($js_css['footer']) && $js_css['footer'] == 'YES') {
                $footer = self::set_footer();
            }

            $bottom_js = self::set_bottom_js($js_css);

            $closing_tags = self::set_closing_tags();

            return [
                'top' => $top,
                'head' => $head,
                'navLocation' => $navLocation,
                'modalDiv' => $modalDiv,
                'footer' => $footer,
                'bottom_js' => $bottom_js,
                'closing_tags' => $closing_tags
            ];
        }


        //SET JS CSS
        private static function setJsCss ($js_css): array
        {
            $defaultJsCss = [
                'bs' => '5.0.2',
                'chartjs' => '',
                'fontawesome' => '',
                'footer' => '',
                'google_analytics' => 'NO',
                'jQuery' => '3.6.3',
                'jq_ui' => '',
                'material_icons' => 'YES',
                'modal' => 'NO',
                'navLocation' => 'SIDE', // TOP, SIDE, NONE
                'page_type' => '',
                'popper' => '1.14.7',
                'responsive-tabs' => 'NO',
                'stripe' => 'NO',
                'sub_nav' => ''
            ];

            if(empty($js_css)) {
                $js_css = $defaultJsCss;

            } else {
                //add any node not supplied, default will be used if not supplied
                foreach ($defaultJsCss as $item => $includeInfo) {
                    if (!array_key_exists($item, $js_css)) {
                        $js_css[$item] = $includeInfo;
                    }
                }
            }

            return $js_css;
        }


        //SET BOOTSTRAP
        private static function setBootstrap($js_css): array
        {
            $js_css['bsCss'] = '';
            $js_css['bsJs'] = '';
            switch ($js_css['bs']) {
                /*case '3.3.7':
                    $js_css['bsCss'] = '<link href="/vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">';
                    $js_css['bsJs'] = "<script src='/vendor/twbs/bootstrap/dist/js/bootstrap.min.js'></script>";
                    break;

                case '4.0.0';
                    $js_css['bsCss'] = '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">';

                    $js_css['bsJs'] = '<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>';
                    break;

                case '4.3.1':
                    $js_css['bsCss'] = '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">';

                    $js_css['bsJs'] = '<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>';

                    break;*/

                case '5.0.2':
                    $js_css['bsCss'] = '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">';
                    $js_css['bsJs'] = '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>';
                    break;

                default:
                    break;
            }

            return $js_css;
        }


        //SET JQUERY
        private static function setJQuery($js_css): array
        {
            switch ($js_css['jQuery']) {
                /*case '3.3.1':
                case '3.5.1':
                    $js_css['jQueryJs'] = '<script src="/vendor/components/jquery/jquery.min.js"></script>';
                    $js_css['jQueryCss'] = '';
                    break;*/

                case '3.6.3':
                    //$js_css['jQueryJs'] = '<script
                    //      src="https://code.jquery.com/jquery-3.6.3.min.js"
                    //      integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU="
                    //      crossorigin="anonymous"></script>';
                    //$js_css['jQueryJs'] = '<script src="/js/jquery_3.6.3.js"></script>';
                    $js_css['jQueryJs'] = '<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.3/dist/jquery.min.js"></script>';
                    $js_css['jQueryCss'] = '';
                    break;

                default:
                    $js_css['jQueryJs'] = '';
                    $js_css['jQueryCss'] = '';
                    break;
            }

            return $js_css;
        }


        //SET JQUERY UI
        private static function setJqueryUi($js_css): array
        {

            switch ($js_css['jq_ui']) {
                case '1.12.1':
                    $js_css['jq_uiJs'] = "<script src='https://code.jquery.com/ui/1.12.1/jquery-ui.js'></script>";
                    $js_css['jq_uiCss'] = '<link rel="stylesheet" href="/js/jquery-ui/jquery-ui.min.css">';
                    //$js_css['jq_uiCss']='';

                    break;

                default:
                    $js_css['jq_uiJs'] = '';
                    $js_css['jq_uiCss'] = '';
                    break;
            }

            return $js_css;
        }


        //SET POPPER
        private static function setPopper ($js_css): array
        {
            switch ($js_css['popper']) {
                case '1.12.9':
                    $js_css['popper'] = '<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>';
                    break;

                case '1.14.7':
                    $js_css['popper'] = '<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>';
                    break;

                case '1.16.1':
                    $js_css['popper'] = '<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/popper.min.js"></script>';
                    break;

                default:
                    $js_css['popper'] = '';
                    break;
            }

            return $js_css;
        }


        //SET CHARTJS
        private static function setChartJs ($js_css): array
        {
            switch ($js_css['chartjs']) {
                case '2.9.3':
                    $js_css['chartCss'] = '
						<script src="/js/chartjs/Chart.min.js"></script>
						<script src="/js/chartjs/chartjs-plugin-datalabels.min.js"></script>';
                    $js_css['chartjsScript'] = '
						<script src="/js/horizontalBarPlugin.js"></script>
						<script src="/js/chartjs-plugin-annotation.min.js"></script>';
                    break;

                case '3.6.2':

                    $js_css['chartCss'] = '';
                    $js_css['chartjsScript'] = '<script src="/js/chartjs_362/chart.min.js"></script>
                        <script src="/js/chartjs-plugin-annotation/dist/chartjs-plugin-annotation.min.js"></script>
                        <script src="/js/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.min.js"></script>';
                    break;

                default:
                    $js_css['chartCss'] = '';
                    $js_css['chartjsScript'] = '';
                    break;
            }

            return $js_css;
        }


        //SET MATERIAL ICONS
        private static function setMaterialIcons ($js_css): array
        {
            $js_css['material_icons_font'] = '';
            if ($js_css['material_icons'] == 'YES') {
                $js_css['material_icons_font'] = '
				<link href="https://fonts.googleapis.com/icon?family=Material+Icons|Material+Icons+Outlined|Material+Icons+Two+Tone|Material+Icons+Round|Material+Icons+Sharp"
				rel="stylesheet" />';
            }

            return $js_css;
        }


        //SET FONTAWESOME FONT
        private static function setFontawesomeFont ($js_css): array
        {
            $js_css['fontawesome-font'] = '';

            switch ($js_css['fontawesome']) {
                case '4.7.0':
                case 'YES':
                    $js_css['fontawesome-font'] = '<link href="/vendor/fortawesome/font-awesome/css/font-awesome.min.css" rel="stylesheet">';
                    break;

                case '5.0.13':
                    $js_css['fontawesome-font'] = '
                    <script defer src="https://use.fontawesome.com/releases/v5.0.13/js/solid.js" integrity="sha384-tzzSw1/Vo+0N5UhStP3bvwWPq+uvzCMfrN1fEFe+xBmv1C/AtVX5K0uZtmcHitFZ" crossorigin="anonymous"></script>

                    <script defer src="https://use.fontawesome.com/releases/v5.0.13/js/fontawesome.js" integrity="sha384-6OIrr52G08NpOFSZdxxz1xdNSndlD4vdcf/q2myIUVO0VsqaGHJsB0RaBE01VTOY" crossorigin="anonymous"></script>';
                    break;

                default: //don't load
                    break;
            }

            return $js_css;
        }


        //SET STRIPE
        private static function setStripe ($js_css): array
        {
            $js_css['stripe_js'] = '';
            if ($js_css['stripe'] == 'YES' ) {

                $js_css['stripe_js'] = '<script src="https://js.stripe.com/v3/"></script>';

            }

            return $js_css;
        }


        //SET_PAGE_INFO
        public static function set_page_info($bc): string
        {
            //dbg(__CLASS__.' | '.__FUNCTION__.' | '.__LINE__.' | parsing url PHP_SELF', parse_url($_SERVER['PHP_SELF']));

            $pageArr = parse_url($_SERVER['REQUEST_URI']);
            $pageArr = parse_url($_SERVER['PHP_SELF']);

            //dbg(__CLASS__.' | '.__FUNCTION__.' | '.__LINE__.' | page parts ', $pageArr);

            $pageURI = $pageArr['path'];
            if (stripos($pageURI, '.php') !== FALSE) {
                $pageURI = substr($pageURI, 0, (strlen($pageURI) - 4));
            }
            dbg(__CLASS__.' | '.__FUNCTION__.' | '.__LINE__.' | pageURI for seo table ', $pageURI);

            $pageInfo = $bc->find_rows('seo', ['PageURI' => $pageURI], 'PageURI, PageTitle, MetaDescrip, CanonicalRef, NoIndex', 'PageURI');
            //dbg(__CLASS__.' | '.__FUNCTION__.' | '.__LINE__.' | $pageInfo', $pageInfo);

            if (!empty($pageInfo)) {

                $noIndex = '';
                if ($pageInfo[0]['NoIndex'] == 1) {
                    $noIndex = '
					<meta name="robots" content="noindex">';
                }

                $canonical = '';
                if(!empty($pageInfo[0]['CanonicalRef'])) {
                    $canonical = '<link rel="canonical" href="'.$pageInfo[0]['CanonicalRef'].'">';
                }

                return '
                <title>'.$pageInfo[0]['PageTitle'].'</title>
                <meta name="description" content="'.$pageInfo[0]['MetaDescrip'].'"/>
                '.$canonical.$noIndex;
            }

            return '';
        }


        //SET_SUBNAV
        public static function set_subnav($subnavName): string
        {
            $host = $_SERVER['HTTP_HOST'];
            $subnavHtml = '';

            dbg(__CLASS__.' | '.__FUNCTION__.' | '.__LINE__.' | $host', $host );

            $flyoutMenu = self::setFlyoutMenu($subnavName);

            /*switch ($subnavName) {

                case 'foundation_program':
                case 'advanced_program':

                    $subnavHtml = '
                    <div class="container subnav text-center" id="subnav">
                        
                        <ul style="display:inline;">
                        
                             <li class="menu-item">
                                <a href="'.$briia.'" id="briia_home" target="_blank">BRIIA</a>
                            </li>
                            
                            <li class="menu-item">|</li>
                             
                            <li class="menu-item dropdown">
                            
                                '.$fndMenu.'
                                
                            </li>
                                                       
                            <li class="menu-item">|</li>
                            
                            <li class="menu-item dropdown">
                                
                                '.$advMenu.'
                                
                            </li>
                            
                            <li class="menu-item">|</li>
                            
                            <li class="menu-item" >
                                 <a id="menu_tia_login"
                                  href="/login" target="_blank">Login</a>
                            </li>
                            
                        </ul>
                    </div>'

                    .$flyoutMenu;

                    break;

                case 'gps_menu':

                    $subnavHtml = '
                    <div class="container subnav text-center" id="subnav">
                        
                        <ul style="display:inline;">
                        
                             <li class="menu-item">
                                <a href="'.$briia.'" id="briia_home" target="_blank">BRIIA</a>
                            </li>
                            
                            <li class="menu-item">|</li>
                             
                             <li class="menu-item">
                                <a href="/" id="tia_home" target="_blank">Overview</a>
                            </li>

                            <li class="menu-item">|</li>
                            
                            <li class="menu-item" >
                                 <a id="menu_tia_login"
                                  href="/login" target="_blank">Login</a>
                            </li>
                            
                        </ul>
                    </div>'

                    .$flyoutMenu;

                    break;


                case 'scorecard_menu':
                    $subnavHtml = '
                    <div class="container subnav text-center" id="subnav">
                        
                        <ul style="display:inline;">
                        
                             <li class="menu-item">
                                <a href="/tia_app/tia_scorecard" id="tia_scorecard">Back to my GPS Score</a>
                            </li>
                                                      
                        </ul>
                    </div>'

                    .$flyoutMenu;
                    break;

                default:
                    $subnavHtml = '';
            }*/

            return $subnavHtml;
        }


        //SET FLYOUT MENU
        private static function setFlyoutMenu($mode='foundation_program'):string
        {
            $host = $_SERVER['HTTP_HOST'];

            switch ($mode) {

                case 'gps_menu':
                    $menuHtml = '
                    <div class="flyoutMenu" style="display: none;" >
                        <ul>
                            <li><h6><a href="'.$briia.'">BRIIA</a></h6></li>
                            <li><a href="/">TIA Overview</a></li>
                            <li><a href="/login" target="_blank">Login</a>
                        </ul>                    
                    </div>';
                    break;

                case 'scorecard_menu':
                    $menuHtml = '
                    <div class="flyoutMenu" style="display: none;" >
                        <ul>
                             <li><a href="/tia_app/tia_scorecard" id="tia_scorecard" target="_blank">Back to my GPS Score</a></li>
                        </ul>                    
                    </div>';

                    break;

                case 'foundation_program':
                case 'advanced_program':
                default:
                    $menuHtml = '
                    <div class="flyoutMenu" style="display: none;" >
                        <ul>
                            <li><h6><a href="'.$briia.'">BRIIA</a></h6></li>
                            <li><hr></li>
                            <li><h6>Advanced Programs</h6>
                            <li><a href="/advanced_genai">Business AI</a>
                            <li><a href="/advanced_hcai">Healthcare AI</a>
                          
                            <li><a href="/advanced_apply" target="_blank">How to apply</a>
                            <li><a href="/advanced_registration" target="_blank">Register</a> 
                            <li><a href="/tia_app/faqs" target="_blank">More details</a></li>
                          
                            <li><hr></li>
                            
                            <li><h6>Foundation Program</h6>
                            <li><a href="/">Overview</a></li>
                            <li><a href="/tia_app/faqs" target="_blank">More details</a></li>
                            <li><a href="/tia_app/tia_gps_profile" target="_blank">Start my Journey</a>
                            <li><a href="/login" target="_blank">Login</a>
                        </ul>                    
                    </div>';

                    break;
            }

            return $menuHtml;
        }


        //SET_GOOGLE_ANALYTICS
		private static function set_google_analytics($includeGA): array
        {
			$ga = '';
			$gtm_top = '';
			$gtm_btm = '';

            //dbg(__CLASS__.' | '.__FUNCTION__.' | '.__LINE__.' | selecting ga for host', $_SERVER['HTTP_HOST']);

            //AS OF 5/25/22 - unified gtag property -  (tia, briia, etc) but separate gtags for dev, staging and production
            //for staging
            if (stripos($_SERVER['HTTP_HOST'],'staging') !== FALSE) {

                $gtm_top = '
                <!-- Global site tag (gtag.js) - Google Analytics -->
                <script async src="https://www.googletagmanager.com/gtag/js?id=G-NTWDEFF6VD"></script>'
                    . "
                <script>
                  window.dataLayer = window.dataLayer || [];
                  function gtag(){dataLayer.push(arguments);}
                  gtag('js', new Date());
                  gtag('set', 'content_group', 'tia');               
                  gtag('config', 'G-NTWDEFF6VD', {'debug_mode': true});
                </script>";

            // for production
            } elseif (stripos($_SERVER['HTTP_HOST'],'briia.io') !== FALSE)  {

                $gtm_top = '
                <!-- Global site tag (gtag.js) - Google Analytics -->
                <script async src="https://www.googletagmanager.com/gtag/js?id=G-8CESQ3V9FM"></script>'
                    . "
                <script>
                  window.dataLayer = window.dataLayer || [];
                  function gtag(){dataLayer.push(arguments);}
                  gtag('js', new Date());
                  gtag('set', 'content_group', 'tia');                
                  gtag('config', 'G-8CESQ3V9FM');
                </script>";

            //for dev
            } else {

                $gtm_top = '
                <!-- Global site tag (gtag.js) - Google Analytics -->
                <script async src="https://www.googletagmanager.com/gtag/js?id=G-HDJRMDK7CG"></script>'
                    . "
                <script>
                  window.dataLayer = window.dataLayer || [];
                  function gtag(){dataLayer.push(arguments);}
                  gtag('js', new Date());
                  gtag('set', 'content_group', 'tia');                
                  gtag('config', 'G-HDJRMDK7CG', {'debug_mode': true});
                </script>";
            }

            return [
                'ga' => $ga,
                'gtm_top' => $gtm_top,
                'gtm_btm' => $gtm_btm
            ];

            //GOOGLE_ANALYTICS set in local; should be 'OFF' for dev; 'ON' for Production
			/*if (defined('GOOGLE_ANALYTICS') && GOOGLE_ANALYTICS == 'ON' && $includeGA != "NO") {

                // for staging
                if (stripos($_SERVER['HTTP_HOST'],'staging') !== FALSE) {
                    $gtm_top .= "
                        <!-- Global site tag (gtag.js) - Google Analytics -->
                        <script async src='https://www.googletagmanager.com/gtag/js?id=G-K881NC87NJ'></script>
                        <script>
                            window.dataLayer = window.dataLayer || [];
                            function gtag(){dataLayer.push(arguments);}
                            gtag('js', new Date());
                        
                            gtag('config', 'G-K881NC87NJ');
                        </script>
                    
                        <!-- global site tag(gtag . js) - Google Analytics GA4 -->
                        <!--<script async src = 'https://www.googletagmanager.com/gtag/js?id=G-V3J4JZ57L8' ></script >
                        <script >
                          window . dataLayer = window . dataLayer || [];
                          function gtag()
                          {
                              dataLayer . push(arguments);
                          }
                          gtag('js', new Date());
                        
                          gtag('config', 'G-V3J4JZ57L8');
                        </script >-->                        
                    ";

                    $gtm_btm .= '
                        <!-- Google Tag Manager (noscript) -->
                        <!-- <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-M6654Q5"
                        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript> -->
                        <!-- End Google Tag Manager (noscript) -->
                    ';

                } else {  //default for production website

                    //this is the tag manager tied to the tia.briia.io GA4 property 296532964
                    $gtm_top .= "
                    <!-- Google Tag Manager -->
                    <script>
                        (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
                        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
                        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
                        })(window,document,'script','dataLayer','GTM-MG8GF49');
                    </script>
                    <!-- End Google Tag Manager -->
                    
                    <!-- Global site tag (gtag.js) - Google Analytics -->
                    <script async src='https://www.googletagmanager.com/gtag/js?id=G-YB7ZYJ9XT9'></script>
                    <script>
                      window.dataLayer = window.dataLayer || [];
                      function gtag(){dataLayer.push(arguments);}
                      gtag('js', new Date());
                    
                      gtag('config', 'G-YB7ZYJ9XT9');
                    </script>";


                    $gtm_btm = '
    
                    <!-- Google Tag Manager (noscript) -->
                    <noscript>
                        <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MG8GF49"
                            height="0"
                            width="0" 
                            style="display:none;
                            visibility:hidden"></iframe>
                    </noscript>
                    <!-- End Google Tag Manager (noscript) -->
                    ';
                }

			}
			
			return [
				"ga" => $ga,
				"gtm_top" => $gtm_top,
				"gtm_btm" => $gtm_btm
			];*/
		}
		

		//SET_TOP
		private static function set_top($gaArr, $includeGA, $pageInfo): string
        {
            if ($includeGA == "NO") {
                $gaArr['gtm_top'] = "";
            }

            $viewport = '';
            if (empty($js_css['chartjsScript'])) {
                //can not be used on any page displaying a chartjs
                $viewport = '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">';
            }
            
			return '<!DOCTYPE html>
			<html lang="en">
			  <head>
			  	'.$gaArr['gtm_top'].'
				<meta charset="utf-8">
				<meta http-equiv="X-UA-Compatible" content="IE=edge">'
                .$viewport
                .$pageInfo;

		}


		//SET_HEAD
		private static function set_head($top_css_js, $js_css, $gtm_btm): string
        {
			$topnav = "";
			if ($js_css['navLocation'] == "TOP") {
				$topnav = self::set_top_nav();
			}
			
			$tia_styles = self::set_tia_styles($js_css);

			$modal = "";
			if ($js_css['modal'] == "YES") {
				$modal = '
				<link href="/css/modal.css" rel="stylesheet">';
			}

			$head = "";

			$head .= '

			<!-- Bootstrap -->
			'.$js_css['bsCss'].'

			<!-- jQuery UI -->
			'.$js_css['jq_uiCss'].'

			<!-- Favicon -->
			<link rel="shortcut icon" href="/images/favicon.ico" type="image/x-icon">

			<link rel="icon" href="/images/favicon.ico" type="image/x-icon">
			<link rel="apple-touch-icon" sizes="57x57" href="/apple-icon-57x57.png">
			<link rel="apple-touch-icon" sizes="60x60" href="/apple-icon-60x60.png">
			<link rel="apple-touch-icon" sizes="72x72" href="/apple-icon-72x72.png">
			<link rel="apple-touch-icon" sizes="76x76" href="/apple-icon-76x76.png">
			<link rel="apple-touch-icon" sizes="114x114" href="/apple-icon-114x114.png">
			<link rel="apple-touch-icon" sizes="120x120" href="/apple-icon-120x120.png">
			<link rel="apple-touch-icon" sizes="144x144" href="/apple-icon-144x144.png">
			<link rel="apple-touch-icon" sizes="152x152" href="/apple-icon-152x152.png">
			<link rel="apple-touch-icon" sizes="180x180" href="/apple-icon-180x180.png">
			<link rel="icon" type="image/png" sizes="192x192"  href="/android-icon-192x192.png">
			<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
			<link rel="icon" type="image/png" sizes="96x96" href="/favicon-96x96.png">
			<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
			<link rel="manifest" href="/manifest.json">
			<meta name="msapplication-TileColor" content="#ffffff">
			<meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
			<meta name="theme-color" content="#ffffff">

			<!--icons-->
			'.$js_css['material_icons_font'].'

			<!-- TIA CSS -->
			'.$tia_styles
			.$modal.'

			<!-- Internet Explorer -->

			<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->

			<!-- WARNING: Respond.js does not work if you view the page via file:// -->

			<!--[if lt IE 9]>

				<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>

				<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>

			<![endif]-->

			<!-- additional css/js -->'.

			$top_css_js.
			$js_css['chartCss'].
            $js_css['chartjsScript'].
			$js_css['stripe_js'];

			$head .= '

		  </head>

		  <body>
			'.$gtm_btm.'
			<header>

			</header>

			<div id="page_body">

				'.$topnav.'
			  ';

			return $head;
		}


		//SET_TIA_STYLES
		private static function set_tia_styles($js_css): string
        {
			return '
			<link href="/css/pi48.css" rel="stylesheet">
			
			<!-- Font Awesome JS -->
			'.$js_css['fontawesome-font'];
		}


		//SET_TOP_NAV
		private static function set_top_nav(): string
        {
            $loginLogout = "Login";
            $href= '/login';
            
            if (!in_array($_SERVER['REQUEST_URI'], ['/', '/custom_plan_a', '/custom_plan_b'])) {
                $loginLogout = '';
            }

            //NOTE: logo does not link by design as may have unintended consequences during the GPS/Scorecard journey

            $showSubnav = ['advanced_apply', 'advanced_registration', 'advanced_hcai', 'advanced_genai',
                'advanced_apply.php', 'advanced_registration.php', 'advanced_hcai.php', 'advanced_genai.php', 'tia_gps_index', 'tia_cda', 'cda', 'tia_login', 'login', 'tia_gps_profile', 'scorecard'];

            $thisPage = basename(parse_url($_SERVER['PHP_SELF'], PHP_URL_PATH));

            $subNavHtml = '';
            if (in_array($thisPage, $showSubnav)) {
                $subNavHtml = '
			    <div class="menu-icon">
			        <span class="material-icons-round">menu</span>
			    </div>';
            }

			return '
			<header>'
                .$subNavHtml.'
				<div class="pageHead text-center">
                    <img src="/images/BRIIA_logo_white_wCirR.png" class="logo img-fluid" style="max-height:50px;" alt="BRIIA logo registered trademark">
					<h3>The Intelligent Accelerator&reg;</h3>
					<a id="loginLink" href="'.$href.'" target="_blank">'.$loginLogout.'</a>
				</div>
			</header>
			';
		}


		//SET_SIDE_NAV
		private static function set_side_nav(): string
        {
			$tiaVers = "";
			if (!empty($_SESSION['tiaVers'])) {
				$tiaVers = $_SESSION['tiaVers'];
			}

			$logout_login = "Login";
			if (!empty($_SESSION['UID'])) {
				$logout_login = "Logout";
			}

			$loginName = "";
			if (!empty($_SESSION['u_name'])) {

				$loginName = '
				<div class="sidebar-header"
					style="padding: 0 13px;">
					'.$_SESSION['u_name'].'</div>

				<div class="sidebar-header" style="font-size:11px; padding: 0 13px 15px; font-weight: 300;">
					'.$tiaVers.'</div>';
			}

			//disable menu while not yet onboard
			$disabled = "";
			$title = "";
			if (basename($_SERVER['REQUEST_URI']) == "assessment_check") {
				$disabled = "disabled";
				$title = "Please confirm your GPS responses.";
			}

			//set Provisions visibility
			/*$host = $_SERVER['HTTP_HOST'];
			if ($host == 'tia.briia.io') {
                $myProvisions = '';
                $provisioners = '
                    <a>Provisions <span class="coming-soon">coming soon</span></a>';

            } else {*/
                $myProvisions = '
			        <li>
						<a href="/tia_app/my_provisions" title="'.$title.'">My Provisions</a>
					</li>';

                $provisioners = '
			        <a href="/tia_app/provisions" title="'.$title.'">Provisions</a>';
			//}

			return '
				<!-- Sidebar  -->
				<nav id="sidebar">
					<div class="sidebar-header">
						<h3>The Intelligent Accelerator</h3>
					</div>

					'.$loginName.'

					<ul class="list-unstyled components '.$disabled.'">
						<li>
							<a href="/tia_app/journey" title="'.$title.'">My Journey</a>
						</li>
						<li>
							<a href="/tia_app/my_mentors" title="'.$title.'">My Mentors</a>
						</li>
						<li>
							<a href="/tia_app/my_courses" title="'.$title.'">My Courses</a>
						</li>
						<li>
							<a href="/tia_app/portfolio" title="'.$title.'">My Portfolio</a>
						</li>
						
						'.$myProvisions.'
						
						<li>
							<a href="/tia_app/schedule" title="'.$title.'">My Schedule</a>
						</li>

						<li>
							<a href="#pageSubmenu"
							 data-toggle="collapse" aria-expanded="false" class="dropdown-toggle" title="'.$title.'">Resources</a>
							<ul class="collapse list-unstyled" id="pageSubmenu">
								<li>
									<a href="/tia_app/courses" title="'.$title.'">Courses</a>
								</li>
								<li>
									<a href="/tia_app/mentors" title="'.$title.'">Mentors</a>
								</li>

								<li>
									'.$provisioners.'
								</li>
								<li>
									<a href="/tia_app/special_offers" title="'.$title.'">Special offers</a>
								</li>
								<li>
									<a href="/tia_app/recommended"
									title="'.$title.'">Recommended...</a>
								</li>

							</ul>
						</li>

						<li>
							<a>Memberships <span class="coming-soon">coming soon</span></a>
						</li>

						<li>
							<a href="/tia_app/community" title="'.$title.'">Community</a>
						</li>

						<li>
							<a href="#advPgmMenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle caret" title="'.$title.'">Advanced Programs</a>

							<ul class="collapse list-unstyled" id="advPgmMenu">
								<li>
									<a href="/tia_app/advanced_genai" title="'.$title.'">Business AI</a>
								</li>
								<li>
									<a href="/tia_app/advanced_hcai" title="'.$title.'">Healthcare AI</a>
								</li>
								<li>
									<a href="/tia_app/advanced_registration" title="'.$title.'">Register</a>
								</li>
								
								<!--<li>
									<a href="/tia_app/advanced_scaling" title="'.$title.'">Scaling</a>
								</li>-->
							</ul>
						</li>

						<li>
							<a href="#moreMenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle caret" title="'.$title.'">More</a>

							<ul class="collapse list-unstyled" id="moreMenu">

								<li>
									<a href="/tia_app/company" title="'.$title.'">My Company</a>
								</li>
								<li>
									<a href="/tia_app/profile" title="'.$title.'">My Profile</a>
								</li>
								<li>
									<a href="/tia_app/feedback" title="'.$title.'">Feedback</a>
								</li>
								<!--<li>
									<a href="/tia_app/support" title="'.$title.'">Support</a>
								</li>-->
							</ul>
						</li>

						<li class="never-disabled">
							<a href="/login" >'.$logout_login.'</a>
						</li>
					</ul>

					<div class="copyright" id="copyright">
						<span>
							&copy; Copyright BRIIA, Inc. 2020
						</span>
						<div id="tos" class="float-link-left"><a href="/tos">Terms of Service</div>
						<div id="privacy" class="float-link-right"><a href="/privacy">Privacy Policy</a></div>
					</div>
				</nav>';

		}


        //SET_MODAL_HTML
        public static function setModalHtml($id, $title, $form, $size='') {

            return '
            <div class="modal fade" id="'.$id.'" style="padding-right: 0 !important">

                <div class="modal-dialog '.$size.'">

                    <div class="modal-content">

                        <div class="modal-header">

                            <div class="modal-title-text">'.
                                $title.'
                            </div>

                            <button class="close" type="button" data-bs-dismiss="modal" aria-hidden="true">&times;</button>

                        </div>

                        <div class="modal-body text-center">'.

                             $form.'

                        </div>
                    </div>
                </div>
            </div>
			';
        }


        //SET LOGIN FORM
        public static function setLoginForm(): string
        {
            return '
            <div class="center-block">	
			
                <form id="login_form"><input type="hidden" name="form_key" id="form_key" value="0b6ba206c4d1e9d445a364064f9f9ca6">
                    
                    <input name="formID" value="login_form" type="hidden">
                    
                    <div class="row">
                        <div class="col-12">	
                            <label title="If more than one name is on the agreement, use the lastname of the first person listed on the agreement.">Enter the <span style="font-weight: bold;">last name</span> on your Brindley Beach agreement: </label>
                        </div>
                    </div>
                    <div class="row mt-1">
                        <div class="col-12">	
                            <input name="u_name" id="u_name" class="enabled text-center" style="margin:auto;" type="text" required="" placeholder="last name only">
                        </div>
                    </div>
                                        
                    <div class="row mt-3">
                        <div class="col-12">	
                            <label>Enter the <span style="font-weight: bold;">Saturday date</span> your "OZ" rental began (mm/dd/yy):  </label>
                        </div>
                    </div>
                    <div class="row mt-1">
                        <div class="col-12">	
                            <input id="u_pw" name="u_pw" class="enabled typeahead text-center" style="margin: auto;" type="text" required="" placeholder="mm/dd/yy">
                        </div>
                    </div>
                    
                    <!--<div class="row mt-3 text-center">
                        <div class="col-12">            
                            <a href="#" style="font-size: 12px;" data-toggle="modal" class="login_help_modal" id="login_help_modal">Need help logging in?</a>	
                        </div>				
                    </div>-->
                    
                    <div class="row mt-3 text-center">		
                        <div class="col-12">	
                            <input name="submitLogin" id="loginBtn" class="btn btn-primary btn-sm" type="submit" value="Login" style="margin: auto; width: 100px;">
                        </div>
                    </div>	
                </form>
            
                <div id="alerts" class="marg_top1"></div>
            </div>
            ';
        }

		//SET_FOOTER
        public static function set_footer(): string
        {
            $dt = new DateTime();
            
            return '
			<div style="padding: 50px 0 0;">
			
                <footer>
    
                    <div class="container">
                        
                        <div class="row">
    
                            <div class="col-12 text-center">
    
                                <address class="">&copy; 2017 - '.$dt->format('Y').'
    
                             </div>
                            
                        </div>
                        
                        <div class="row">
                            
                            <div class="col-12 text-center" style="margin-top:0; padding-top: 0;">
                                <a href="/privacy">Privacy Policy</a>
                                <a href="/tos" style="margin-left:15px">Terms of Service</a>
                                <a href="/tia_app/gps_feedback" class="launch_modal" style="margin-left:15px" rel="nofollow">Support / Feedback</a>
                            </div>
    
                        </div>
    
                    </div>
    
                </footer> <!--footer-->
                
            </div>

			';
        }
		

		//SET_BOTTOM_JS
		public static function set_bottom_js($js_css): string
        {
			$pgName = $_SERVER['REQUEST_URI']; // '/...?..'

    		$footerjs = '';
			if (!empty($js_css['footer']) && $js_css['footer'] == "YES") {
			   $footerjs = '<script src="/js/tia_gps_feedback.js"></script>';
            }
			
			return '

			    <!-- jQuery (necessary for Bootstrap and JavaScript plugins) -->
				'.$js_css['jQueryJs'].'

			    <!-- jQuery UI  -->
				'.$js_css['jq_uiJs'].'

				<!-- popper -->'.
				$js_css['popper'].'

			    <!-- Include all compiled plugins (below), or include individual files as needed -->
				'.$js_css['bsJs'].

				$js_css['chartjsScript'].

                //$js_css['responsiveTabs'].  // not used? 12/27/21
                
                $footerjs.'

				<script>
					
                    /*if (! $.fn.modal)
                    {
                        document.write(\'<link rel="stylesheet" href="\/vendor\/twbs\/bootstrap\/dist\/css\/bootstrap.min.css"\');
                        document.write(\'<script src="\/vendor\/twbs\/bootstrap\/dist\/js\/bootstrap.min.js"><\/script>\');
                    }*/
    
                    $(function() {
    
                        $(".components a[href$='."'".$pgName."'".']").addClass("active");
    
                        $("a.active").parents("ul.collapse").show();
                        $("a.active").parents("li").find("a.dropdown-toggle").removeClass("dropdown-toggle");
    
                        //button for sidebar hide/show
                        $("#sidebarCollapse").on("click", function () {
                            $("#sidebar, #content").toggleClass("active");
                            $(".collapse.in").toggleClass("in");
                            $("a[aria-expanded=true]").attr("aria-expanded", "false");
                            let icon;
                            let tos = $("#tos");
                            let thisIcon = $(this).find(".material-icons").html();
                            if ($(thisIcon) === "arrow_left") {
                                icon = $(this).find(".material-icons");
                                $(icon).html("arrow_right");
                                $(".alt-brand").css("display","inline");
                                $(".copyright").addClass("copyright-expand");
                                $(tos).addClass("float-link-right");
                                $(tos).removeClass("float-link-left");
                                
                            } else {
                                icon = $(this).find(".material-icons");
                                $(icon).html("arrow_left");
                                $(".alt-brand").hide();
                                $(".copyright").removeClass("copyright-expand");
                                $(tos).removeClass("float-link-right");
                                $(tos).addClass("float-link-left");
                            }
                        });
    
                        //BACKGROUND OF ACTIVE sidenav selection
                         $(".components ul li a").on("click",function() {
                            //alert("dropdown clicked");
                            $(".components").find(".active").removeClass("active");
                            $(this).addClass("active");
                            //$("a.active").closest("ul.collapse").show();
                        });
                        
                        $(window).resize( function () {
                            footer_adjust();
                        });
                        
                    });
                    
                    function footer_adjust () {
                        
                        let sidebarLeft = $("#sidebar").css("margin-left");
                        let tos = $("#tos");
                        
                        if (parseInt(sidebarLeft) * 1 === 0) {
                        
                            $(tos).removeClass("float-link-right");
                            $(tos).addClass("float-link-left");
                            $(".copyright").removeClass("copyright-expand");
                                
                        } else {                          
                            $(tos).addClass("float-link-right");
                            $(tos).removeClass("float-link-left");
                            $(".copyright").addClass("copyright-expand");
                        }
                    }
                    
                    footer_adjust();
                    
                    //subnav collapse expand
                    /*function setSubnav() {
                        let x = $("#subnav");
                        if (x.hasClass("subnav")) {
                            x.addClass("responsive");
                        } else {
                            x.removeClass("responsive");
                        }
                    }*/
				
				    $(document).on("click", ".menu-icon", function () 
				    {
                        let flyoutMenu = $(".flyoutMenu");    
				        
                        if (flyoutMenu.hasClass("active")) {
                            flyoutMenu.removeClass("active");
                            flyoutMenu.hide();
                        } else {
                            flyoutMenu.css("display", "block");
                            flyoutMenu.addClass("active");
                        }
				    });
                    
				</script>';
		}


		//SET_CLOSING_TAGS
		public static function set_closing_tags(): string
        {
			return '
				</body>
			</html>';
		}

    }
