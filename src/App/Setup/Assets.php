<?php
/**
 * @package inc2734/wp-oembed-blog-card
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Inc2734\WP_OEmbed_Blog_Card\App\Setup;

use Inc2734\WP_OEmbed_Blog_Card\App\View\View;

class Assets {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, '_enqueue_scripts' ), 9 );
		add_action( 'enqueue_block_assets', array( $this, '_enqueue_block_assets' ), 9 );
		add_action( 'enqueue_block_editor_assets', array( $this, '_enqueue_block_editor_assets' ) );
	}

	/**
	 * Enqueue scripts.
	 */
	public function _enqueue_scripts() {
		wp_enqueue_script(
			'wp-oembed-blog-card',
			get_template_directory_uri() . '/vendor/inc2734/wp-oembed-blog-card/src/assets/js/app.js',
			array(),
			filemtime( get_template_directory() . '/vendor/inc2734/wp-oembed-blog-card/src/assets/js/app.js' ),
			array(
				'in_footer' => false,
				'strategy'  => 'defer',
			)
		);

		wp_localize_script(
			'wp-oembed-blog-card',
			'WP_OEMBED_BLOG_CARD',
			array(
				'endpoint' => get_rest_url( null, '/wp-oembed-blog-card/v1' ),
			)
		);
	}

	/**
	 * Enqueue assets
	 */
	public function _enqueue_block_assets() {
		wp_enqueue_style(
			'wp-oembed-blog-card',
			get_template_directory_uri() . '/vendor/inc2734/wp-oembed-blog-card/src/assets/css/app.css',
			array(),
			filemtime( get_template_directory() . '/vendor/inc2734/wp-oembed-blog-card/src/assets/css/app.css' )
		);
	}

	/**
	 * Enqueue assets for editor.
	 */
	public function _enqueue_block_editor_assets() {
		$dependencies = include get_template_directory() . '/vendor/inc2734/wp-oembed-blog-card/src/assets/js/editor.asset.php';
		wp_enqueue_script(
			'wp-oembed-blog-card@editor',
			get_template_directory_uri() . '/vendor/inc2734/wp-oembed-blog-card/src/assets/js/editor.js',
			$dependencies['dependencies'],
			filemtime( get_template_directory() . '/vendor/inc2734/wp-oembed-blog-card/src/assets/js/editor.js' ),
			array(
				'in_footer' => false,
				'strategy'  => 'defer',
			)
		);
	}
}
