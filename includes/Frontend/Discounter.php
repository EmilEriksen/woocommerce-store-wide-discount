<?php
/**
 * The class responsible for applying the global discount and handling the views.
 */

namespace WCSWD\Frontend;

use WCSWD\Helpers\Utility;
use WCSWD\Helpers\Store;

/**
 * The class responsible for applying the global discount and handling the views.
 */
class Discounter {
    /**
     * Whether the discount is enabled.
     *
     * @var boolean
     */
    private $enabled = false;

    /**
     * Whether we should try to make prices ending with specific decimals.
     *
     * @var boolean
     */
    private $charm_pricing_enabled = false;

    /**
     * The decimals prices should end in if charm pricing is enabled.
     *
     * @var integer
     */
    private $charm_pricing_decimals = -1;

    /**
     * Whether pretty pricing is enabled.
     *
     * @var boolean
     */
    private $pretty_pricing_enabled = false;

    /**
     * The discount in %.
     *
     * @var integer
     */
    private $discount_percentage = -1;

    /**
     * The maximum discount for pretty pricing.
     *
     * @var integer
     */
    private $max_discount_percentage = -1;

    /**
     * Initialize.
     */
    public function init() {
        $this->discount_percentage = absint( get_option( 'wcswd_discount_percentage' ) ) ?: $this->discount_percentage;
        $this->enabled = get_option( 'wcswd_enable' ) === 'yes' && $this->discount_percentage > 0;

        if ( Utility::is_request( 'admin' ) || ! $this->enabled ) {
            return;
        }

        $charm_pricing_decimals = absint( get_option( 'wcswd_charm_pricing_decimals', 0 ) );
        $this->charm_pricing_enabled = Utility::prices_have_decimals() &&
            $charm_pricing_decimals > 0 &&
            $charm_pricing_decimals < ( pow( 10, \wc_get_price_decimals() ) - 1 );
        if ( $this->charm_pricing_enabled ) {
            $this->charm_pricing_decimals = $charm_pricing_decimals;
        }

        $this->max_discount_percentage = absint( get_option( 'wcswd_pretty_price_max_discount_percentage' ) ) ?: $this->max_discount_percentage;
        $this->pretty_pricing_enabled = $this->max_discount_percentage > 0;

        // Simple products.
        add_filter( 'woocommerce_product_get_price', array( $this, 'adjust_price' ), 10, 2 );
        add_filter( 'woocommerce_product_get_sale_price', array( $this, 'adjust_price' ), 10, 2 );

        /**
         * Variable products.
         *
         * @see https://github.com/woocommerce/woocommerce/blob/master/includes/class-wc-product-variable.php
         *
         * We can simply do this because WooCommerce generates a price hash based on what
         * functions are hooked into these to filters (#L247-L255). It takes care of transient
         * caching and everything. Clever!
         */
        add_filter( 'woocommerce_variation_prices_price', array( $this, 'adjust_price' ), 10, 2 );
        add_filter( 'woocommerce_variation_prices_sale_price', array( $this, 'adjust_price' ), 10, 2 );
    }

    /**
     * Add the global discount to single products.
     *
     * @param  numeric    $price   Product price.
     * @param  WC_Product $product Product object.
     *
     * @return numeric
     */
    public function adjust_price( $price, $product ) {

        // Check if product is on sale and apply discount if it isn't.
        if ( ! Store::is_product_discounted( $product ) ) {
            return $price;
        }

        $cache_key = $product->get_id() . ( $this->pretty_pricing_enabled ? 'pretty' : '' );

        $adjusted_price = \wp_cache_get( $product->get_id(), 'wcswd_price' );
        if ( false === $adjusted_price ) {
            if ( $this->pretty_pricing_enabled ) {
                $adjusted_price = Store::pretty_price( $product->get_price( 'edit' ), $this->discount_percentage, $this->max_discount_percentage );

                if ( $this->charm_pricing_enabled ) {
                    $adjusted_price -= 1 - pow( 10, -( wc_get_price_decimals() ) ) * $this->charm_pricing_decimals;

                    // If we have 0 decimals only substract 1 from numbers ending in 0. E.g. 10 becomes 9.
                } elseif ( 0 === wc_get_price_decimals() && 0 === $adjusted_price % 10 ) {
                    $adjusted_price -= 1;
                }
            } else {
                $adjusted_price = Store::get_discounted_price( $product->get_price( 'edit' ), $this->discount_percentage );
            }

            $adjusted_price = (string) $adjusted_price;

            \wp_cache_set( $product->get_id(), $adjusted_price, 'wcswd_price' );
        }

        return $adjusted_price;
    }
}
