<?php
function get_post_type_name( $post_type ) {
	return $post_type->name;
}
$all_post_types = array_map( get_post_type_name, get_post_types( '', 'objects' ) );

function is_synced( $post_type_name ) {
	return get_option( "botamp_{$post_type_name}_sync" ) === 'enabled';
}
$all_synced_post_types = join( "','", array_filter( $all_post_types, 'is_synced' ) );

global $wpdb;
// @codingStandardsIgnoreStart
$posts = $wpdb->get_col(
    "SELECT $wpdb->posts.ID
    FROM $wpdb->posts
    WHERE $wpdb->posts.post_type in ('$all_synced_post_types')
    AND $wpdb->posts.post_status = 'publish'
    AND $wpdb->posts.ID
    NOT IN (
        SELECT $wpdb->postmeta.post_id
        FROM $wpdb->postmeta
        WHERE  $wpdb->postmeta.meta_key = 'botamp_entity_id'
    )");
// @codingStandardsIgnoreEnd
?>
<h3 class="title"> <?php _e( 'Importing all existing posts into your Botamp app', 'botamp' ) ?> </h3><hr>
<p>
	<b>
		<?php
		// translators: The placeholder parameter is the number of posts to import
		printf( __( 'Total Posts to import: %s', 'botamp' ), count( $posts ) );
		?><br>
	</b>
	<b>
		<?php
		// translators: The placeholder parameter is the number of successfully imported posts
		printf( __( 'Imported Posts: %s', 'botamp' ), '<span id="successCount">0</span>' );
		?><br>
	</b>
	<b>
		<?php
		// translators: The placeholder parameter is number of posts failed to import
		printf( __( 'Failed to import: %s', 'botamp' ), '<span id="failureCount">0</span>' );
		?><br>
	</b>
</p>
<h4> <?php _e( 'Debug informations' ); ?> </h4>
<ol id="debug">

</ol>

<script type="text/javascript">
	jQuery( document ).ready( function( $ ) {

		var posts = [ <?php echo implode( ',', $posts ); ?> ];
		if(posts.length === 0)
			return;

		var successCount = 0;
		var failureCount = 0;
		var stop = false;

		function updateStatus( post_id, message, success ) {
			if( success === true )
				$("span#successCount").html( ++successCount );
			else
				$("span#failureCount").html( ++failureCount );

			$( "#debug" ).append( "<li> " + message + " </li>" )
		}

		function importPost( post_id ) {
			if( stop === true )
				return;
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
					importPost( posts.shift() );
				}
			})
			if( !posts.length )
				stop = true;
		}

		importPost( posts.shift() );
	});
</script>
