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
		<?php echo esc_html( get_admin_page_title(), 'amp-admanager' ); ?>
	</h1>
	<form method="post" action="">
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="dfp-network-id">DFP Network ID</label>
					</th>
					<td>
						<input name="dfp-network-id" type="text" id="dfp-network-id" value="<?php echo esc_attr( get_option( 'dfp-network-id' ) ); ?>" class="regular-text">
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="load-amp-resources">Load AMP Resources For Non AMP Site</label>
					</th>
					<td>
						<input name="load-amp-resources" type="checkbox" id="load-amp-resources" value="1" <?php checked( get_option( 'load-amp-resources' ), '1', true ); ?>>
					</td>
				</tr>
			</tbody>
		</table>
		<?php submit_button(); ?>
	</form>
</div>

