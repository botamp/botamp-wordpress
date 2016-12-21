<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       support@botamp.com
 * @since      1.0.0
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
		settings_fields( $this->plugin_name );
		do_settings_sections( $this->plugin_name );
		submit_button();
	?>
	</form>

	<form action="" method="post">
		<input type="hidden" name="action" value="import_all_posts">
		<?php submit_button( __( 'Import all posts' ) ); ?>
	</form>
	<?php endif; ?>
</div>
