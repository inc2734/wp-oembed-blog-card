<?php
/**
 * @package inc2734/wp-oembed-blog-card
 * @author inc2734
 * @license GPL-2.0+
 */

class Inc2734_WP_oEmbed_Blog_Card_Parser {

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
		if ( wp_http_validate_url( $url ) ) {
			if ( is_array( $response ) && isset( $response['body' ] ) ) {
				$this->content = $response['body'];
			}

			if ( is_array( $response ) && isset( $response['response'] ) ) {
				if ( is_array( $response['response'] ) && isset( $response['response']['code'] ) ) {
					$this->status_code = $response['response']['code'];
				}
			}
		} else {
			if ( WP_Filesystem() ) {
				global $wp_filesystem;
				$this->content = $wp_filesystem->get_contents( $url );
				if ( $this->content ) {
					$this->status_code = 200;
				}
			}
		}

		if ( $this->content ) {
			if ( function_exists( 'mb_convert_encoding' ) && $this->content ) {
				foreach( array( 'UTF-8', 'SJIS', 'EUC-JP', 'ASCII', 'JIS' ) as $encode ) {
					$encoded_content = mb_convert_encoding( $this->content, $encode, $encode );
					if ( strcmp( $this->content, $encoded_content ) === 0 ) {
						$from_encode = $encode;
						break;
					}
				}
				if ( ! empty( $from_encode ) ) {
					$this->content = mb_convert_encoding( $this->content, get_bloginfo( 'charset' ), $from_encode );
				}
			}
		}

		if ( ! $this->status_code ) {
			$this->status_code = '404';
		}
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
		if ( ! empty( $reg[1] ) ) {
			return $reg[1];
		}

		preg_match( '/<link +?rel=["\']icon["\'][^\/>]*? href=["\']([^"\']+?)["\'][^\/>]*?\/?>/si', $this->content, $reg );
		if ( ! empty( $reg[1] ) ) {
			return $reg[1];
		}
	}

	/**
	 * Return thumbnail of the page you want to blog card
	 *
	 * @return string
	 */
	public function get_thumbnail() {
		preg_match( '/<meta +?property=["\']og:image["\'][^\/>]*? content=["\']([^"\']+?)["\'].*?\/?>/si', $this->content, $reg );
		if ( ! empty( $reg[1] ) ) {
			return $reg[1];
		}
	}
}
