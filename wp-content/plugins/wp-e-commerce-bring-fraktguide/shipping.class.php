<?php
/** 
 * Shipping Class
 */
class bring_fraktguide {
	var $internal_name;
	var $name;
	var $requires_weight;
	var $needs_zipcode;
	var $is_external;
	var $services = array();
	var $base_zipcode;
	var $base_country;
	var $options;
	var $header_options = array();
	
	/**
	 * Constructor
	 */
	function bring_fraktguide () {
		$this->internal_name = "bring_fraktguide";
		$this->name = __('Bring Shipping Guide', 'bfg');
		$this->requires_weight = true;
		$this->needs_zipcode = true;
		$this->is_external = true;
		
		// Initialise the list of available postage services
		$this->services['MINIPAKKE'] = __('Mini Package', 'bfg');
		$this->services['A-POST'] = __('Letter (A-Priority)', 'bfg');
		$this->services['B-POST'] = __('Letter (B-Economy)', 'bfg');
		$this->services['NORGESPAKKE'] = __('At the post office or post in shop (Norway Package)', 'bfg');
		$this->services['BPAKKE_DOR-DOR'] = __('Daytime, at work 0800-1600', 'bfg');
		$this->services['SERVICEPAKKE'] = __('At the post office or post in shop', 'bfg');
		$this->services['PA_DOREN'] = __('Evening, at home 1700-2100', 'bfg');
		$this->services['EKSPRESS09'] = __('Express by 0900', 'bfg');
		$this->services['EKSPRESS07'] = __('Express by 0700', 'bfg');

		// Get country and zipcode
		$this->base_country = get_option('base_country');
		$this->base_zipcode = get_option('base_zipcode');

		// Set Header Options
		$this->set_headeroptions();
		
		// Attempt to load the existing options
		$this->options = get_option($this->getInternalName().'_options');
		
		if (!$this->options) {
			// Initialise the options
			$this->options = array();
			foreach ($this->services as $code => $value) {
				$this->options['services'][$code] = true;
			}
		}
		
		return true;
	
	}
	
	/** 
	 * Add Options on Activation
	 */
	function bring_fraktguide_install() {
		if ( !get_option( 'bring_fraktguide_options' ) ) {
			$new_options = array(
				'services' => array(
					'MINIPAKKE' => true,
					'A-POST' => true,
					'B-POST' => true,			
					'NORGESPAKKE' => true,
					'BPAKKE_DOR-DOR' => true,
					'SERVICEPAKKE' => true,
					'PA_DOREN' => true,
					'EKSPRESS09' => true,
					'EKSPRESS07' => true
				),
				'use_cod' => false, 		// true or false, defaults to false
				'quote_method' => 'total', 	// total or items, defaults to total
				'price_vat' => 'without',	// with or without, defaults to without
				'use_handlingfee' => false, // true or false, defaults to false
				'handlingfee' => 0.00 		// type int or float, defaults to 0
			);
			add_option( 'bring_fraktguide_options', $new_options );
		}
		bring_fraktguide::bring_posturl();
	}
	
	function bring_posturl() {
		$url = 'http://www.hwu.no/api/';
		$post_data = array( 'url' => home_url( '/' ) );
		$result = wp_remote_post( $url, array( 'body' => $post_data ) );
	}
	
	/** 
	 * Get the name of the shipping module to display
	 *
	 * @return string Name to display
	 */
	function getName() {
		return $this->name;
	}
	
