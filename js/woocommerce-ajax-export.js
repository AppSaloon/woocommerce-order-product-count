(function($) {

	function woocommerce_export_orders_event(e) {
		//e.preventDefault();

		$.ajax({
			url: ajaxurl,
			data: {
				action: 'woocommerce_export_orders_submit'
			},
			success: function(data) {
				console.log( data );
			}
		});
	}

	/**
	 *  On document ready
	 */
	$(function() {
		// Export button
		var $export_btn = $('#woocommerce-export-orders-form #woocommerce-export-orders-submit');

		// Event handler for export button
		$export_btn.on( 'click', woocommerce_export_orders_event );
	});
	
})(jQuery);