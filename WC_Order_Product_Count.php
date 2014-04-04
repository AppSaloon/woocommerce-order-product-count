<?php
/**
 * Plugin Name: WooCommerce Order Product Count
 * Plugin URI: http://www.dkjensen.com/
 * Description: Export bulk order product count in PDF format.
 * Version: 1.0
 * Author: David Jensen
 * Author URI: http://dkjensen.com
 * License: GPL2
 */


/**
 * Includes WooCommerce functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) || ! function_exists( 'is_woocommerce_active' ) )
	require_once 'woocommerce/woo-functions.php';

/**
 * Check if WooCommerce is active, and if it isn't, disable Subscriptions.
 */
if ( ! is_woocommerce_active() ) {
	add_action( 'admin_notices', 'WC_Export_Orders::woocommerce_inactive_notice' );
	return;
}

define( 'WOO_EXPORT_ORDERS', plugin_dir_path( __FILE__ ) );

require_once 'WC_Export_PDF.class.php';

add_action( 'init', 'WC_Export_Orders::woocommerce_export_init' );

class WC_Export_Orders {

	public static function woocommerce_export_init() {
		add_action( 'admin_footer', __CLASS__ . '::woocommerce_export_orders_menu', 10 );
		add_action( 'load-edit.php', __CLASS__ . '::woocommerce_export_orders_bulk_action' );
	}

	/**
	 * Admin error notice to ensure WooCommerce is active
	 * 
	 * @return type
	 */
	public static function woocommerce_inactive_notice() {
		if ( current_user_can( 'activate_plugins' ) ) {
			$output  = '<div id="message" class="error"><p>';
			$output .= sprintf( __( 'WooCommerce Export Orders is inactive. WooCommerce must be active in order to use WooCommerce Export Orders.', 'export-orders' ) );
			$output .= '</p></div>';

			print $output;
		}
	}

	/**
	 * Add the Orders Product Count menu item to bulk actions
	 * 
	 * @return type
	 */
	public static function woocommerce_export_orders_menu() {
		global $post_type;

		if ( 'shop_order' == $post_type ) {
			?>
			<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery('<option>').val('export_orders').text('<?php _e( 'Orders Product Count', 'woocommerce-export' )?>').appendTo("select[name='action']");
				jQuery('<option>').val('export_orders').text('<?php _e( 'Orders Product Count', 'woocommerce-export' )?>').appendTo("select[name='action2']");
			});
			</script>
			<?php
		}
	}

	/**
	 * Are we exporting the order count from the bulk actions menu?
	 * 
	 * @return type
	 */
	public static function woocommerce_export_orders_bulk_action() {
		if( ! isset( $_REQUEST['action'] ) || empty( $_REQUEST['action'] ) )
			return;

		$action = $_REQUEST['action'];

		if ( $action == 'export_orders' ) {

			// If no posts selected do nothing
			if( empty( $_REQUEST['post'] ) )
				return; 

			// If posts are selected join them into string
			if( is_array( $_REQUEST['post'] ) && ! empty( $_REQUEST['post'] ) ) {
				$posts = implode( ',', $_REQUEST['post'] );
			}

			$forward = wp_nonce_url( admin_url( 'admin.php' ), 'export-orders' );
			$forward = add_query_arg( array( 'weo_export' => 'woocommerce-export-orders', 'posts' => $posts ), $forward );
			wp_redirect( $forward );
			exit();
		}

		return;
	}

	/**
	 * Retrieves the sum of items from a list of orders
	 * 
	 * @param type $orders 
	 * @return type
	 */
	public static function get_order_product_count( $orders ) {
		if( ! isset( $orders ) || empty( $orders ) || ! is_array( $orders ) )
			return 0;

		$total = array();
		foreach( $orders as $order ) {
			$items = new WC_Order( $order );
			$items = $items->get_items();

			foreach( $items as $item ) {
				// If product doesn't exist in order total
				// then create it with quantity
				if( ! array_key_exists( $item['product_id'], $total ) ) {
					$total[$item['product_id']] = (int) $item['qty'];
				} else {
					$total[$item['product_id']] += (int) $item['qty'];
				}
			}
		}

		return $total;
	}

	/**
	 * Convert product ID to Title
	 * 
	 * @param type $id 
	 * @return type
	 */
	public static function get_product_title( $id ) {
		if( ! isset( $id ) || empty( $id ) )
			return false;

		$product = get_product( $id );

		if( $product instanceof WC_Product )
			return $product->get_title();
	}
}

$exportPDF = new WC_Export_PDF;