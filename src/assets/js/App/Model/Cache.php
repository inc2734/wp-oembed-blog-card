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
	 * @var WP_Filesystem
	 */
	protected static $_wp_file_system;

	/**
	 * Set WP_Filesystem_Direct to $wp_filesystem.
	 *
	 * @return WP_Filesystem;
	 */
	protected static function _wp_filesystem() {
		global $wp_filesystem;
		static::$_wp_file_system = $wp_filesystem;

		add_filter(
			'filesystem_method',
			[ '\Inc2734\WP_OEmbed_Blog_Card\App\Model\Cache', '_filesystem_method' ],
			10,
			3
		);

		WP_Filesystem( false, static::get_directory() );

		remove_filter(
			'filesystem_method',
			[ '\Inc2734\WP_OEmbed_Blog_Card\App\Model\Cache', '_filesystem_method' ],
			10,
			3
		);

		return $wp_filesystem;
	}

	/**
	 * Set WP_Filesystem_Direct to $wp_filesystem.
	 *
	 * @param string $method  Filesystem method to return.
	 * @param array  $args    An array of connection details for the method.
	 * @param string $context Full path to the directory that is tested for being writable.
	 * @return string
	 */
	public static function _filesystem_method( $method, $args, $context ) {
		if (
			'direct' !== $method
			&& untrailingslashit( static::get_directory() ) === untrailingslashit( $context )
		) {
			return 'direct';
		}
		return $method;
	}

	/**
	 * Reset $wp_filesystem.
	 */
	protected static function _reset_wp_filesystem() {
		global $wp_filesystem;
		$wp_filesystem = static::$_wp_file_system; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	}

	/**
	 * Return the directory where the cache is stored.
	 *
	 * @param string|null $url Target URL.
	 * @return string
	 */
	public static function get_directory( $url = null ) {
		$upload_dir = wp_upload_dir();
		$basedir    = apply_filters( 'inc2734_wp_oembed_blog_card_cache_directory', $upload_dir['basedir'] );
		$directory  = path_join( $basedir, 'wp-oembed-blog-card' );

		if ( ! is_null( $url ) ) {
			$host      = parse_url( $url, PHP_URL_HOST );
			$directory = path_join( $directory, $host );
		}

		if ( ! file_exists( $directory ) ) {
			$created = wp_mkdir_p( $directory );
			if ( ! $created ) {
				return false;
			}
		}

		if ( ! is_writable( $directory ) ) {
			return false;
		}

		return $directory;
	}

	/**
	 * Remove directory.
	 */
	public static function rmdir() {
		$filesystem = static::_wp_filesystem();
		if ( $filesystem ) {
			$filesystem->rmdir( static::get_directory(), true );
		}
		static::_reset_wp_filesystem();
	}

	/**
	 * Return cache.
	 *
	 * @param string $url Target URL.
	 * @return boolean
	 */
	public static function get( $url ) {
		$filepath = static::_get_cache_filepath( $url );
		if ( ! file_exists( $filepath ) ) {
			return false;
		}

		$cache      = false;
		$filesystem = static::_wp_filesystem();
		if ( $filesystem ) {
			$cache = $filesystem->get_contents( $filepath );
		}
		static::_reset_wp_filesystem();

		return $cache
			? json_decode( $cache, true )
			: false;
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

		$content    = false;
		$filesystem = static::_wp_filesystem();
		if ( $filesystem ) {
			$filepath = static::_get_cache_filepath( $url );
			if ( $filepath ) {
				$content = $filesystem->put_contents( $filepath, json_encode( $cache ) );
			}
		}
		static::_reset_wp_filesystem();

		return $content;
	}

	/**
	 * Get cache filename.
	 *
	 * @see https://qiita.com/koriym/items/efc1c419e4b7772b65c0
	 *
	 * @param string $url Target URL.
	 * @return string
	 */
	protected static function _get_cache_filepath( $url ) {
		$directory = static::get_directory( $url );
		if ( ! $directory ) {
			return false;
		}

		return path_join( $directory, sha1( $url ) . '.json' );
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
