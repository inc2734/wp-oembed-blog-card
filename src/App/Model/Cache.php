<?php
/**
 * @package inc2734/wp-oembed-blog-card
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Inc2734\WP_OEmbed_Blog_Card\App\Model;

require_once( ABSPATH . 'wp-admin/includes/file.php' );

class Cache {

	protected static $_wp_file_system;

	protected static function _wp_filesystem() {
		global $wp_filesystem;
		static::$_wp_file_system = $wp_filesystem;
		WP_Filesystem();
		return $wp_filesystem;
	}

	protected static function _reset_wp_filesystem() {
		global $wp_filesystem;
		$wp_filesystem = static::$_wp_file_system; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	}

	/**
	 * Return the directory where the cache is stored.
	 *
	 * @param string|null $url
	 * @return string
	 */
	public static function get_directory( $url = null ) {
		$upload_dir = wp_upload_dir();
		$directory  = path_join( $upload_dir['basedir'], 'wp-oembed-blog-card/' );

		if ( ! is_null( $url ) ) {
			$host = parse_url( $url, PHP_URL_HOST );
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

	public static function rmdir() {
		$wp_filesystem = static::_wp_filesystem();
		if ( $wp_filesystem ) {
			$wp_filesystem->rmdir( static::get_directory(), true );
		}
		static::_reset_wp_filesystem();
	}

	/**
	 * Return cache
	 *
	 * @param string $url
	 * @return array
	 */
	public static function get( $url ) {
		$directory = static::get_directory( $url );
		if ( ! $directory ) {
			return false;
		}

		$filepath  = trailingslashit( $directory ) . static::_get_meta_key( $url ) . '.html';
		if ( ! file_exists( $filepath ) ) {
			return false;
		}

		$cache = false;
		$wp_filesystem = static::_wp_filesystem();
		if ( $wp_filesystem ) {
			$cache = $wp_filesystem->get_contents( $filepath );
		}
		static::_reset_wp_filesystem();

		return $cache
			? json_decode( $cache, true )
			: false;
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

		delete_transient( static::_get_meta_key( $url ) ); // Delete old version cache.

		$content = false;
		$wp_filesystem = static::_wp_filesystem();
		if ( $wp_filesystem ) {
			$directory = static::get_directory( $url );
			if ( $directory ) {
				$filepath  = trailingslashit( $directory ) . static::_get_meta_key( $url ) . '.html';
				$content = $wp_filesystem->put_contents( $filepath, json_encode( $cache ) );
			}
		}
		static::_reset_wp_filesystem();

		return $content;
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
