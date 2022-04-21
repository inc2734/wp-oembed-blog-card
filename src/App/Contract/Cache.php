<?php
/**
 * @package inc2734/wp-oembed-blog-card
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Inc2734\WP_OEmbed_Blog_Card\App\Contract;

interface Cache {
	/**
	 * Return cache.
	 *
	 * @param string $url Target URL.
	 * @return boolean
	 */
	public static function get( $url );

	/**
	 * Refresh cache.
	 *
	 * @param string $url Target URL.
	 * @param array  $cache Array of cache content.
	 * @return string
	 */
	public static function refresh( $url, $cache );
}
