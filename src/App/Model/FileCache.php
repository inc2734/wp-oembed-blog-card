<?php
/**
 * @package inc2734/wp-oembed-blog-card
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Inc2734\WP_OEmbed_Blog_Card\App\Model;

use Inc2734\WP_OEmbed_Blog_Card\App\Contract\Cache;

require_once( ABSPATH . 'wp-admin/includes/file.php' );

class FileCache implements Cache {

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
			[ '\Inc2734\WP_OEmbed_Blog_Card\App\Model\FileCache', '_filesystem_method' ],
			10,
			3
		);

		WP_Filesystem( false, static::_get_directory() );

		remove_filter(
			'filesystem_method',
			[ '\Inc2734\WP_OEmbed_Blog_Card\App\Model\FileCache', '_filesystem_method' ],
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
			&& untrailingslashit( static::_get_directory() ) === untrailingslashit( $context )
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
	public static function _get_directory( $url = null ) {
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
	public static function _rmdir() {
		$filesystem = static::_wp_filesystem();
		if ( $filesystem ) {
			$filesystem->rmdir( static::_get_directory(), true );
		}
		static::_reset_wp_filesystem();
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
		$directory = static::_get_directory( $url );
		if ( ! $directory ) {
			return false;
		}

		return path_join( $directory, sha1( $url ) . '.json' );
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
	 * Refresh cache.
	 *
	 * @param string $url Target URL.
	 * @param array  $cache Array of cache content.
	 * @return string
	 */
	public static function refresh( $url, $cache ) {
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
}
