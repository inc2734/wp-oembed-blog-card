<?php
/**
 * @package inc2734/wp-oembed-blog-card
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Inc2734\WP_OEmbed_Blog_Card\App\Setup;

use Inc2734\WP_OEmbed_Blog_Card\App\View\View;

class Assets {
	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, '_enqueue_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, '_enqueue_styles' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, '_enqueue_styles' ] );
		add_action( 'after_setup_theme', [ $this, '_add_editor_style' ] );
		add_action( 'wp_ajax_wp_oembed_blog_card_render', [ $this, '_wp_oembed_blog_card_render' ] );
		add_action( 'wp_ajax_nopriv_wp_oembed_blog_card_render', [ $this, '_wp_oembed_blog_card_render' ] );
	}

	/**
	 * Add editor style
	 *
	 * @return void
	 */
	public function _add_editor_style() {
		add_editor_style(
			[
				'vendor/inc2734/wp-oembed-blog-card/src/assets/css/app.min.css',
			]
		);
	}

	/**
	 * Enqueue scripts
	 *
	 * @return void
	 */
	public function _enqueue_scripts() {
		$relative_path = '/vendor/inc2734/wp-oembed-blog-card/src/assets/js/app.min.js';
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
		$relative_path = '/vendor/inc2734/wp-oembed-blog-card/src/assets/css/app.min.css';
		wp_enqueue_style(
			'wp-oembed-blog-card',
			get_template_directory_uri() . $relative_path,
			[],
			filemtime( get_template_directory() . $relative_path )
		);
	}

	/**
	 * Render blog card with ajax
	 *
	 * @SuppressWarnings(PHPMD.ExitExpression)
	 *
	 * @return void
	 */
	public function _wp_oembed_blog_card_render() {
		if ( empty( $_GET['url'] ) ) {
			return;
		}

		header( 'Content-Type: text/html; charset=utf-8' );
		$url = esc_url_raw( wp_unslash( $_GET['url'] ) );
		echo wp_kses_post( View::get_template( $url ) );
		die();
	}
}
