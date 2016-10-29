<?php
    global $wpdb;
    $query = sprintf( "SELECT $wpdb->posts.ID
                       FROM $wpdb->posts
                       WHERE $wpdb->posts.post_type = '%s'
                       AND $wpdb->posts.post_status = 'publish'
                       AND $wpdb->posts.ID
                       NOT IN (
                           SELECT $wpdb->postmeta.post_id
                           FROM $wpdb->postmeta
                           WHERE  $wpdb->postmeta.meta_key = 'entity_id'
                       )", $this->get_option( 'post_type' ) );
    $posts = $wpdb->get_col( $query, 0 );

?>
<h3 class="title"> <?php _e( 'Importing all existing posts into your Botamp app', 'botamp' ) ?> </h3><hr>
<p>
    <b>
        <?php printf( __( 'Total Posts to import: %s', 'botamp' ), count( $posts ) ); ?><br>
    </b>
    <b>
        <?php printf( __( 'Imported Posts: %s', 'botamp' ), '<span id="successCount">0</span>' ); ?><br>
    </b>
    <b>
        <?php printf( __( 'Failed to import: %s', 'botamp' ), '<span id="failureCount">0</span>' ); ?><br>
    </b>
</p>
<h4> <?php _e( 'Debug informations' ); ?> </h4>
<ol id="debug">

</ol>

<script type="text/javascript">
    jQuery( document ).ready( function( $ ) {

        var posts = [ <?php echo implode( ',', $posts ); ?> ];
        var successCount = 0;
        var failureCount = 0;

        function updateStatus( post_id, message, success ) {
            if( success === true )
                $("span#successCount").html( ++successCount );
            else
                $("span#failureCount").html( ++failureCount );

            $( "#debug" ).append( "<li> " + message + " </li>" )
        }

        function importPost( post_id ) {
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: { action: "botamp_import", post_id: post_id },
                success: function( response ) {
                    if ( response.success )
                        updateStatus( post_id, response.success, true );
                    else if( response.error )
                        updateStatus( post_id, response.error, false );
                },
                error: function( response ) {
                    updateStatus( post_id, response, false );
                },
                complete: function( response ) {
                    if( posts.length )
                        importPost( posts.shift() );
                }
            })
        }
        if( posts.length > 0 )
            importPost( posts.shift() );
    });
</script>
