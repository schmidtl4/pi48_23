<?php
	// no includes...assumes local_config is part of base_page.class and this class is included in base_page

	class base_nav {
		//===== CONSTRUCT =====//
		// set defaults: read from json, position: top, site_ref from local_config, default links from set_linkarray

        public $t_nav;
        public $top_links;
        public $json_path;
        public $link_array;
        public $linkarray;
        public $nav_type;
        public $nav_content;
        public $nav_position;
        public $default_a_class;
        public $default_li_class;
        public $default_div_class;
        public $lg_li_string;
        public $sm_li_string;
        public $social_icon_string;

        function __construct() {
		}
		
		//===== RENDER NAV =====//
		/*** construct nav and push string to browser ***/
		//not used by base_page.class; available if needed to build nav only
		function render_nav() {
			
			// build default menu links
			$this->read_json();
			$this->assemble_nav();
			//echo($this->nav_content);		
		}
		
		
		// READ_JSON
		function read_json() {
			
			//read values from json default fields file
			$this->link_array = json_decode(file_get_contents($this->json_path),true);
			
			//set top links
			$this->top_links = array_keys($this->link_array);
			
			/*pp('link_array',$this->link_array);
			pp('top_links',$this->top_links);
			die;*/
			
		}
		
		
		
		//===== ASSEMBLE NAV =====//
		
		// ASSEMBLE_NAV
		function assemble_nav() {
			$this->set_class_defaults();
			$this->set_site_ref();
			$this->process_link_array();
			$this->add_wrapper_html();
		}
		
		
		//PROCESS_LINK_ARRAY
		//sequentially proceed through link_array
		function process_link_array() {
			
			/*pp('link_array',$this->link_array);
			die;*/
			
			foreach ($this->link_array as $a_link) {
				/*echo '<br>';
				var_dump($a_link);*/
				
				switch ($a_link['link_level']) {
					case 'brand':
						$this->build_brand($a_link);
						break;
						
					case 'top':
						$this->build_top($a_link);
						break;
						
					case 'sub':
						$this->build_sub($a_link);
						break;
						
					case 'subsub':
						$this->build_subsub($a_link);
						break;
						
					default:
						break;
				}
			}
		}
		
		
		//BUILD_BRAND
		function build_brand($link_array) {
			$this->lg_li_string = '
				<li class="nav-brand">
					<a href="'.$link_array["link_page"].'">
						<h1>'.$link_array["link_display"].'</h1>
					</a>
				</li>
						
			';
		}
		
		
		// BUILD_TOP
		function build_top($link_array) {
			if ($link_array['li_class'] == "dropdown") {
				
				$sub_str = $this->build_sub($link_array['sub_menu']);
				
				$this->lg_li_string .= '
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="'.
						$link_array['link_page'].'">'.
						$link_array['link_display'].
						'<span class="caret"></span></a>'.
						$sub_str.
					'</li>';
			} else {				
				$li_string = $this->build_one_item($link_array);
				$this->lg_li_string .= $li_string;
			}
		}
		
		//BUILD_SUB
		function build_sub($sub_array) {
			
			$sub_str = '<ul class="dropdown-menu" role="menu">';
			
			foreach($sub_array as $item) {
				$sub_str .= '
					<li><a href="'.$item['link_page'].
					'">'.
					$item['link_display'].'</a></li>';
			}
			
			$sub_str .= '</ul>';
			
			return $sub_str;
		}
		
		
		// BUILD_SUBSUB
		function build_subsub($link_array) {
		}
		
	
		
		// SET_CLASS_DEFAULTS 
		//(defaults, overide on page instance)
		function set_class_defaults() {
			switch ($this->nav_position) {
				case 'top' :
					$this->default_a_class = '';
					$this->default_li_class='';
					$this->default_div_class='';
					break;
					
				case 'btm' :
					$this->default_a_class = 'normaltip';
					$this->default_li_class = '';
					$this->default_div_class = '';
					break;
			
				case 'rit' :
				case 'lft' :
					break;
			}
		}
		
		
		// BUILD_ONE_ITEM
		//(desktop) - turn array fields into <li></li> string
		function build_one_item($nav_array) {
			
			//NOTE:  BOTTOM ALWAYS USE DISPLAY_SM			
			$one_item = '';

			// dont include class tag for <li> if nothing in class tags			
			if (!empty($nav_array['li_class'])) {
				$li_class = 'class="'.$this->default_li_class.' '.$nav_array['li_class'].'" ';
			} else if (empty($nav_array['li_class']) and empty($this->default_li_class)) {
				$li_class ='';
			} else {
				$li_class = ' class="'.$this->default_li_class.'" ';
			}

			//if link supplied starts with http or www, then don't prepend with site_ref			
			if (!empty($nav_array['link_page']) and 
				(mb_stripos($nav_array['link_page'],'http') === 0 or mb_stripos($nav_array['link_page'],'www') === 0)) {
				$link_page = '';		
			} else if (mb_stripos($nav_array['link_page'],'#') !== false) {
				$link_page = 'href="#" ';	
			} else {
				$link_page = 'href="'.$nav_array['link_page'].'" ';
			}
						
			//handle class for <a> link
			if (!empty($nav_array['link_class'])) {
				$link_class = ' class="'.$this->default_a_class.' '.$nav_array['link_class'].'"';
			} else if (empty($nav_array['link_class']) and empty($this->default_a_class)) {
				$link_class = '';
			} else {
				$link_class = ' class="'.$this->default_a_class.'" ';
			}
			
			//handle id for <a> link
			if (!empty($nav_array['link_id'])) {
				$link_id = 'id="'.$nav_array['link_id'].'" ';
			} else {
				$link_id = '';
			}
			
			//handle title for <a> link
			if (!empty($nav_array['link_tip'])) {
				$link_tip = 'title="'.$nav_array['link_tip'].'" ';
			} else {
				$link_tip = '';
			}
								
			
			//now build li string
			$one_item = '
				<li '.
				$li_class.
				'><a '.
				$link_id.
				$link_page.
				$link_class.
				$link_tip.
				'>'.
				$nav_array["link_display"].
				'</a></li>';
			
			return $one_item;
		}

		//=====SET NAV PARTS =====//
		// SET SITE REF
		function set_site_ref() {

			if (empty($this->site_ref)) {
				$this->site_ref = '/';
			}

		}
			
		// SET NAV POSITION
		function set_nav_position() {
			// set default nav_position
			// can be overridden; choices are: 'top', 'btm', 'rit', 'lft'
			// 'rit' and 'lft' not impltemented as of 131023
			if (empty($this->nav_position)) {
				$this->nav_position = 'top';				
			}
		}
		
		// ADD WRAPPER HTML
		function add_wrapper_html()
        {
            $dt = new DateTime();

            if ($this->nav_position == 'top') {
                if ($this->nav_type = 'pill') {
                    $this->nav_content = '
						<div class="container">
						<div class="navbar" style="z-index: 100;">
							<ul class="nav nav-pills">'.

                        $this->lg_li_string.'
							
								<!--<li style="margin-top:15px">'.$dt->format('M d Y').'</li>-->
							</ul>
		  
							
							<div class="hidden-sm hidden-md hidden-lg tighter">
									<ul class="nav nav-pills">'.

                        //$this->sm_li_string.

                        '</ul>
							</div>
						</div>
						</div>
					';
                }

            //for bottom navs small and lg
            } elseif ($this->nav_position = 'btm') {
				
				$this->set_social_icons();
				
				$this->nav_content = '
	
					<div class="row hidden-xs">
						<div class="footer text-center">
							<ul class="icons small">'.
			
								$this->lg_li_string.
								$this->social_icon_string.
								
							'</ul>
						</div>
					</div>
					
					<!--xs screens -->
					<div class="row visible-xs hidden-sm hidden-md hidden-lg">
						
						<div class="container">
							<div class="smaller icons sm-footer">'.
							
								$this->sm_li_string.
							
								'<div class="col-xs-12 pad_bot1">'.
								
										$this->social_icon_string.

								 '</div>
								 
							 </div>
						</div>	
				
					</div>
					
				';

			} else {
				// for rit and lft menus
			}
		
			
		}

		// ========== below this point are legacy functions; FUTURE:  to be integrated =========//
		
		
		/*** ASSEMBLE_SMALL_DIVS (default for m2 footer) ***/
		function assemble_sm_div($nav_array) {

			//if link supplied starts with http or www, then don't prepend with site_ref			
			if (!empty($nav_array['link_page']) and 
				(mb_stripos($nav_array['link_page'],'http') === 0 or mb_stripos($nav_array['link_page'],'www') === 0)) {
				$site_ref ='';
			} else {
				$site_ref = $this->site_ref;
			}
			
			if (!empty($nav_array['link_id_sm']) and !empty($nav_array['link_display_sm'])) {
				$one_sm_div = '
					<div class="text-left '.
						$this->default_div_class.
						'">
						<a id="'.
						$nav_array["link_id_sm"].
						'" href="'.
						$site_ref.
						$nav_array["link_page"].
						'" title="'.
						$nav_array["link_tip"].
						'">'.
						$nav_array["link_display_sm"].
						'</a>
					</div>
				';
			} else if (!empty($nav_array['link_display_sm'])) {
				$one_sm_div = '
					<div class="text-left '.
						$this->default_div_class.
						'">
						<a href="'.
						$site_ref.
						$nav_array["link_page"].
						'" title="'.
						$nav_array["link_tip"].
						'">'.
						$nav_array["link_display_sm"].
						'</a>
					</div>
				';
			} else if (!empty($nav_array['link_id_sm'])) {
				$one_sm_div = '
					<div class="text-left">
						<a id="'.
						$nav_array["link_id_sm"].
						'" href="'.
						$site_ref.
						$nav_array["link_page"].
						'" title="'.
						$nav_array["link_tip"].
						'">'.
						$nav_array["link_display"].
						'</a>
					</div>
				';
			} else if (!empty($nav_array["link_id"])) {
				$one_sm_div = '
					<div class="text-left">
						<a id="'.
						$nav_array["link_id"].
						'" href="'.
						$site_ref.
						$nav_array["link_page"].
						'" title="'.
						$nav_array["link_tip"].
						'">'.
						$nav_array["link_display"].
						'</a>
					</div>
				';
			} else {
				$one_sm_div = '
					<div class="text-left">
						<a href="'.
						$site_ref.
						$nav_array["link_page"].
						'" title="'.
						$nav_array["link_tip"].
						'">'.
						$nav_array["link_display"].
						'</a>
					</div>
				';
			}
			
			return $one_sm_div;

		}
		
	} // class
