<?php
/**
 * @package inc2734/wp-oembed-blog-card
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Inc2734\WP_OEmbed_Blog_Card\App\Model;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @todo refactoring
 */
class Parser {

	/**
	 * URL of the page you want to blog card
	 *
	 * @var string
	 */
	protected $url;

	/**
	 * Status code of the page you want to blog card
	 *
	 * @var int
	 */
	protected $status_code;

	/**
	 * Content type of the page you want to blog card
	 *
	 * @var int
	 */
	protected $content_type;

	/**
	 * Title of the page you want to blog card
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * Permalink of the page you want to blog card
	 *
	 * @var string
	 */
	protected $permalink;

	/**
	 * Description of the page you want to blog card
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * Domain of the page you want to blog card
	 *
	 * @var string
	 */
	protected $domain;

	/**
	 * Favicon of the page you want to blog card
	 *
	 * @var string
	 */
	protected $favicon;

	/**
	 * Thumbnail of the page you want to blog card
	 *
	 * @var string
	 */
	protected $thumbnail;

	/**
	 * User Agent
	 *
	 * @var string
	 */
	protected $user_agent;

	/**
	 * @param string $url
	 */
	public function __construct( $url ) {
		$this->url = $url;

		if ( empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$user_agent = 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' );
		} else {
			$user_agent = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) );
		}
		$this->user_agent = apply_filters( 'http_headers_useragent', $user_agent );

		$response = $this->_request( $this->url );
		if ( is_wp_error( $response ) ) {
			return;
		}

		$this->status_code = $this->_get_status_code( $response );
		if ( 200 != $this->get_status_code() && 304 != $this->get_status_code() ) {
			return;
		}

		if ( wp_http_validate_url( $this->url ) ) {
			$this->content_type = $this->_get_content_type( $response );
		} else {
			$this->content_type = mime_content_type( $this->url );
		}
		if ( ! preg_match( '/text\/html/', $this->get_content_type() ) ) {
			return;
		}

		$content = $this->_get_content( $response );

		$this->title       = $this->_get_title( $content );
		$this->permalink   = $this->_get_permalink( $content );
		$this->description = $this->_get_description( $content );
		$this->domain      = $this->_get_domain( $content );
		$this->favicon     = $this->_get_favicon( $content );
		$this->thumbnail   = $this->_get_thumbnail( $content );
	}

	/**
	 * Request
	 *
	 * @param string $url
	 * @return WP_Error|array
	 */
	protected function _request( $url ) {
		return wp_remote_get( $url, [
			'timeout'    => 10,
			'user-agent' => $this->user_agent,
		] );
	}

	/**
	 * @param array $response
	 * @return string
	 */
	protected function _get_content( $response ) {
		$content = wp_remote_retrieve_body( $response );

		if ( empty( $content ) ) {
			return;
		}

		return $this->_encode( $content );
	}

	/**
	 * Return status code of the page you want to blog card
	 *
	 * @param array $response
	 * @return string
	 */
	protected function _get_status_code( $response ) {
		$status_code = wp_remote_retrieve_response_code( $response );

		if ( ! $status_code ) {
			$status_code = 404;
		}

		return $status_code;
	}

	/**
	 * Return content type of the page you want to blog card
	 *
	 * @param array $response
	 * @return string
	 */
	protected function _get_content_type( $response ) {
		$headers = wp_remote_retrieve_headers( $response );
		if ( ! $headers ) {
			return;
		}

		if ( ! is_object( $headers ) || ! method_exists( $headers, 'offsetGet' ) ) {
			return;
		}

		return $headers->offsetGet( 'content-type' );
	}

	/**
	 * Return page title of the page you want to blog card
	 *
	 * @param string $content
	 * @return string
	 */
	protected function _get_title( $content ) {
		preg_match( '/<meta +?property=["\']og:title["\'][^\/>]*? content=["\']([^"\']+?)["\'].*?\/?>/si', $content, $reg );
		if ( ! empty( $reg[1] ) ) {
			return $reg[1];
		}

		preg_match( '/<title>([^"\']+?)<\/title>/si', $content, $reg );
		if ( ! empty( $reg[1] ) ) {
			return $reg[1];
		}
	}

	/**
	 * Return URL of the page you want to blog card
	 *
	 * @param string $content
	 * @return string
	 */
	protected function _get_permalink( $content ) {
		preg_match( '/<meta +?property=["\']og:url["\'][^\/>]*? content=["\']([^"\']+?)["\'].*?\/?>/si', $content, $reg );
		if ( ! empty( $reg[1] ) ) {
			return $reg[1];
		}

		return $this->url;
	}

	/**
	 * Return page description of the page you want to blog card
	 *
	 * @param string $content
	 * @return string
	 */
	protected function _get_description( $content ) {
		preg_match( '/<meta +?property=["\']og:description["\'][^\/>]*? content=["\']([^"\']+?)["\'].*?\/?>/si', $content, $reg );
		if ( ! empty( $reg[1] ) ) {
			return $reg[1];
		}

		preg_match( '/<meta +?name=["\']description["\'][^\/>]*? content=["\']([^"\']+?)["\'].*?\/?>/si', $content, $reg );
		if ( ! empty( $reg[1] ) ) {
			return $reg[1];
		}
	}

	/**
	 * Return domain of the page you want to blog card
	 *
	 * @param string $content
	 * @return string
	 */
	protected function _get_domain( $content ) {
		$permalink = $this->get_permalink();
		if ( ! $permalink ) {
			$permalink = $this->_get_permalink( $content );
		}

		preg_match( '/https?:\/\/([^\/]+)/', $permalink, $reg );
		if ( ! empty( $reg[1] ) ) {
			return $reg[1];
		}
	}

	/**
	 * Return favicon of the page you want to blog card
	 *
	 * @param string $content
	 * @return string
	 */
	protected function _get_favicon( $content ) {
		preg_match( '/<link +?rel=["\']shortcut icon["\'][^\/>]*? href=["\']([^"\']+?)["\'][^\/>]*?\/?>/si', $content, $reg );
		if ( empty( $reg[1] ) ) {
			preg_match( '/<link +?rel=["\']icon["\'][^\/>]*? href=["\']([^"\']+?)["\'][^\/>]*?\/?>/si', $content, $reg );
		}

		if ( empty( $reg[1] ) ) {
			return;
		}

		$favicon = $reg[1];
		$favicon = $this->_relative_path_to_url( $favicon, $content );

		if ( is_ssl() ) {
			$favicon = preg_replace( '|^http:|', 'https:', $favicon );
		}

		$response = $this->_request( $favicon );
		if ( is_wp_error( $response ) ) {
			return;
		}

		$status_code = $this->_get_status_code( $response );

		if ( 200 != $status_code && 304 != $status_code ) {
			return;
		}

		return $favicon;
	}

	/**
	 * Return thumbnail of the page you want to blog card
	 *
	 * @param string $content
	 * @return string
	 */
	protected function _get_thumbnail( $content ) {
		preg_match( '/<meta +?property=["\']og:image["\'][^\/>]*? content=["\']([^"\']+?)["\'].*?\/?>/si', $content, $reg );
		if ( empty( $reg[1] ) ) {
			return;
		}

		$thumbnail = $reg[1];
		$thumbnail = $this->_relative_path_to_url( $thumbnail, $content );

		if ( is_ssl() ) {
			$thumbnail = preg_replace( '|^http:|', 'https:', $thumbnail );
		}

		$response = $this->_request( $thumbnail );
		if ( is_wp_error( $response ) ) {
			return;
		}

		$status_code = $this->_get_status_code( $response );

		if ( 200 != $status_code && 304 != $status_code ) {
			return;
		}

		return $thumbnail;
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

	/**
	 * Return url that converted from relative path
	 *
	 * @param string $path
	 * @param string $content
	 * @return string
	 */
	protected function _relative_path_to_url( $path, $content ) {
		if ( wp_http_validate_url( $path ) ) {
			return $path;
		}

		$permalink = $this->get_permalink();
		if ( ! $permalink ) {
			$permalink = $this->_get_permalink( $content );
		}

		preg_match( '/(https?:\/\/[^\/]+)/', $permalink, $reg );
		if ( empty( $reg[0] ) ) {
			return false;
		}

		return trailingslashit( $reg[0] ) . $path;
	}

	/**
	 * Return status code of the page you want to blog card
	 *
	 * @return int
	 */
	public function get_status_code() {
		return $this->status_code;
	}

	/**
	 * Return content type of the page you want to blog card
	 *
	 * @return int
	 */
	public function get_content_type() {
		return $this->content_type;
	}

	/**
	 * Return page title of the page you want to blog card
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Return URL of the page you want to blog card
	 *
	 * @return string
	 */
	public function get_permalink() {
		return $this->permalink;
	}

	/**
	 * Return page description of the page you want to blog card
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Return domain of the page you want to blog card
	 *
	 * @return string
	 */
	public function get_domain() {
		return $this->domain;
	}

	/**
	 * Return favicon of the page you want to blog card
	 *
	 * @return string
	 */
	public function get_favicon() {
		return $this->favicon;
	}

	/**
	 * Return thumbnail of the page you want to blog card
	 *
	 * @return string
	 */
	public function get_thumbnail() {
		return $this->thumbnail;
	}
}
