<?php

class WC_Export_TCPDF extends TCPDF {

    //Page header
    public function Header() {
        // Logo
        $logo = WOO_EXPORT_ORDERS . 'images/woocommerce-logo.jpg';
        $this->Image( $logo, '', '', '200', '', 'JPG', '', 'T', true, 300, '', false, false, 0, false, false, false );
        // Set font
        $this->SetFont('helvetica', 'B', 20);
        // Title
        $this->Cell( '', 				// Width
        			 '50', 			   // Height
        			 $this->title,    // Text
        			 0, 			 // Border
        			 1, 			// Line
        			 'R', 		   // Align
        			 false, 	  // Fill
        			 '', 		 // Link
        			 0, 		// Stretch
        			 false,    // Ignore min height
        			 'C',     // Calign
        			 'B'     // Valign
        			); 
    }

}