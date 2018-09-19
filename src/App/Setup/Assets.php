<?php
/**
 * @package inc2734/wp-oembed-blog-card
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Inc2734\WP_OEmbed_Blog_Card\App\Setup;

class Assets {
	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, '_enqueue_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, '_enqueue_styles' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, '_enqueue_styles' ] );
		add_action( 'after_setup_theme', [ $this, '_add_editor_style' ] );
	}

	/**
	 * Add editor style
	 *
	 * @return void
	 */
	public function _add_editor_style() {
		add_editor_style(
			[
				'vendor/inc2734/wp-oembed-blog-card/src/assets/css/wp-oembed-blog-card.min.css',
			]
		);
	}

	/**
	 * Enqueue scripts
	 *
	 * @return void
	 */
	public function _enqueue_scripts() {
		$relative_path = '/vendor/inc2734/wp-oembed-blog-card/src/assets/js/wp-oembed-blog-card.min.js';
		$src  = get_template_directory_uri() . $relative_path;
		$path = get_template_directory() . $relative_path;

		if ( ! file_exists( $path ) ) {
			return;
		}

		wp_enqueue_script(
			'wp-oembed-blog-card',
			$src,
			[ 'jquery' ],
			filemtime( $path ),
			true
		);

		wp_localize_script(
			'wp-oembed-blog-card',
			'WP_OEMBED_BLOG_CARD',
			[
				'endpoint' => admin_url( 'admin-ajax.php' ),
				'action'   => 'wp_oembed_blog_card_render',
			]
		);
	}

	/**
	 * Enqueue styles
	 *
	 * @return void
	 */
	public function _enqueue_styles() {
		$relative_path = '/vendor/inc2734/wp-oembed-blog-card/src/assets/css/wp-oembed-blog-card.min.css';
		$src  = get_template_directory_uri() . $relative_path;
		$path = get_template_directory() . $relative_path;

		if ( ! file_exists( $path ) ) {
			return;
		}

		wp_enqueue_style(
			'wp-oembed-blog-card',
			$src,
			[],
			filemtime( $path )
		);
	}
}
