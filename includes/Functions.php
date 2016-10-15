<?php

/**
 * Checks if a product is discounted.
 *
 * @param  int|WC_product $product Product ID or object.
 *
 * @return boolean
 */
function wcswd_is_product_discounted( $product ) {
    return \WCSWD\Helpers\Store::is_product_discounted( $product );
}
