<?php
namespace WCSWD\Admin\Settings;

use WCSWD\Helpers\Utility;

/**
 * This class manages the settings section in WooCommerce.
 */
class StoreWideDiscount extends \WC_Settings_Page {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->id    = 'wcswd';
        $this->label = __( 'Store-wide Discount', 'wcswd' );

        $this->init();
    }

    /**
     * Initialize.
     */
    public function init() {
        add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
        add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
    }

    /**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {
		$settings = array(
			array( 'title' => __( 'Store-wide Discount settings', 'wcswd' ), 'type' => 'title', 'desc' => '', 'id' => 'wcswd_settings' ),
			array(
				'title'   => __( 'Enable store-wide discount', 'wcswd' ),
				'desc'    => __( 'Enable store-wide discount', 'wcswd' ),
				'id'      => 'wcswd_enable',
				'default' => 'no',
				'type'    => 'checkbox',
			),
            array(
				'title'   => __( 'Apply to products on sale', 'wcswd' ),
				'desc'    => __( 'Apply the discount to products already on sale', 'wcswd' ),
				'id'      => 'wcswd_enable_on_sale',
				'default' => 'no',
				'type'    => 'checkbox',
			),
            array(
				'title'    => __( 'Discount % amount', 'wcswd' ),
				'desc'     => __( 'The % discount on all products', 'wcswd' ),
				'id'       => 'wcswd_discount_percentage',
				'css'      => 'width:100px;',
				'default'  => '0',
				'desc_tip' => true,
				'type'     => 'number',
				'custom_attributes' => array(
					'min'  => 0,
					'step' => 1,
                    'max'  => 100,
				),
			),
            array(
				'title'    => __( 'Pretty pricing max % amount', 'wcswd' ),
				'desc'     => __( 'Enter a value to enable pretty pricing. If you enter this a pretty price will be generated within the range.', 'wcswd' ),
				'id'       => 'wcswd_pretty_price_max_discount_percentage',
				'css'      => 'width:100px;',
				'default'  => '0',
				'desc_tip' => true,
				'type'     => 'number',
				'custom_attributes' => array(
					'min'  => 0,
					'step' => 1,
                    'max'  => 100,
				),
			),
		);

		if ( Utility::prices_have_decimals() ) {
			$settings[] = array(
				'title'   => __( 'Prices end in', 'wcswd' ),
				'desc'    => __( 'To enable enter a value all prices should end with.', 'wcswd' ),
				'id'      => 'wcswd_charm_pricing_decimals',
				'type'    => 'number',
				'custom_attributes' => array(
					'min'  => 1,
					'step' => 1,
                    'max'  => pow( 10, \wc_get_price_decimals() ) - 1,
				),
			);
		}

		$settings[] = array( 'type' => 'sectionend', 'id' => 'wcswd_settings' );

		$settings = apply_filters( 'wcswd_settings', $settings );

		return apply_filters( 'wcswd_get_settings_' . $this->id, $settings );
	}

    /**
     * Save settings.
     */
    public function save() {
        $settings = $this->get_settings();
        \WC_Admin_Settings::save_fields( $settings );
    }
}
