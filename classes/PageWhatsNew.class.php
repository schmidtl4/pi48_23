<?php

    dbg('blank | '.__LINE__.' | *** TOP OF CLASS ***', '*');

	class PageWhatsNew extends BasePage {
		
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
                        <img class="img-fluid" src="/_images/babies_beach_750x200.jpg">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <div class="web-title">
                            <h2 style="margin: 10px 0; padding-top: 5px;">What\'s New? (2023)
                            </h2>
                        </div>
                        <div class="print-title" id="printTitle"></div>
                    </div>
                </div>
                
                <div class="row mt-3" >
                    <div class="col">
                        <h4 style="font-weight:bold;">Quick takes</h4>
                        <ul class="quick-takes">
                            <li>New plumbing!</li>
                            <li>Recirculating hot water added</li>
                            <li>Central vacuum updated</li>
                            <li>TV upgrades</li> 
                            <li>Paint/touchup</li>
                            <li>Screen porch siding</li>
                            <li>Corn hole boards</li>
                            <li>Kitchen (pots/pans, coffee grinder)</li>
                            <li>Bath mats?</li>
                            <li>Front lights</li>
                            <li>Exhaust fan timer switches</li>
                            <li>Electrical outlets / new circuits</li>
                            <li>Extended wifi</li>
                            <li>New cable boxes, modem and router</li>
                            <li>Paint touchup</li>
                            <li>Floors refinished?</li>
                            <li>New pool heater</li>
                            <li>Spa?</li>
                            <li>Elevator safety</li>
                            <li>This website!</li>
                       </ul>
                    </div>
                </div>
                
                <hr style="color: #A69256; height: 2px;">
                
                <div class="row mt-3" >
                    <div class="col">
                        <h4 style="font-weight: bold; padding-top: 10px;">The full story</h4>
                        <h5>Overview</h5>
                        <p>This was another "big project" year for us. We spent considerable time and resources upgrading the plumbing, replacing pool heaters, fixing the spa and replacing siding. While many of the things we\'ve done may not be visible, they are major updates to the home and contribute to fighting the impacts of the harsh oceanfront environment.  But we\'ve added a few pleasures as well:</p>
                        
                        <ul class="full-story">
                            <li>New plumbing</li>
                            <p>After nearly two years of planning and waiting for the right moment, we undertook the herculean task this year of replacing every inch of plumbing. This involved cutting open ceilings, walls and closets to allow removal of the old polybutylene pipes and replacing them with brand new PEX piping.  Why did we do this?  Apparently, polybutylene gets brittle and cracks over time. Fortunately, we\'ve never had a plumbing failure, but increasingly, failures were being reporting in older homes.  Hopefully, by replacing it all now, we will have avoided having any plumbing break for another 30 years! </p>
                            
                            <li>Hot water</li>
                            <p>Ever waited an eternity for there to be hot water to wash your hands, step into the shower or rinse a dish?  Well wait no more! We\'ve added a recirculating hot water system so that you should have hot water in a couple of seconds.  No matter which sink or tub or shower you are using, hot water will be coming fast!</p>
                            
                            <li>Central vacuum replacement</li>
                            <p>If you have used our central vacuum system lately, you probably noticed it didn\'t have very much suction.  We were scratching our heads about that too, but, thanks to having the walls and ceilings ripped out for the plumbing project, we discovered a hole the size of a silver dollar in one of the vacuum pipes.  After fixing that section of pipe, replacing the entire unit and updating a few of the vacuum outlets, it now works as expected.</p>
                            
                            <li>TV upgrades</li>
                            <p>Keeping up with technology is always a challenge but we made a couple of changes to the TV setup.  First, every TV has a new cable box.  In Master Bedroom 3, we\'ve replaced the previous TV with a new Samsung 40" "Smart TV".  In addition, we\'ve tossed the 1990\'s in the Rec Room on the bottom floor and replaced it with a Samsung TV and a Roku connection.  While this TV does not have a cable box, much of the functionality of the cable box is now available through Roku. </p>
                            
                            <p>Speaking of Roku, did you know that Roku gives you access to your own Netflix, Disney+, Paramount and Hulu accounts.  When you launch any of these apps, you can enter the date you are leaving OZ and all of your passwords will be wiped out on that date.</p>
                            
                            <li>Paint/touchup</li>
                            <p>In addition to the extensive repairs required by the plumbing project, there\'s been extensive painting touchup and wall repairs throughout the house. </p>
                            
                            <li>Screen porch siding</li>
                            <p>This year we replaced the original wood shake shingles present on the knee wall of the screen porch with cement-based siding like the rest of the house. No more loose or dropped shingles!</p>

                            <li>Corn hole boards</li>
                            <p>Corn hole boards take a beating at the beach.  We like to leave them near the sport court so they can be quickly used, but they often get left out in the rain or get a good soaking by the sprinklers when they are left on the bocce/sport court.  This year we\'re trying out boards that aren\'t made of wood and are supposed to be much more resistant to weather. Hopefully, the playing surface of these new boards will still allow expertly thrown bags to slide perfectly into the hole. Let us know what you think!</p>
                                                     
                            <li>Kitchen (pots/pans, coffee grinder)</li>
                            <p>We\'ve added pots and pans and a new coffee grinder. In case they aren\'t out on the counter, there\'s actually two Mr. Coffee makers - one marked for tea and the other one (perhaps unmarked) for coffee. You might find one in a cabinet. We know that tea drinkers don\'t appreciate coffee-flavored tea so please use these for their intneded purpose.</p>
                            
                            <li>Bath mats?</li>
                            <p>We\'ve added some bath mats in the Master baths. New materials make these highly water absorbent but hopefully they will also quickly dry in accordance with the manufacturer\'s description!</p>

                            <li>Front lights?</li>
                            <p>In our continued push to eliminate metal wherever we can, we\'ve replaced the lanterns on the front columns with acrylic globes. In addition, we discovered that the wires powering the columns had been severed so we reconnected them and purchased new bulbs and a light sensor.  These should now automatically come on at dusk and turn off at dawn.</p>                                                        
                            
                            <li>Exhaust fan timer switches</li>
                            <p>Turning on the exhaust fan after you are done showering really helps keep mildew and mold to a minimum. The challenge, of course, is remembering to turn the fan off in a reasonable amount of time.  Here\'s a case where electronics come to the rescue.  In Master Bath 1 and Master Bath 2, there\'s now a timer switch for the exhaust fan.  You can select 10, 20, 30 or 60 minutes on the switch and the fan will automatically shut off after the selected time expires.</p>
                            
                            <li>Electrical outlets / new circuits</li>
                            <p>Have you ever been waiting for the coffee to brew and the toaster to pop when you plug in the waffle iron and find that the power just went out to the entire kitchen?  Well that\'s not going to happen any more. To keep up with the use of many electrical appliances in the kitchen, we upgraded the number of circuits serving the kitchen from one to three! The two outlets on the island are one circuit, the two outlets on the east wall (where the sink is) are another circuit and the two outlets on the north wall (with the Microwave) is another circuit.  If you spread your devices around, you should not encounter any tripping breakers anymore!</p>
                            
                            <li>Extended wifi</li>
                            <p>In this day and age, fast, reliable wifi is expected no matter where you go. To improve the strength of the wifi signal throughout the house, we\'ve added an access point at the mid-level.  This should vastly improve the quality and speed of the wifi you are able to attain on the mid and lower levels of the house.</p>         
                            
                            <li>New modem and router</li>
                            <p>To further enhance your experience with accessing the internet at "Oz" we\'ve upgraded the modem and router we use to connect to the Spectrum internet service.  It has a new user name and password as well.  See the Internet page for more details.</p>

                            <li>Floors refinished</li>
                            <p>Whether it\'s dripping wet bathing suits, or sandy/salty feet coming back from the beach, hardwood floors take a beating.  Every 8-10 years, we refinish the floors in the great room and 2023 was that year.  We appreciate all that you can do to keep moisture off the floor and treat the floors with all the TLC you can muster!</p>                                                        
                            
                            <li>New pool heater</li>
                            <p>Generally speaking, the pool gets pretty comfortable all on its own. Guests at Oz that stay with us during the beginning or end of the season may find they\'d like the pool to be a bit warmer than its natural temperature. After 7 years of good service, the pool heater died and has been replaced.  Anyone who requests a warmer pool should enjoy the benefits of this little investment!</p>
                            
                            <li>New heat pumps and more...</li>
                            <p>When it rains, it pours - things that need fixing, that is.  The air conditioning and heating units in our home seem to last only three to five years because the salt air eats these units for breakfast!  Prior to the season start we replaced __________________________.</p>

                            <li>Spa</li>
                            <p>We have been on a mission to improve the Spa experience of our guests from the day we first opened our home to others.  For most guests, the custom spa attached to the pool &mdash; with truly room enough for six adults (or more), unlike the standard hot tub where four adults must play footsie to be in the hot tub together &mdash; is a vastly better experience.  We know, however, that there have been challenges with the heating system and maintaining the right water level.</p>
                            
                            <p>To address these concerns ___________________________________</p>
                                                        
                            <li>Elevator safety</li>
                            <p>Recently, North Carolina added some laws to protect children from getting trapped between the elevator gate and the external doors.  You probably noticed the white baffles on the lower half of the doors.  That was phase 1 of the safety improvements.  The final phase involved installing a new gate that resists horizontal pressure. In the off-season, we installed this new type of gate and our elevator is now fully compliant with these new laws.  See the Elevator page for more safety tips and information about operating the elevator.</p>
                            
                            <li>This website!</li>   
                            <p>Yep, even websites get outdated and need a fresh face.  We\'d love to know what you think of the 2023 version of the Oz website!  Leave us a comment below.</p>
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
