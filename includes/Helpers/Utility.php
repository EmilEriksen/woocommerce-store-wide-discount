<?php
namespace WCSWD\Helpers;

/**
 * Utility functions used in various places.
 */
final class Utility {

    /**
	 * What type of request is this?
	 *
	 * @param string $type Ajax, frontend or admin.
	 * @return bool
	 */
    public static function is_request( string $type ) {
        switch ( $type ) {
			case 'admin' :
				return is_admin();
			case 'ajax' :
				return defined( 'DOING_AJAX' );
			case 'cron' :
				return defined( 'DOING_CRON' );
			case 'frontend' :
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
    }

	/**
	 * Checks if prices have decimals.
	 *
	 * @return bool Whether prices have decimals.
	 */
	public static function prices_have_decimals() {
		return \wc_get_price_decimals() > 0;
	}
}
