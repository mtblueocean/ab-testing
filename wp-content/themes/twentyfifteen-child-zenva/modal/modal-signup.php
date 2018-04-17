<div id="zva-modal-area">
    <div id="zva-modal-overlay">
    </div>
    <div id="zva-modal">
        <h2>PROGRAMMING TUTORIALS AND COURSES</h2>
        <!-- Begin MailChimp Signup Form -->
        <link href="//cdn-images.mailchimp.com/embedcode/slim-10_7.css" rel="stylesheet" type="text/css">
        <style type="text/css">
                /* Add your own MailChimp form style overrides in your site stylesheet or in this style block.
                   We recommend moving this block and the preceding CSS link to the HEAD of your HTML file. */
        </style>
        <div id="embed_signup">
            <img src="<?php echo esc_url( wp_get_attachment_url(get_option('zva_signup_modal_id')) ); ?>" alt="Tutorials on game, web and mobile app development" />
            <form action="//zenva.us5.list-manage.com/subscribe/post?u=f41c9e0b7b18c78350ab6041b&amp;id=1b72637e18" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
                <div id="mc_embed_signup_scroll">
                    <div style="margin-top:20px;margin-bottom:20px;">
<!--                        <label for="mce-EMAIL">Send me a list of free and premium resources on <strong>game</strong>, <strong>web</strong>, <strong>iOS</strong> and <strong>Android</strong> development.</label>-->
                        <label for="mce-EMAIL">Send me the latest programming tutorials.</label>
                    </div>
                    <div style="margin-top:20px;margin-bottom:20px;">
                        <input type="email" value="" name="EMAIL" class="email" id="mce-EMAIL" placeholder="email address" required>
                        <input type="hidden" value="signup-modal <?php echo $_SERVER['HTTP_HOST'].' - '.(isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" name="MMERGE4"  id="mce-MMERGE4" >
                    </div>
                    <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
                    <div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_f41c9e0b7b18c78350ab6041b_1b72637e18" tabindex="-1" value=""></div>
                    <div class="clear">
                        <input style="background:#009D84;float:left;" type="submit" value="Send me free tutorials" name="subscribe" id="mc-embedded-subscribe" class="button">
                        <button type="button" id="zva-modal-signup-close">Not now</button>
                    </div>
                </div>
            </form>
        </div>

        <!--End mc_embed_signup-->
    </div>
</div>
<script>
    window.addEventListener("load",function() {
        setTimeout(function(){
            
            function getUrlParameter(name) {
                name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
                var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
                var results = regex.exec(location.search);
                return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
            };
                    
            if(document.body.clientWidth > 780 && getUrlParameter('utm_medium') != 'email') {
                document.getElementById('zva-modal-area').style.top = '0px';
                document.getElementById('zva-modal-area').style.height = document.body.clientHeight + 'px';
                document.getElementById('zva-modal-area').style.display = 'block';
                document.getElementById('zva-modal').style.top = ( window.pageYOffset + 110 ) + 'px';

                document.getElementById('zva-modal-overlay').addEventListener('click', function(e){
                    document.getElementById('zva-modal-area').style.display = 'none';
                });
                document.getElementById('zva-modal-signup-close').addEventListener('click', function(e){
                    document.getElementById('zva-modal-area').style.display = 'none';
                });
                
                ga('send', 'event', 'Signup modal', 'prompt');
                
                document.getElementById('mc-embedded-subscribe').addEventListener('click', function(e){
                    ga('send', 'event', 'Signup modal', 'subscribed');
                });
            }  
        }, 60 * 1000);
    });
</script>
