<?php
/**
 * @package inc2734/wp-oembed-blog-card
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Inc2734\WP_OEmbed_Blog_Card\App\Model;

use Inc2734\WP_OEmbed_Blog_Card\App\Contract\Cache;

class TransientCache implements Cache {

	/**
	 * Return cache.
	 *
	 * @param string $url Target URL.
	 * @return boolean
	 */
	public static function get( $url ) {
		$key   = static::_get_meta_key( $url );
		$cache = get_transient( $key );

		return $cache
			? json_decode( $cache, true )
			: false;
	}

	/**
	 * Refresh cache.
	 *
	 * @param string $url Target URL.
	 * @param array  $cache Array of cache content.
	 * @return string
	 */
	public static function refresh( $url, $cache ) {
		$key     = static::_get_meta_key( $url );
		$content = wp_json_encode( $cache );
		$success = set_transient( $key, $content, 365 * DAY_IN_SECONDS );
		return $success ? $content : false;
	}

	/**
	 * Get post meta key for blog card.
	 *
	 * @see https://qiita.com/koriym/items/efc1c419e4b7772b65c0
	 *
	 * @param string $url Target URL.
	 * @return string
	 */
	protected static function _get_meta_key( $url ) {
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$hash = base64_encode( pack( 'H*', sha1( $url ) ) );
		// phpcs:enable
		return '_wpoembc2_' . $hash;
	}
}
