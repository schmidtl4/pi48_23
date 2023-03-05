<?php

    class Comments
    {

        public static function setCommentForm(): string
        {
            return '    
                <div class="row mt-3" >
                    <div class="col-12">
                        <h4 style="font-weight: bold; padding-top: 10px;">Comments</h4>
                        <p>Did we miss anything? Is anything not clear? Is there something we got right? Let us know!</p>
                    </div>
                </div>
                <div class="row">  
                    <div class="col-12">
                        <textarea class="comment"></textarea>
                    </div>
                </div>
                
                <div class="row">    
                    <div class="col-9 mb-1 text-left">
                        <input id="commentorEmail" name="commentorEmail" type="email" class="w-100" placeholder="your email (optional)">
                    </div>
                    
                    <div class="col-3 mb-1 float-right">
                        <button class=" btn-xs btn-info send-btn" type="button">Send it!</button>
                    </div>
                </div>    
                    
                <div class="row">    
                    <div class="col-12 mb-5">
                        <div class="msg alert"></div>
                    </div>
                </div>   ';
        }

        public static function recordComment(object $pc, array $inputs): string
        {
            if (empty($inputs['comment'])) {
                return 'No comment entered. Nothing received.';
            }

            $dt = new DateTime();
            $insArr = [
                'commentDate' => $dt->format('Y-m-d H:i:s'),
                'comment' => $inputs['comment'],
                'originPage' => $inputs['originPage'],
                'guestLastName' => $_SESSION['guestLastName'],
                'rentalStartDate' => $_SESSION['rentalStartDate']
            ];

            if (!empty($inputs['commentorEmail'])) {
                $insArr['commentorEmail'] = $inputs['commentorEmail'];
            }

            $pc->create_item ('comments', $insArr);

            return 'Thank you. Your comment was received!';
        }

    }