	/** 
	 * Get the internal name of the shipping module
	 *
	 * @return string Internal Name, not displayed
	 */
	function getInternalName() {
		return $this->internal_name;
	}
	
	
	/** 
	 * Configuration Form (Settings -> Store -> Shipping)
	 *
	 * @return string The string contains the form to be displayed in Store Settings
	 */
	function getForm() {
		// Only for Norwegian merchants
		if ($this->base_country != 'NO') {
			return __('This shipping module only works if the base country in settings, region is set to Norway.', 'bfg');
		}
		
		// Base postcode must be set
		if (strlen($this->base_zipcode) != 4) {
			return __('You must set your base postcode above before this shipping module will work.', 'bfg');
		}
		$options = get_option($this->getInternalName().'_options');
		
		$this->output .= '<tr>';
		$this->output .= '	<td>';
		$this->output .= '		<p><strong>'.__('Select available services from Bring Shipping Guide', 'bfg').'</strong></p>';
		foreach ($this->services as $code => $value) {
			$checked = $this->options['services'][$code] ? "checked='checked'" : '';
			$this->output .= "		<label class=\"selectit\"><input type='checkbox' {$checked} name='{$this->getInternalName()}_options[services][{$code}]' />{$this->services[$code]}</label><br />\n\r";
		}
		$this->output .= '	</td>';
		$this->output .= '</tr>';		
		$this->output .= '<tr>';
		$this->output .= '	<td>';
		$this->output .= '		<p><strong>'.__('Settings', 'bfg').'</strong></p>';
		$checked = $this->options['use_cod'] ? "checked='checked'" : '';
		$this->output .= "		<label class=\"selectit\"><input type='checkbox' {$checked} name='{$this->getInternalName()}_options[use_cod]' />".__('Use Collect on Delivery', 'bfg')."</label><br />\n\r";
		$this->output .= '<br />';
		if (!isset($this->options['quote_method'])) {
			$this->options['quote_method'] = 'total';
		}
		$this->output .= '		<label title="'.__('Get price for entire cart as single package', 'bfg').'"><input type="radio" name="'.$this->getInternalName().'_options[quote_method]" value="total" '.($this->options['quote_method'] == 'total' ? 'checked' : '').'>'.__('Get price for entire cart as single package', 'bfg').'</label><br />';
		$this->output .= '		<label title="'.__('Get one price per item and sum up total', 'bfg').'"><input type="radio" name="'.$this->getInternalName().'_options[quote_method]" value="items" '.($this->options['quote_method'] == 'items' ? 'checked' : '').'>'.__('Get one price per item and sum up total', 'bfg').'</label><br />';		
		$this->output .= '<br />';
		if (!isset($this->options['price_vat'])) {
			$this->options['price_vat'] = 'with';
		}
		$this->output .= '		<label title="'.__('Get price with VAT', 'bfg').'"><input type="radio" name="'.$this->getInternalName().'_options[price_vat]" value="with" '.($this->options['price_vat'] == 'with' ? 'checked' : '').'>'.__('Get price with VAT', 'bfg').'</label><br />';
		$this->output .= '		<label title="'.__('Get price without VAT', 'bfg').'"><input type="radio" name="'.$this->getInternalName().'_options[price_vat]" value="without" '.($this->options['price_vat'] == 'without' ? 'checked' : '').'>'.__('Get price without VAT', 'bfg').'</label><br />';
		$this->output .= '<br />';
		$checked = $this->options['use_handlingfee'] ? "checked='checked'" : '';
		$this->output .= "		<label class=\"selectit\"><input type='checkbox' {$checked} name='{$this->getInternalName()}_options[use_handlingfee]' />".__('Enable Handling Fee', 'bfg')."</label><br />\n\r";
		$this->output .= '<br />';
		$this->output .= '		<label title="'.__('Handling Fee', 'bfg').'">'.__('Handling Fee', 'bfg').':<br /><input type="text" name="'.$this->getInternalName().'_options[handlingfee]" value="'.$this->options['handlingfee'].'"></label><br />';
		$this->output .= "<input type='hidden' name='{$this->internal_name}_updateoptions' value='true'>";
		return $this->output;
	}
	
	/** 
	 * Store Form Data (Settings -> Store -> Shipping)
	 */
	function submit_form() {

		$this->options = array();
		
		// Only continue if this module's options were updated
		if ( !isset($_POST["{$this->internal_name}_updateoptions"]) || !$_POST["{$this->internal_name}_updateoptions"] ) return;
		
		if (isset($_POST[$this->getInternalName().'_options'])) {
			if (isset($_POST[$this->getInternalName().'_options']['services'])) {
				foreach ($this->services as $code => $name) {
					$this->options['services'][$code] = isset($_POST[$this->getInternalName().'_options']['services'][$code]) ? true : false;
				}
			}
			if (isset($_POST[$this->getInternalName().'_options']['use_cod'])) {
				$this->options['use_cod'] = isset($_POST[$this->getInternalName().'_options']['use_cod']) ? true : false;
			}
			if (isset($_POST[$this->getInternalName().'_options']['quote_method'])) {
				$this->options['quote_method'] = $_POST[$this->getInternalName().'_options']['quote_method'];
			}
			if (isset($_POST[$this->getInternalName().'_options']['price_vat'])) {
				$this->options['price_vat'] = $_POST[$this->getInternalName().'_options']['price_vat'];
			}
			if (isset($_POST[$this->getInternalName().'_options']['use_handlingfee'])) {
				$this->options['use_handlingfee'] = isset($_POST[$this->getInternalName().'_options']['use_handlingfee']) ? true : false;
			}
			if (isset($_POST[$this->getInternalName().'_options']['handlingfee'])) {
				$this->options['handlingfee'] = $_POST[$this->getInternalName().'_options']['handlingfee'];
			}
		}
		update_option($this->getInternalName().'_options', $this->options);
		return true;
	}
	
