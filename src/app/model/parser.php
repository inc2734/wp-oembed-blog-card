<?php
/**
 * @package inc2734/wp-oembed-blog-card
 * @author inc2734
 * @license GPL-2.0+
 */

/**
 * Parsing web page class
 */
class Inc2734_WP_OEmbed_Blog_Card_Parser {

	/**
	 * URL of the page you want to blog card
	 *
	 * @var string
	 */
	protected $url;

	/**
	 * Content of the page you want to blog card
	 *
	 * @var content
	 */
	protected $content;

	/**
	 * Status code of the page you want to blog card
	 *
	 * @var int
	 */
	protected $status_code;

	/**
	 * @param string $url
	 */
	public function __construct( $url ) {
		$this->url = $url;
		$response  = wp_remote_get( $this->url );

		$this->content     = $this->_get_content( $response );
		$this->status_code = $this->_get_status_code( $response );

		if ( $this->content && ! $this->status_code ) {
			$this->status_code = 200;
		}

		if ( ! $this->status_code ) {
			$this->status_code = '404';
		}
	}

	/**
	 * @param array $response
	 * @return string
	 */
	protected function _get_content( $response ) {
		if ( wp_http_validate_url( $this->url ) ) {
			if ( is_array( $response ) && isset( $response['body'] ) ) {
				$content = $response['body'];
			}
		} elseif ( WP_Filesystem() ) {
			global $wp_filesystem;
			$content = $wp_filesystem->get_contents( $this->url );
		}

		if ( empty( $content ) ) {
			return;
		}

		return $this->_encode( $content );
	}

	/**
	 * @param array $response
	 * @return string
	 */
	protected function _get_status_code( $response ) {
		if ( wp_http_validate_url( $this->url ) ) {
			if ( is_array( $response ) && isset( $response['response'] ) ) {
				if ( is_array( $response['response'] ) && isset( $response['response']['code'] ) ) {
					return $response['response']['code'];
				}
			}
		}
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
	 * Return status code of the page you want to blog card
	 *
	 * @return int
	 */
	public function get_status_code() {
		return $this->status_code;
	}

	/**
	 * Return page title of the page you want to blog card
	 *
	 * @return string
	 */
	public function get_title() {
		preg_match( '/<meta +?property=["\']og:title["\'][^\/>]*? content=["\']([^"\']+?)["\'].*?\/?>/si', $this->content, $reg );
		if ( ! empty( $reg[1] ) ) {
			return $reg[1];
		}

		preg_match( '/<title>([^"\']+?)<\/title>/si', $this->content, $reg );
		if ( ! empty( $reg[1] ) ) {
			return $reg[1];
		}
	}

	/**
	 * Return URL of the page you want to blog card
	 *
	 * @return string
	 */
	public function get_permalink() {
		preg_match( '/<meta +?property=["\']og:url["\'][^\/>]*? content=["\']([^"\']+?)["\'].*?\/?>/si', $this->content, $reg );
		if ( ! empty( $reg[1] ) ) {
			return $reg[1];
		}

		return $this->url;
	}

	/**
	 * Return page description of the page you want to blog card
	 *
	 * @return string
	 */
	public function get_description() {
		preg_match( '/<meta +?property=["\']og:description["\'][^\/>]*? content=["\']([^"\']+?)["\'].*?\/?>/si', $this->content, $reg );
		if ( ! empty( $reg[1] ) ) {
			return $reg[1];
		}

		preg_match( '/<meta +?name=["\']description["\'][^\/>]*? content=["\']([^"\']+?)["\'].*?\/?>/si', $this->content, $reg );
		if ( ! empty( $reg[1] ) ) {
			return $reg[1];
		}
	}

	/**
	 * Return domain of the page you want to blog card
	 *
	 * @return string
	 */
	public function get_domain() {
		$permalink = $this->get_permalink();
		preg_match( '/https?:\/\/([^\/]+)/', $permalink, $reg );
		if ( ! empty( $reg[1] ) ) {
			return $reg[1];
		}
	}

	/**
	 * Return favicon of the page you want to blog card
	 *
	 * @return string
	 */
	public function get_favicon() {
		preg_match( '/<link +?rel=["\']shortcut icon["\'][^\/>]*? href=["\']([^"\']+?)["\'][^\/>]*?\/?>/si', $this->content, $reg );
		if ( empty( $reg[1] ) ) {
			preg_match( '/<link +?rel=["\']icon["\'][^\/>]*? href=["\']([^"\']+?)["\'][^\/>]*?\/?>/si', $this->content, $reg );
		}

		if ( empty( $reg[1] ) ) {
			return;
		}

		$favicon = $reg[1];

		if ( is_ssl() ) {
			$favicon = preg_replace( '|^http:|', 'https:', $favicon );
		}

		$response    = wp_remote_get( $favicon );
		$status_code = $this->_get_status_code( $response );

		if ( 200 != $status_code && 304 != $status_code ) {
			return;
		}

		return $favicon;
	}

	/**
	 * Return thumbnail of the page you want to blog card
	 *
	 * @return string
	 */
	public function get_thumbnail() {
		preg_match( '/<meta +?property=["\']og:image["\'][^\/>]*? content=["\']([^"\']+?)["\'].*?\/?>/si', $this->content, $reg );
		if ( empty( $reg[1] ) ) {
			return;
		}

		$thumbnail = $reg[1];

		if ( is_ssl() ) {
			$thumbnail = preg_replace( '|^http:|', 'https:', $thumbnail );
		}

		$response    = wp_remote_get( $thumbnail );
		$status_code = $this->_get_status_code( $response );

		if ( 200 != $status_code && 304 != $status_code ) {
			return;
		}

		return $thumbnail;
	}
}
