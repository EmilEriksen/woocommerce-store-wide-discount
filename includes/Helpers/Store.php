<?php
namespace WCSWD\Helpers;

/**
 * Helper functions for store.
 */
final class Store {
    /**
     * Return a pretty price within a discount range.
     *
     * Ways I've tried to be smart and failed:
     * Truncate $discount_min (divide by $nearest_10_exp and typecast as int).
     * Calculating y for ($discount_min % 10^y) = 0.
     *
     * A pretty price is a price consisting of two arbitrary numbers followed by
     * n identical numbers (typically 0 or 9). E.g. 12000 or 11999. It might
     * also be a multiple of 5 * 10^i followed by n identical numbers. E.g. 125000
     * or 12599.
     *
     * This function takes a $price to make pretty, $discount_min and $discount_max
     * which is a reasonable discount range the pretty price should not fall out
     * of. As a fallback $price is floored (which might cause pretty price to fall)
     * out of the discount range for very small prices.
     *
     * @param  numeric $price       	The price to make pretty.
     * @param  numeric $discount_min    The minimum discount percentage.
     * @param  numeric $discount_max 	The maximum discount percentage.
     * @return numeric               	The pretty price.
     */
    public static function pretty_price( $price, $discount_min, $discount_max ) {
    	// Calculate the discounted price from the min discount. This is the highest price in our range.
        $discounted_price = self::get_discounted_price( $price, $discount_min );
    	// Find the length of the price (before the fraction).
        $discounted_price_length = ceil( log10( $discounted_price ) );
        $pretty_price = $discounted_price;

    	// Prices < 100 should just be floored.
        if ( $discounted_price_length > 2 ) {
    		// Calculate the discounted price from the max discount. This is the lowest price in our range.
            $max_discounted_price = self::get_discounted_price( $price, $discount_max );
    		// First we try to round the part of the price after the first two digits to 0s.
            $nearest_10_exp = pow( 10, $discounted_price_length - 2 );

    		/*
    		 * If we have the no. 12645 we try to round to 12000 first. If that falls
    		 * out of range we try 12600 and so on.
    		 */
            while ( $nearest_10_exp >= 10 ) {
                $_pretty_price = self::floor_to_nearest_multiple_of( $discounted_price, $nearest_10_exp );

                $nearest_10_exp = $nearest_10_exp / 10;

    			// If a valid price is found stop.
                if ( $_pretty_price >= $max_discounted_price ) {
                    $pretty_price = $_pretty_price;
                    break;
                } else {
                    $nearest_5_exp = $nearest_10_exp * 5;

    				/*
    				 * Before we try the next 10 exp see if the nearest multiple of 5 exp
    				 * falls within range. E.g. 12000 was out of range try 12500.
    				 */
    				$_pretty_price = self::floor_to_nearest_multiple_of( $discounted_price, $nearest_5_exp );

    				if ( $_pretty_price >= $max_discounted_price ) {
    					$pretty_price = $_pretty_price;
    					break;
    				}
                }
            }
        }

        return floor( $pretty_price );
    }

    /**
     * Round a number down to nearest multiple of another number.
     *
     * @param  numeric $num_to_round Number to round.
     * @param  numeric $multiple     Number the rounded number should be a multiple of.
     * @return numeric               Rounded number.
     */
    public static function floor_to_nearest_multiple_of( $num_to_round, $multiple ) {
        if ( 0 === $multiple ) {
            return $num_to_round;
        }

        $remainder = $num_to_round % $multiple;

        if ( 0 === $remainder ) {
            return $num_to_round;
        }

        return $num_to_round - $remainder;
    }

    /**
     * Calculate a discounted price.
     *
     * @param  numeric $price      The price to apply discount to.
     * @param  numeric $percentage Discount %.
     * @return numeric             Discounted price.
     */
    public static function get_discounted_price( $price, $percentage ) {
        return $price * ( 100 - $percentage ) / 100;
    }

    /**
     * Check if a product is discounted.
     *
     * @param  int|WC_Product $product Product ID or object.
     *
     * @return boolean
     */
    public static function is_product_discounted( $product ) {
        if ( get_option( 'wcswd_enable' ) !== 'yes' || absint( get_option( 'wcswd_discount_percentage' ) ) <= 0 ) {
            return false;
        }

        if ( is_numeric( $product ) ) {
            $product = \wc_get_product( $product );
        }

        if ( ! $product ) {
            return false;
        }

        // If discount is enabled even when the product is on sale the product
        // will always be discounted.
        $enabled_on_sale = get_option( 'wcswd_enable_on_sale' ) === 'yes';
        if ( $enabled_on_sale ) {
            return true;
        }

        // Since we filter the price in get_*_price(), we have to check if the
        // product is on sale using the "raw" (edit) values.
        if ( $product->is_on_sale( 'edit' ) ) {
            return false;
        }

        return true;
    }
}
