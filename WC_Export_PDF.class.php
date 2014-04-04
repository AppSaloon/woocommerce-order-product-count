<?php

add_action( 'init', array( 'WC_Export_PDF', 'export_PDF' ) );

class WC_Export_PDF {

	//Location of the PDF stylesheet
	const WOOCOMMERCE_EXPORT_PDF_STYLESHEET = 'css/woocommerce-pdf.css';
	
	/**
	 * Template to get each PDF started
	 * 
	 * @return type
	 */
	public function __template() {
		require_once 'lib/tcpdf/config/tcpdf_config.php';
		require_once 'lib/tcpdf/tcpdf.php';
		require_once 'lib/tcpdf/woo_export_tcpdf.php';

		$pdf = new WC_Export_TCPDF( PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false );

		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('Nicola Asuni');
		$pdf->SetTitle( $this->get_PDF_Title() );
		$pdf->SetSubject('TCPDF Tutorial');
		$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

		// set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

		// set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		$pdf->AddPage();

		return $pdf;
	}

	/**
	 * Retrieve the stylesheet used for PDFs
	 */
	public function get_PDF_Stylesheet() {
		return sprintf( '<style>%s</style>', file_get_contents( WOO_EXPORT_ORDERS . WC_Export_PDF::WOOCOMMERCE_EXPORT_PDF_STYLESHEET ) );
	}

	/**
	 * Retrieve the export mode
	 */
	public function get_PDF_Export_Mode() {
		if( ! isset( $_REQUEST['weo_export_mode'] ) || empty( $_REQUEST['weo_export_mode'] ) )
			return 'all_orders'; // default

		return sanitize_key( $_REQUEST['weo_export_mode'] );
	}

	public function get_PDF_Title() {
		if( ! isset( $_REQUEST['weo_export_title'] ) || empty( $_REQUEST['weo_export_title'] ) )
			return __( 'Order Export', 'export-orders' );

		return __( $_REQUEST['weo_export_title'], 'export-orders' );
	}

	/**
	 * The actual PDF generator
	 * 
	 * @param type $type 
	 * @return type
	 */
	public static function export_PDF() {
		// Make sure we're exporting
		if( ! isset( $_REQUEST['weo_export'] ) || 
			! current_user_can( 'edit_shop_orders' ) ) return;

		// Verify the nonce
		//check_admin_referer( 'woo_export_orders' );

		global $exportPDF;

		$item_count 	= ! empty( $_REQUEST['weo_item_count'] ) ? (int) $_REQUEST['weo_item_count'] : null;
		$orders 		= ! empty( $_REQUEST['posts'] ) ? explode( ',', $_REQUEST['posts'] ) : null;

		// Get the PDF started
		$pdf = $exportPDF->__template();

		$totals = WC_Export_Orders::get_order_product_count( $orders );

		// Check if there are any order products
		if( $totals < 1 )
			wp_die( __( 'Empty order product count.', 'export-orders' ) );

		// Start the content
		$output  = $exportPDF->get_PDF_Stylesheet();
		$output .= "<table>
						<tr>
							<td class=\"table-head product-id\">" . __( 'ID', 'export-orders' ) . "</td>
							<td class=\"table-head product-name\">" . __( 'Product', 'export-orders' ) . "</td>
							<td class=\"table-head product-quantity\">" . __( 'Quantity', 'export-orders' ). "</td>
						</tr>";

		$i = 0;
		foreach( $totals as $product => $quantity ) { $i++;
			// Default behavior is no fill
			$fill = null;

			// Alternating row highlighting					
			if( $i % 2 === 0 ) {
				$fill = 'fill';
				$i = 0; // Reset
			}

			$output .= '<tr>';
			$output .= '<td class="table-cell product-id ' . $fill . '">' . $product . '</td>';
			$output .= '<td class="table-cell product-name ' . $fill . '">' . WC_Export_Orders::get_product_title( $product ) . '</td>';
			$output .= '<td class="table-cell product-quantity ' . $fill . '">' . $quantity . '</td>';
			$output .= '</tr>';
		}

		$output .= "</table>";

		$pdf->writeHTMLCell( 0, 0, '', '', $output, 0, 1 );

		$pdf->Output( 'woocommerce-export-order-count-' . time() . '.pdf', 'D' );

		exit;
	}

}