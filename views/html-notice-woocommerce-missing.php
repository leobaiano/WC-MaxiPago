<?php
/**
 * WC_MaxiPago - missing dependencies notice.
 *
 * @author   Leo Baiano
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly.

$is_installed = false;

if ( function_exists( 'get_plugins' ) ) {
	$all_plugins  = get_plugins();
	$is_installed = ! empty( $all_plugins['woocommerce/woocommerce.php'] );
}

?>

<div class="error">
	<p><strong><?php esc_html_e( 'WC MaxiPago', 'wc-maxipago' ); ?></strong> <?php esc_html_e( 'depends on the last version of WooCommerce to work!', 'wc-maxipago' ); ?></p>

	<?php
		if ( $is_installed && current_user_can( 'install_plugins' ) ) {
	?>
			<p><a href="<?php echo esc_url( wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=woocommerce/woocommerce.php&plugin_status=active' ), 'activate-plugin_woocommerce/woocommerce.php' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Active WooCommerce', 'wc-maxipago' ); ?></a></p>
	<?php
		} else {
			if ( current_user_can( 'install_plugins' ) ) {
				$url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=woocommerce' ), 'install-plugin_woocommerce' );
			} else {
				$url = 'https://wordpress.org/plugins/woocommerce/';
			}
	?>
		<p><a href="<?php echo esc_url( $url ); ?>" class="button button-primary"><?php esc_html_e( 'Install WooCommerce', 'wc-maxipago' ); ?></a></p>
	<?php } ?>
</div>
