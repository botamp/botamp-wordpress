<?php
	$messenger_script = "<script>
            window.fbAsyncInit = function() {
                FB.init({
                  appId      : '%1\$s',
                  xfbml      : true,
                  version    : 'v2.6'
                });
            };

            (function(d, s, id){
                var js, fjs = d.getElementsByTagName(s)[0];
                  if (d.getElementById(id)) {return;}
                  js = d.createElement(s); js.id = id;
                  js.src = '//connect.facebook.net/en_US/sdk.js';
                  fjs.parentNode.insertBefore(js, fjs);
              }(document, 'script', 'facebook-jssdk')
            );
        </script>
        <div class='fb-send-to-messenger'
          messenger_app_id='%1\$s'
          page_id='%2\$s'
          data-ref='%3\$s'
          color='blue'
          size='standard'>
        </div></div>";

