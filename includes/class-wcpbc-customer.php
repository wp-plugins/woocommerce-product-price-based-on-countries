<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WCPBC_Customer' ) ) :

/**
 * WCPBC_Customer
 *
 * Customer Handler
 *
 * @class 		WCPBC_Customer
 * @version		1.2.3
 * @category	Class
 * @author 		oscargare
 */
class WCPBC_Customer {

	/** Stores customer price based on country data as an array */
	protected $_data;

	/** Stores bool when data is changed */
	private $_changed = false;

	/**
	 * Constructor for the wcpbc_customer class loads the data.
	 *
	 * @access public
	 */

	public function __construct() {		

		$this->_data = WC()->session->get( 'wcpbc_customer' );	

		//print_r( WC()->customer->country );

		if ( empty( $this->_data ) || ! in_array( WC()->customer->country, $this->countries ) || ( $this->timestamp < get_option( 'wc_price_based_country_timestamp' ) ) ) {

			$this->set_country( WC()->customer->country );
		}

		// When leaving or ending page load, store data
		add_action( 'shutdown', array( $this, 'save_data' ), 10 );	
	}

	/**
	 * save_data function.
	 *
	 * @access public
	 */
	public function save_data() {
		
		if ( $this->_changed ) {
			WC()->session->set( 'wcpbc_customer', $this->_data );				
		}	

	}

	/**
	 * __get function.
	 *
	 * @access public
	 * @param string $property
	 * @return string
	 */
	public function __get( $property ) {

		$value = isset( $this->_data[ $property ] ) ? $this->_data[ $property ] : '';

		if ( $property === 'countries' && ! $value) {
			$value = array();			
		}

		return $value;
	}


	/**
	 * Sets wcpbc data form country.
	 *
	 * @access public
	 * @param mixed $country
	 */
	public function set_country( $country ) {

		$this->_data = array();	
				
		foreach ( WCPBC()->get_regions() as $key => $group_data ) {				

			foreach ( $group_data['countries'] as $country_code ) {
		
				if ( $country === $country_code ) {

					$this->_data = array_merge( $group_data, array( 'group_key' => $key, 'timestamp' => time() ) );
										
					break 2;
				}
			}
		}
		
		$this->_changed = true;
	}

}

endif;

?>