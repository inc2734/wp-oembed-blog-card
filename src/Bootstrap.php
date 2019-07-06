<?php
/**
 * @package inc2734/wp-oembed-blog-card
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Inc2734\WP_OEmbed_Blog_Card;

use Inc2734\WP_OEmbed_Blog_Card\App\Model\Cache;
use Inc2734\WP_OEmbed_Blog_Card\App\View\View;
use Inc2734\WP_OEmbed_Blog_Card\App\Setup;

class Bootstrap {

	public function __construct() {
		add_action( 'rest_api_init', [ $this, '_rest_api_init' ] );

		if ( $this->_is_admin_request() ) {
			return false;
		}

		if ( $this->_is_wordpress_request() ) {
			return false;
		}

		add_filter( 'embed_oembed_html', [ $this, '_embed_html_for_wordpress' ], 9, 2 );
		add_filter( 'embed_maybe_make_link', [ $this, '_embed_html_for_no_oembed' ], 9, 2 );

		if ( $this->_is_block_embed_rendering_request() ) {
			add_filter( 'rest_request_after_callbacks', [ $this, '_block_filter_oembed_result' ], 11, 3 );
		} else {
			new Setup\Assets();
		}
	}

	/**
	 * Add REST API for get blog card content from ajax
	 *
	 * @SuppressWarnings(PHPMD.ExitExpression)
	 *
	 * @param string $cache
	 * @param string $url
	 * @return string
	 */
	public function _rest_api_init() {
		register_rest_route(
			'wp-oembed-blog-card/v1',
			'/response',
			[
				'methods'  => 'GET',
				'callback' => function( $request ) {
					$params = $request->get_params();
					if ( empty( $params['url'] ) ) {
						return;
					}

					header( 'Content-Type: text/html; charset=utf-8' );
					$url = esc_url_raw( wp_unslash( $params['url'] ) );
					echo wp_kses_post( View::get_template( $url ) );
					die();
				},
			]
		);
	}

	/**
	 * Return embed html for WordPress oEmbed
	 *
	 * @param string $cache
	 * @param string $url
	 * @return string
	 */
	public function _embed_html_for_wordpress( $cache, $url ) {
		if ( 0 !== strpos( $cache, '<blockquote class="wp-embedded-content"' ) ) {
			return $cache;
		}

		return $this->_render( $url );
	}

	/**
	 * Return embed HTML for non oEmbed link
	 *
	 * @param string $output
	 * @param string $url
	 * @return string
	 */
	public function _embed_html_for_no_oembed( $output, $url ) {
		return $this->_render( $url );
	}

	/**
	 * Make sure oEmbed REST Requests apply the WP Embed security mechanism for WordPress embeds.
	 *
	 * @see  https://core.trac.wordpress.org/ticket/32522
	 * @see  https://github.com/WordPress/gutenberg/blob/master/lib/rest-api.php
	 * @copyright  https://github.com/WordPress/gutenberg
	 *
	 * @param  WP_HTTP_Response|WP_Error $response The REST Request response.
	 * @param  WP_REST_Server            $handler  ResponseHandler instance (usually WP_REST_Server).
	 * @param  WP_REST_Request           $request  Request used to generate the response.
	 * @return WP_HTTP_Response|object|WP_Error    The REST Request response.
	 */
	public function _block_filter_oembed_result( $response, $handler, $request ) {
		if ( isset( $response->html ) ) {
			if ( 0 !== strpos( $response->html, '<blockquote class="wp-embedded-content"' ) ) {
				return $response;
			}
		}

		global $wp_embed;
		$html = $wp_embed->shortcode( [], $request->get_param( 'url' ) );
		if ( ! $html ) {
			return $response;
		}

		return [
			'provider_name' => 'wp-oembed-blog-card',
			'html'          => $html,
		];
	}

	/**
	 * Refresh cache if the cache is expired or is_admin
	 *
	 * @param string $url
	 * @return void
	 */
	protected function _maybe_refresh_cache( $url ) {
		$cache = Cache::get( $url );

		if ( ! $cache || is_admin() ) {
			Cache::refresh( $url );
		}
	}

	/**
	 * Rendering bloc card on editor
	 *
	 * @param string $url
	 * @return string
	 */
	protected function _render( $url ) {
		$this->_maybe_refresh_cache( $url );

		if ( ! is_admin() ) {
			return $this->_is_block_embed_rendering_request()
				? View::get_block_template( $url )
				: View::get_pre_blog_card_template( $url );
		}

		return View::get_template( $url );
	}

	/**
	 * Return true when requested from WordPress
	 *
	 * @return boolean
	 */
	protected function _is_wordpress_request() {
		if ( ! isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			return false;
		}

		$user_agent = wp_unslash( $_SERVER['HTTP_USER_AGENT'] ); // WPCS: sanitization ok.

		return 0 === strpos( $user_agent, 'WordPress' );
	}

	/**
	 * Return true when requested from admin-ajax.php, wp-cron.php, wp-json
	 *
	 * @return boolean
	 */
	protected function _is_admin_request() {
		if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		$request_uri = wp_unslash( $_SERVER['REQUEST_URI'] ); // WPCS: sanitization ok.

		if ( false !== strpos( $request_uri, '/wp-admin/admin-ajax.php' ) ) {
			return false === strpos( $request_uri, 'action=wp_oembed_blog_card_render' );
		}

		if ( false !== strpos( $request_uri, '/wp-json/' ) ) {
			return false === strpos( $request_uri, '/wp-json/oembed/' );
		}

		if ( false !== strpos( $request_uri, '/wp-cron.php' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Return true when block embed rendering request
	 *
	 * @return boolean
	 */
	protected function _is_block_embed_rendering_request() {
		if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		return false !== strpos( $_SERVER['REQUEST_URI'], '/wp-json/oembed/1.0/proxy?url=' );
	}
}
