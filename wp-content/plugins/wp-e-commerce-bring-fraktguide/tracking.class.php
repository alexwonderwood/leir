<?php
/** 
 * Tracking Class
 */
class BFG_Tracking {
	private function __construct() {
		// Empty Constructor
	}

	private function __destruct() {
		// Empty Destructor
	}

	/** 
	 * Add tracking data to purchase log
	 */
	public function bfg_display_tracking_link( $purchase ) {
		global $wp_version;
		if( $purchase['processed'] != 4 ) {
			// Do nothing if purchase status is unlike 'job_dispatched' 
			return false;
		}
		if ( $purchase['track_id'] != null ) {
			$bhg_header_options = array(
				'timeout' => 3,
				'user-agent' => 'WordPress/' . $wp_version . '; WP e-Commerce/' . WPSC_PRESENTABLE_VERSION . '; ' . home_url( '/' )
			);
			$bfg_tracking_url_beta = "http://beta.bring.no/sporing/sporing.xml";
			$bfg_tracking_url = "http://sporing.bring.no/sporing.xml";
			$bfg_params = array( 'q' => $purchase['track_id'] );
			$bfg_tracking_url_beta = add_query_arg( $bfg_params, $bfg_tracking_url_beta );
			$bfg_results = wp_remote_get( $bfg_tracking_url_beta, $bhg_header_options );
			$bfg_body = trim( $bfg_results['body'] );
			if( $bfg_body != "No shipments found" ) {
				$bfg_xml = new SimpleXMLElement( $bfg_body );
				$bfg_xml->registerXPathNamespace( "s", "http://www.bring.no/sporing/1.0" );
				$bfg_status_description_xml = $bfg_xml->xpath( 's:Consignment/s:PackageSet/s:Package/s:StatusDescription' );
				$bfg_status_description = (string) $bfg_status_description_xml[0];
				printf(__('<strong class="form_group">Tracking ID:</strong> <a href="http://sporing.bring.no/sporing.html?q=%1$s" target="_blank">%2$s</a><br><br>', 'bfg'), $purchase['track_id'], $purchase['track_id']);
				printf(__('<strong class="form_group">Shipment Status:</strong> %1$s<br><br>', 'bfg'), $bfg_status_description);
			} else {
				_e('<strong class="form_group">Shipment Status: Shipment not found</strong><br><br>', 'bfg');
			}
		}
	}
}
?>