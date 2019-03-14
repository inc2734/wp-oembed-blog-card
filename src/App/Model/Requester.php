<?php
/**
 * @package inc2734/wp-oembed-blog-card
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Inc2734\WP_OEmbed_Blog_Card\App\Model;

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
	protected $response = [];

	/**
	 * @param string $url
	 */
	public function __construct( $url ) {
		$this->url = $url;

		$remote_addr = isset( $_SERVER['REMOTE_ADDR'] ) ? wp_unslash( $_SERVER['REMOTE_ADDR'] ) : null; // WPCS: sanitization ok.
		$server_addr = isset( $_SERVER['SERVER_ADDR'] ) ? wp_unslash( $_SERVER['SERVER_ADDR'] ) : null; // WPCS: sanitization ok.

		if ( empty( $_SERVER['HTTP_USER_AGENT'] ) || $remote_addr === $server_addr ) {
			$user_agent = 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' );
		} else {
			$user_agent = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) );
		}

		$this->user_agent = apply_filters( 'http_headers_useragent', $user_agent );
	}

	/**
	 * Request
	 *
	 * @return WP_Error|array
	 */
	public function request() {
		if ( 0 === strpos( $this->url, 'http://127.0.0.1:' ) || 0 === strpos( $this->url, 'http://localhost:' ) ) {
			return new \WP_Error(
				'http_request_failed',
				__( 'Requests for local URLs are not supported.', 'inc2734-wp-oembed-blog-card' )
			);
		}

		$this->response = wp_remote_get(
			$this->url,
			[
				'timeout'    => 10,
				'user-agent' => $this->user_agent,
			]
		);

		return $this->response;
	}

	/**
	 * Return status code of the page you want to blog card
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
	 * Return content type of the page you want to blog card
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

		return $headers->offsetGet( 'content-type' );
	}

	/**
	 * Return response body
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
	 * @param string $content
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
