<?php
/**
 * AMP AdManager Settings page layout.
 *
 * @author Vishal Dodiya <vishal.dodiya@rtcamp.com>
 *
 * @package AMP_AdManager
 */

?>

<div class="wrap">
	<h1>
		<?php echo esc_html( get_admin_page_title() ); ?>
	</h1>
	<form method="post" action="options.php">
		<?php
		settings_fields( 'amp-admanager-menu' );
		do_settings_sections( 'amp-admanager-menu-page' );
		submit_button();
		?>
	</form>
</div>

