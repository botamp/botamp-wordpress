<?php
$shutdown_alert = '<div id="botamp-shutdown-notice" class="notice notice-error is-dismissible"> <p>'
// translators: The placeholder parameter is the url to the wordpress dashboard
				. sprintf( __('An unexpected error has happened and the Botamp plugin has been deactivated. <br>
                              Please report this error by sending an email to support@botamp.com.<br>
                              <a href="%s">Go to the Dashboard</a>', 'botamp'), admin_url() )
				. '</p> </div>
                  <script>
                    jQuery(".wrap").prepend(jQuery("#botamp-shutdown-notice"));
                  </script>';
