<?php
/**
 * My Licences
 *
 * @author  Sebs Studio
 * @package WooCommerce My Licences/Templates
 * @version 1.0.0
 */

global $woocommerce, $wpdb, $post;

$current_user = wp_get_current_user();

/**
 * If the customer deactivates a product then
 * it will notify the customer that it was
 * successfully deactivated.
 */
if ( isset( $_GET['deactivate'] ) ) {
	$request = esc_url( get_site_url() . '/?wc-api=software-api&request=deactivation&email=' . urldecode( $_GET['email'] ) . '&licence_key=' . $_GET['licence_key'] . '&product_id=' . $_GET['deactivate'] . '&instance=' . $_GET['instance'] );

	$response = wp_remote_get( $request ); // Send request.

	// Check the response.
	if( is_wp_error( $response ) ) {
		$message = apply_filters( 'wc_my_licence_deactivation_error_message', __( 'Unable to deactivate at this time. If this error persist, please contact us. Thank you!', 'woocommerce-my-licences' ) );
		$type    = 'error';
	} else {
		$message = apply_filters( 'wc_my_licence_deactivation_success_message', __( 'You have deactivated', 'woocommerce-my-licences' ) . ' ' . get_the_title( $_GET['deactivate'] ) . ' ' . __( 'successfully for', 'woocommerce-my-licences' ) . ' <strong>' . $_GET['instance'] . '</strong>' );
		$type    = 'success';
	}

	wc_add_notice( $message, $type ); // Return message to customer.
}

// Fetch all licence keys.
$licence_keys = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_software_licences WHERE activation_email = %s", $current_user->user_email ) );
?>

<?php do_action( 'woocommerce_before_my_licences' ); ?>

<?php if( count( $licence_keys ) >= 1 ) { ?>
<table class="shop_table my_account_licences">
	<thead>
		<tr>
			<th class="product" colspan="2"><span class="nobr" style="text-align:center;"><?php _e( 'Product', 'woocommerce-my-licences' ); ?></span></th>
			<th class="licence-key" colspan="2"><span class="nobr" style="text-align:center;"><?php _e( 'Key', 'woocommerce-my-licences' ); ?></span></th>
			<th class="software-version" colspan="2"><span class="nobr" style="text-align:center;"><?php _e( 'Software Version', 'woocommerce-my-licences' ); ?></span></th>
			<?php if ( WOOCOMMERCE_MY_LICENCES_VARIABLES == true ) { ?><th class="licence-type" colspan="2"><span class="nobr" style="text-align:center;"><?php _e( 'Type', 'woocommerce-my-licences' ); ?></span></th><?php } ?>
			<th class="activations-remaining" colspan="2"><span class="nobr" style="text-align:center;"><?php _e( 'Activations Remaining', 'woocommerce-my-licences' ); ?></span></th>
		</tr>
	</thead>

	<tbody>
	<?php
	// List each product key found.
	foreach ( $licence_keys as $licence_key ) {
		$order = new WC_Order( $licence_key->order_id ); // Fetch the order details.

		if ( sizeof( $order->get_items() ) > 0 ) {
			foreach ( $order->get_items() as $item ) {
		?>
		<tr class="licences">
			<td class="product" colspan="2"><a href="<?php echo get_permalink( $item['product_id'] ); ?>"><?php echo get_the_title( $item['product_id'] ); ?></a></td>
			<td class="licence-key" colspan="2"><?php echo $licence_key->licence_key; ?></td>
			<td class="product-version" colspan="2"><?php echo $licence_key->software_version; ?></td>
			<?php if ( WOOCOMMERCE_MY_LICENCES_VARIABLES == true ) { ?><td class="licence-type" colspan="2">
			<?php
			if ( isset( $item['variation_id'] ) && $item['variation_id'] > 0 ) {
				$licence_type = get_post_meta( $item['variation_id'], 'attribute_pa_licence-type', true ); // Fetch the licence type ordered.

				if ( ! empty( $licence_type ) ) {
					if ( $licence_type == 'single-licence' ) { $type = __( 'Single site licence', 'woocommerce-my-licences' ); }
					else if ( $licence_type == 'up-to-five-sites' ) { $type = __( 'Up to five sites', 'woocommerce-my-licences' ); }
					else if ( $licence_type == 'unlimited-sites' ) { $type = __( 'Unlimited sites', 'woocommerce-my-licences' ); }
				} else {
					$type = '';
				} // END if licence type is not empty.

				echo '<strong>' . apply_filters( 'wc_my_licences_type', $type ) . '</strong>';
			} // END if product was variable.
			?>
			</td>
			<?php } // END if WOOCOMMERCE_MY_LICENCES_VARIABLES ?>
			<td class="activations-remaining" colspan="2"><?php echo $licence_key->activations_limit; ?></td>
		</tr>
		<?php
			} // END if order has at least 1 item or more.
		}

		// Fetch all licence activations
		$activations = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_software_activations as activations LEFT JOIN {$wpdb->prefix}woocommerce_software_licences as licences ON activations.key_id = licences.key_id WHERE order_id = {$licence_key->order_id}" );

		if ( count( $activations ) > 0 ) {
			foreach ( $activations as $activation ) {
		?>
		<tr class="activation <?php echo ( $activation->activation_active ) ?  'active' : 'not-active'; ?>">
			<td colspan="6" style="text-align:center;"><a target="_blank" href="<?php echo $activation->instance; ?>"><?php echo $activation->instance; ?></a></td>
			<td colspan="<?php if ( WOOCOMMERCE_MY_LICENCES_VARIABLES == true ) { echo '4'; }else{ echo '2'; } ?>"><?php if ( $activation->activation_active == 1 ) { ?><a href="<?php echo get_permalink( $post->ID ); ?>?deactivate=<?php echo $activation->software_product_id; ?>&amp;email=<?php echo urlencode( $activation->activation_email ); ?>&amp;instance=<?php echo urlencode( $activation->instance ); ?>&amp;licence_key=<?php echo $activation->licence_key; ?>" class="button deactivate"><?php _e( 'Deactivate', 'woocommerce-my-licences' ); ?></a><?php } ?></td>
		</tr>
		<?php
			} // END for each activation.
		} // END if total of activations is one or more.

	} // END foreach licence key.
	?>
	</tbody>
</table>
<?php }else{ ?>
	<p><?php _e( 'You have no licences.', 'woocommerce-my-licences'); ?></p>
<?php } ?>

<?php do_action( 'woocommerce_after_my_licences' ); ?>
