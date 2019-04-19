<?php
/**
 * @package inc2734/wp-oembed-blog-card
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Inc2734\WP_OEmbed_Blog_Card\App\Model;

class Cache {

	/**
	 * Return cache
	 *
	 * @param string $url
	 * @return array
	 */
	public static function get( $url ) {
		$cache = get_transient( static::_get_meta_key( $url ) );
		$cache = ! $cache || ! is_array( $cache ) ? [] : $cache;
		return $cache;
	}

	/**
	 * Refresh cache
	 *
	 * @param string $url
	 * @return void
	 */
	public static function refresh( $url ) {
		$parser = new Parser( $url );

		$cache = [
			'permalink'   => $parser->get_permalink(),
			'thumbnail'   => $parser->get_thumbnail(),
			'title'       => $parser->get_title(),
			'description' => $parser->get_description(),
			'favicon'     => $parser->get_favicon(),
			'domain'      => $parser->get_domain(),
			'cached_time' => time(),
		];

		$expiration = empty( $cache['title'] ) ? HOUR_IN_SECONDS : YEAR_IN_SECONDS;

		set_transient( static::_get_meta_key( $url ), $cache, $expiration );
	}

	/**
	 * Get post meta key for blog card
	 *
	 * @see https://qiita.com/koriym/items/efc1c419e4b7772b65c0
	 *
	 * @param string $url
	 * @return string
	 */
	protected static function _get_meta_key( $url ) {
		$hash = base64_encode( pack( 'H*', sha1( $url ) ) );
		return '_wpoembc_' . $hash;
	}
}
