<?php
/**
 * @package inc2734/wp-oembed-blog-card
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Inc2734\WP_OEmbed_Blog_Card\App\Model;

require_once( ABSPATH . 'wp-admin/includes/file.php' );

class Cache {

	/**
	 * Return using the cache object.
	 *
	 * @return string
	 * @throws \RuntimeException If the file fails to write.
	 */
	protected static function _get_cache_object() {
		$class = apply_filters( 'inc2734_wp_oembed_blog_card_cache_object', '\Inc2734\WP_OEmbed_Blog_Card\App\Model\FileCache' );
		if ( $class instanceof Cache ) {
			throw new \RuntimeException( sprintf( '[inc2734/wp-oembed-blog-card] Invalid cache object: %1$s.', $class ) );
		}
		return $class;
	}

	/**
	 * Return cache.
	 *
	 * @param string $url Target URL.
	 * @return boolean
	 */
	public static function get( $url ) {
		$object = static::_get_cache_object();
		return $object::get( $url );
	}

	/**
	 * Return true when expired.
	 *
	 * @param string $url Target URL.
	 * @param int    $expire Expire time (ms).
	 * @return boolean
	 */
	public static function expired( $url, $expire = MINUTE_IN_SECONDS ) {
		$cache = static::get( $url );
		if ( ! $cache ) {
			return false;
		}

		if ( time() < $cache['cached_time'] + $expire ) {
			return false;
		}

		return true;
	}

	/**
	 * Return true when broken.
	 *
	 * @param string $url Target URL.
	 * @return boolean
	 */
	public static function broken( $url ) {
		$cache = static::get( $url );
		if ( ! $cache ) {
			return false;
		}

		if ( ! is_null( $cache['title'] ) ) {
			return false;
		}

		return true;
	}

	/**


	 * Refresh cache.
	 *
	 * @param string $url Target URL.
	 * @return string
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

		delete_transient( static::_get_meta_key( $url ) ); // Delete old version cache.

		$object = static::_get_cache_object();
		return $object::refresh( $url, $cache );
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
		$hash = base64_encode( pack( 'H*', sha1( $url ) ) );
		return '_wpoembc_' . $hash;
	}
}
