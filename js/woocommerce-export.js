(function($) {

	function woocommerce_export_orders_more_options() {
		var $options = $('#woocommerce-export-orders-form #woocommerce-export-more-options');

		$options.toggle({
			'slide': 'swing'
		});
	}

	/**
	 *  On document ready
	 */
	$(function() {
		// Export button
		var $more_options_btn = $('#woocommerce-export-orders-form #more-options-button');

		// Event handler for export button
		$more_options_btn.on( 'click', woocommerce_export_orders_more_options );
	});
	
})(jQuery);