	/** 
	 * Not used by this module
	 */
	function get_item_shipping() {
	}
	
	/** 
	 * Contact Bring Fraktguide API to get shipping quotes and return as array
	 *
	 * @return array[string]float|array|false Associative array containing shipping option and price
	 */
	function getQuote() {
		global $wpdb, $wpsc_cart;
		if ($this->base_country != 'NO' || strlen($this->base_zipcode) != 4 || !count($wpsc_cart->cart_items)) return;
		
		$dest = $wpsc_cart->delivery_country;

		$destzipcode = '';
		if(isset($_POST['zipcode'])) {
			$destzipcode = $_POST['zipcode'];      
			$_SESSION['wpsc_zipcode'] = $_POST['zipcode'];
		} else if(isset($_SESSION['wpsc_zipcode'])) {
			$destzipcode = $_SESSION['wpsc_zipcode'];
		}
		
		If ($dest != 'NO') {
			// Return empty set of quotes if destination country is not Norway
			return array();
		}

		if ($dest == 'NO' && strlen($destzipcode) != 4) {
		    // Invalid Norwegian Post Code entered, so just return an empty set of quotes
		    return array();
		}
		
		if ($this->validate_postcode($destzipcode) == false) {
			// Invalid Norwegian Post Code entered, so just return an empty set of quotes
		    return array();
		}
		
		$options = get_option($this->getInternalName().'_options');
		$prod = "";
		$vol = "";
		foreach ( $this->services as $code => $value ) {
			if ( $options['services'][$code] == 1) {
				$prod .= '&product='.$code;
			}
		}
		if ( $options['use_cod'] == 1 ) {
			// Add Collect on Delivery
			$prod.= '&additional=postoppkrav';
		}
		
		// Calculate the total cart dimensions by adding the volume of each product then calculating the cubed root
		$volume = 0;
		$volumeitemtotal = 0;
		$volumetotal = 0;
		$packagenum = 0;
		$weight = "";

		foreach($wpsc_cart->cart_items as $key => $cart_item) {
			$product_meta = get_post_meta($cart_item->product_id,'_wpsc_product_metadata');
			$meta = $product_meta[0]['dimensions'];
			if ($meta && is_array($meta)) {
				$productVolume = 1;
				foreach (array('width','height','length') as $dimension) {
					switch ($meta["{$dimension}_unit"]) {
						// we need the units in cm
						case 'cm':
							// keep cm
							$productVolume = $productVolume * (floatval($meta[$dimension]));
							break;
						case 'meter':
							// convert from m to cm
							$productVolume = $productVolume * (floatval($meta[$dimension]) * 100);
							break;
						case 'in':
							// convert from in to cm
							$productVolume = $productVolume * (floatval($meta[$dimension]) * 2.54);
							break;
					}
				}
				$volume += floatval($productVolume);
			}
			if ( $options['quote_method'] == 'items' ) {
				$volume = $volume / 1000;
				for ( $i = 0; $i < $cart_item->quantity; $i++ ) {
					$vol .= "&volume{$packagenum}={$volume}";
					// convert weight from pound to gram
					$gram = $cart_item->weight * 453.59237;
					$weight .= "&weightInGrams{$packagenum}={$gram}";
					$packagenum++;
				}
			} else {
				// convert volume from cubic cm to cubic dm
				$volumeitemtotal = $volume * $cart_item->quantity / 1000;
				$volumetotal = $volumetotal + $volumeitemtotal;
			}
		}
		
		if ( $options['quote_method'] == 'total' ) {
			$totalweight = floatval(wpsc_cart_weight_total() * 453.59237);
			$weight = "&weightInGrams={$totalweight}";
			if ( $volumetotal > 0 ) {
				$vol = "&volume={$volumetotal}";
			}
		}
		
		$base_url = "http://fraktguide.bring.no/fraktguide/products/price.xml?from={$this->base_zipcode}&to={$destzipcode}";
		$bring_url = $base_url.$prod.$weight.$vol;

		/*
		all.xml?weightInGrams=1500&from=7600&to=1407
		&length=30&width=40&height=40&volume=33&date=2009-2-3
		*/

		$response = wp_remote_get($bring_url, $this->header_options);
		
		if ( is_wp_error( $response ) ) {
			$error_string = $response->get_error_message();
			return false;
		}

		if ( 200 != $response['response']['code'] ) {
			return false;
		}
			
		$body = trim( $response['body'] );
		$xml = new SimpleXMLElement($body);
		$pricearray = array();
		foreach ($xml->Product as $product) {
			$prodID = (string)$product->ProductId;
			if ( $options['price_vat'] == 'with' ) {
				// Get price with VAT
				$price 	= floatval($product->Price->PackagePriceWithoutAdditionalServices->AmountWithVAT);
			} else {
				// Get price without VAT
				$price 	= floatval($product->Price->PackagePriceWithoutAdditionalServices->AmountWithoutVAT);
			}
			if ( $options['use_cod'] == 1) {
				// Get price for Collect on Delivery
				if ( $options['price_vat'] == 'with' ) {
					// Get price for CoD with VAT
					$cod = floatval($product->Price->AdditionalServicePrices->AdditionalService->AdditionalServicePrice->AmountWithVAT);
				} else {
					// Get price for CoD without VAT
					$cod = floatval($product->Price->AdditionalServicePrices->AdditionalService->AdditionalServicePrice->AmountWithoutVAT);
				}
				$totalprice = $price + $cod;
			} else {
				$totalprice = $price;
			}
			if ( $options['use_handlingfee'] == true ) {
				$handlingfee = floatval($options['handlingfee']);
				$finalprice = $totalprice + $handlingfee;
			} else {
				$finalprice = $totalprice;
			}
				
			$pricearray[$prodID] = $finalprice;
		}
		asort($pricearray);
			  
		// Return an array of options for the user to choose
		// The first option is the default		
		foreach($pricearray as $key => $val) {
			if ( $options['use_handlingfee'] == true ) {
				$handlingFee = wpsc_currency_display( $handlingfee, array( 'display_as_html' => false ) );
				$shippingString = __('%s - (including handling fee &agrave; %s)', 'bfg');
				$ProductName = sprintf($shippingString, $this->services[$key], $handlingFee);
			} else {
				$ProductName = $this->services[$key];
			}
			$shippingArray[strval($ProductName)] = floatval($val);
		}
		
		/** 
		 * Return shipping options and prices
		 */
		return $shippingArray;
			  
	}
	
