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
		add_action( 'wp_enqueue_scripts', [ $this, '_enqueue_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, '_enqueue_styles' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, '_enqueue_styles' ] );
		add_action( 'after_setup_theme', [ $this, '_add_editor_style' ] );
	}

	/**
	 * Add editor style
	 */
	public function _add_editor_style() {
		add_editor_style(
			[
				'vendor/inc2734/wp-oembed-blog-card/src/assets/css/app.css',
			]
		);
	}

	/**
	 * Enqueue scripts
	 */
	public function _enqueue_scripts() {
		$relative_path = '/vendor/inc2734/wp-oembed-blog-card/src/assets/js/app.js';
		wp_enqueue_script(
			'wp-oembed-blog-card',
			get_template_directory_uri() . $relative_path,
			[],
			filemtime( get_template_directory() . $relative_path ),
			true
		);

		wp_localize_script(
			'wp-oembed-blog-card',
			'WP_OEMBED_BLOG_CARD',
			[
				'endpoint' => get_rest_url( null, '/wp-oembed-blog-card/v1' ),
			]
		);
	}

	/**
	 * Enqueue styles
	 *
	 * @return void
	 */
	public function _enqueue_styles() {
		$relative_path = '/vendor/inc2734/wp-oembed-blog-card/src/assets/css/app.css';
		wp_enqueue_style(
			'wp-oembed-blog-card',
			get_template_directory_uri() . $relative_path,
			[],
			filemtime( get_template_directory() . $relative_path )
		);
	}
}
