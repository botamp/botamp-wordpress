<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       support@botamp.com
 * @since      1.3.2
 *
 * @package    Botamp
 * @subpackage Botamp/admin/partials
 */

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
	<?php
	if ( isset( $_POST['action'] ) && 'import_all_posts' === $_POST['action'] ) :
		$this->import_all_posts();
		else :
	?>
	<h2> <?php echo esc_html( get_admin_page_title() ); ?> </h2>

	<form action="options.php" method="post">

	<?php
		settings_fields( 'botamp_general_group' );
		do_settings_sections( 'botamp_general_section' );

		submit_button();
	?>
	</form>
	<?php
	if ( $this->get_auth_status() == 'ok' ) :
	?>
	<form action="options.php" method="post">

	<?php
	$post_type = isset( $_GET['post-type'] ) ? $_GET['post-type'] : 'post';

	settings_fields( "botamp_{$post_type}_group" );
	do_settings_sections( "botamp_{$post_type}_entity_section" );

	submit_button();
	?>
	</form>
	<?php
		endif;
	?>


	<form action="" method="post">
		<input type="hidden" name="action" value="import_all_posts">
		<?php submit_button( __( 'Import all posts' ) ); ?>
	</form>
	<?php
		endif;
	?>
</div>
