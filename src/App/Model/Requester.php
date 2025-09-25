<?php
/**
 * @package inc2734/wp-oembed-blog-card
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Inc2734\WP_OEmbed_Blog_Card\App\Model;

use WP_Error;

class Requester {

	/**
	 * URL of the page you want to blog card
	 *
	 * @var string
	 */
	protected $url;

	/**
	 * User Agent
	 *
	 * @var string
	 */
	protected $user_agent;

	/**
	 * Response
	 *
	 * @var array
	 */
	protected $response = array();

	/**
	 * Constructor.
	 *
	 * @param string $url Target URL.
	 */
	public function __construct( $url ) {
		$this->url = $url;

		$remote_addr     = filter_input( INPUT_SERVER, 'REMOTE_ADDR' );
		$server_addr     = filter_input( INPUT_SERVER, 'SERVER_ADDR' );
		$http_user_agent = filter_input( INPUT_SERVER, 'HTTP_USER_AGENT' );

		$remote_addr = $remote_addr ? wp_unslash( $remote_addr ) : null; // WPCS: sanitization ok.
		$server_addr = $server_addr ? wp_unslash( $server_addr ) : null; // WPCS: sanitization ok.

		if ( ! $http_user_agent || $remote_addr === $server_addr ) {
			$user_agent = 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' );
		} else {
			$user_agent = sanitize_text_field( wp_unslash( $http_user_agent ) );
		}

		$this->user_agent = apply_filters( 'http_headers_useragent', $user_agent );
	}

	/**
	 * Request.
	 *
	 * @return WP_Error|array
	 */
	public function request() {
		$cache_key   = md5( wp_json_encode( $this->url ) );
		$cache_group = 'inc2734/wp-oembed-blog-card/request';
		$cache       = wp_cache_get( $cache_key, $cache_group );

		if ( false !== $cache ) {
			$this->response = $cache;
		} else {
			$parsed_url = wp_parse_url( $this->url );
			if (
				empty( $parsed_url['scheme'] ) ||
				! in_array( strtolower( (string) $parsed_url['scheme'] ), array( 'http', 'https' ), true )
			) {
				return new WP_Error( 'invalid_scheme', __( 'Only http/https are allowed.', 'inc2734-wp-oembed-blog-card' ) );
			}

			if ( ! wp_http_validate_url( $this->url ) ) {
				return new WP_Error( 'invalid_url', __( 'Invalid URL.', 'inc2734-wp-oembed-blog-card' ) );
			}

			$this->response = wp_safe_remote_get(
				$this->url,
				array(
					'timeout'             => 10,
					'redirection'         => 0,
					'limit_response_size' => 1 * MB_IN_BYTES,
					'user-agent'          => $this->user_agent,
				)
			);
		}

		if ( is_wp_error( $this->response ) ) {
			return new WP_Error(
				'http_request_failed',
				__( 'Can\'t be retrieved because it is on the local network or refers to an invalid URL.', 'inc2734-wp-oembed-blog-card' )
			);
		}

		$content_type = wp_remote_retrieve_header( $this->response, 'content-type' );
		if ( $content_type && ! preg_match( '#^(text/html)(;|$)#i', $content_type ) ) {
			return new WP_Error( 'disallowed_content_type', __( 'Disallowed Content-Type.', 'inc2734-wp-oembed-blog-card' ) );
		}

		$status_code = $this->get_status_code();
		if ( 200 !== $status_code && 304 !== $status_code ) {
			return new WP_Error(
				'disallowed_status_code',
				sprintf(
					// translators: %1$s: Status code.
					__( 'Can\'t process URLs that return status code %1$s.', 'inc2734-wp-oembed-blog-card' ),
					$status_code
				)
			);
		}

		wp_cache_set( $cache_key, $this->response, $cache_group );

		return $this->response;
	}

	/**
	 * Return status code of the page you want to blog card.
	 *
	 * @return string
	 */
	public function get_status_code() {
		$status_code = wp_remote_retrieve_response_code( $this->response );

		if ( ! $status_code ) {
			$status_code = 404;
		}

		return $status_code;
	}

	/**
	 * Return content type of the page you want to blog card.
	 *
	 * @return string
	 */
	public function get_content_type() {
		$headers = wp_remote_retrieve_headers( $this->response );
		if ( ! $headers ) {
			return;
		}

		if ( ! is_object( $headers ) || ! method_exists( $headers, 'offsetGet' ) ) {
			return;
		}

		$content_type = $headers->offsetGet( 'content-type' );
		if ( $content_type ) {
			return $content_type;
		}

		$content = $this->get_content();
		if ( false !== strpos( $content, '<html ' ) ) {
			return 'text/html';
		}

		return false;
	}

	/**
	 * Return response body.
	 *
	 * @return string
	 */
	public function get_content() {
		$content = wp_remote_retrieve_body( $this->response );

		if ( empty( $content ) ) {
			return;
		}

		return $this->_encode( $content );
	}

	/**
	 * Encode.
	 *
	 * @param string $content The text you want to encode.
	 * @return string
	 */
	protected function _encode( $content ) {
		if ( ! function_exists( 'mb_convert_encoding' ) || ! $content ) {
			return $content;
		}

		foreach ( array( 'UTF-8', 'SJIS', 'EUC-JP', 'ASCII', 'JIS' ) as $encode ) {
			$encoded_content = mb_convert_encoding( $content, $encode, $encode );
			if ( strcmp( $content, $encoded_content ) === 0 ) {
				$from_encode = $encode;
				break;
			}
		}

		if ( empty( $from_encode ) ) {
			return $content;
		}

		return mb_convert_encoding( $content, get_bloginfo( 'charset' ), $from_encode );
	}
}
