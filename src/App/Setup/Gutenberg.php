<?php
/**
 * @package inc2734/wp-oembed-blog-card
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Inc2734\WP_OEmbed_Blog_Card\App\Setup;

class Gutenberg {
	public function __construct() {
		add_filter( 'rest_request_after_callbacks', [ $this, '_gutenberg_filter_oembed_result' ], 11, 3 );
	}

	/**
	 * Make sure oEmbed REST Requests apply the WP Embed security mechanism for WordPress embeds.
	 *
	 * @see  https://core.trac.wordpress.org/ticket/32522
	 * @see  https://github.com/WordPress/gutenberg/blob/master/lib/rest-api.php
	 * @copyright  https://github.com/WordPress/gutenberg
	 *
	 * TODO: This is a temporary solution. Next step would be to edit the WP_oEmbed_Controller,
	 * once merged into Core.
	 *
	 * @param  WP_HTTP_Response|WP_Error $response The REST Request response.
	 * @param  WP_REST_Server            $handler  ResponseHandler instance (usually WP_REST_Server).
	 * @param  WP_REST_Request           $request  Request used to generate the response.
	 * @return WP_HTTP_Response|object|WP_Error    The REST Request response.
	 */
	public function _gutenberg_filter_oembed_result( $response, $handler, $request ) {
		if ( 'GET' !== $request->get_method() ) {
			return $response;
		}

		if ( is_wp_error( $response ) && 'oembed_invalid_url' !== $response->get_error_code() ) {
			return $response;
		}

		if ( '/oembed/1.0/proxy' !== $request->get_route() ) {
			return $response;
		}

		$provider_name = __( 'wp-oembed-blog-card handler', 'wp-oembed-blog-card' );

		if ( isset( $response->provider_name ) && $provider_name === $response->provider_name ) {
			return $response;
		}

		global $wp_embed;
		$html = $wp_embed->shortcode( [], $request->get_param( 'url' ) );
		if ( ! $html ) {
			return $response;
		}

		return array(
			'provider_name' => $provider_name,
			'html'          => $html,
		);
	}
}