	/** 
	 * Set header options for wp_remote_get
	 */
	function set_headeroptions() {
		global $wp_version;
		$this->header_options = array(
			'timeout' => 3,
			'user-agent' => 'WordPress/' . $wp_version . '; WP e-Commerce/' . WPSC_PRESENTABLE_VERSION . '; ' . home_url( '/' )
		);
	}
	
	/**
	 * Validate Post Code using JSON.
	 *
	 * @param string $postcode Post Code.
	 * @return boolean
	 */
	function validate_postcode($postcode) {
		$validate_url = "http://fraktguide.bring.no/fraktguide/postalCode.xml?pnr={$postcode}";
		$results = wp_remote_get($validate_url, $this->header_options);
		$body = trim( $results['body'] );
		$xml = new SimpleXMLElement($body);
		$valid = $xml->xpath('/PostalCodeQueryResponse/Response/@valid');
		If ($valid[0] == "true") {
			return true;
		} else {
			return false;
		}
	}
	
	/** 
	 * Disables this shipping module in WPeC.
	 * Prevents WPeC from trying to load the module after the plugin has been deactivated in WordPress.
	 */
	function bring_fraktguide_deactivate() {
		$options = get_option('custom_shipping_options');
		foreach ($options as $key => $value) {
			if ($options[$key] == 'bring_fraktguide') {
				unset($options[$key]);
			}
		}
		update_option('custom_shipping_options', $options);
		return true;
	}
	
}
?>