<?php
/**
 * @package inc2734/wp-oembed-blog-card
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Inc2734\WP_OEmbed_Blog_Card\App\Model;

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
	 * Constructor.

	 * @param string $url Target URL.
	 */
	public function __construct( $url ) {
		$this->url = $url;

		$requester = new Requester( $this->url );
		$response  = $requester->request();
		if ( is_wp_error( $response ) ) {
			return;
		}

		$this->status_code = $requester->get_status_code();
		if ( 200 !== $this->get_status_code() && 304 !== $this->get_status_code() ) {
			return;
		}

		if ( wp_http_validate_url( $this->url ) ) {
			$this->content_type = $requester->get_content_type();
		} else {
			$this->content_type = mime_content_type( $this->url );
		}
		if ( ! preg_match( '/text\/html/', $this->get_content_type() ) ) {
			return;
		}

		$content = $requester->get_content();

		$this->title       = $this->_get_title( $content );
		$this->permalink   = $this->_get_permalink( $content );
		$this->description = $this->_get_description( $content );
		$this->domain      = $this->_get_domain( $content );
		$this->favicon     = $this->_get_favicon( $content );
		$this->thumbnail   = $this->_get_thumbnail( $content );
	}

	/**
	 * Return page title of the page you want to blog card.
	 *
	 * @param string $content Content for title extraction.
	 * @return string
	 */
	protected function _get_title( $content ) {
		preg_match( '/<meta [^>]*?property=["\']og:title["\'][^\/>]*? content=["\']([^"\']+?)["\'].*?\/?>/si', $content, $reg );
		if ( ! empty( $reg[1] ) ) {
			return $reg[1];
		}

		preg_match( '/<title[^>]*?>([^"\']+?)<\/title>/si', $content, $reg );
		if ( ! empty( $reg[1] ) ) {
			return $reg[1];
		}
	}

	/**
	 * Return URL of the page you want to blog card.
	 *
	 * @param string $content Content for permalink extraction.
	 * @return string
	 */
	protected function _get_permalink( $content ) {
		preg_match( '/<meta [^>]*?property=["\']og:url["\'][^\/>]*? content=["\']([^"\']+?)["\'].*?\/?>/si', $content, $reg );
		if ( ! empty( $reg[1] ) ) {
			return $reg[1];
		}

		return $this->url;
	}

	/**
	 * Return page description of the page you want to blog card.
	 *
	 * @param string $content Content for description extraction.
	 * @return string
	 */
	protected function _get_description( $content ) {
		preg_match( '/<meta [^>]*?property=["\']og:description["\'][^\/>]*? content=["\']([^"\']+?)["\'].*?\/?>/si', $content, $reg );
		if ( ! empty( $reg[1] ) ) {
			return $reg[1];
		}

		preg_match( '/<meta [^>]*?name=["\']description["\'][^\/>]*? content=["\']([^"\']+?)["\'].*?\/?>/si', $content, $reg );
		if ( ! empty( $reg[1] ) ) {
			return $reg[1];
		}
	}

	/**
	 * Return domain of the page you want to blog card.
	 *
	 * @param string $content Content for domain extraction.
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
	 * Return favicon of the page you want to blog card.
	 *
	 * @param string $content Content for favicon extraction.
	 * @return string
	 */
	protected function _get_favicon( $content ) {
		preg_match( '/<link [^>]*?rel=["\']shortcut icon["\'][^\/>]*? href=["\']([^"\']+?)["\'][^\/>]*?\/?>/si', $content, $reg );
		if ( empty( $reg[1] ) ) {
			preg_match( '/<link [^>]*?rel=["\']icon["\'][^\/>]*? href=["\']([^"\']+?)["\'][^\/>]*?\/?>/si', $content, $reg );
		}

		if ( empty( $reg[1] ) ) {
			return;
		}

		$favicon = $reg[1];
		$favicon = $this->_relative_path_to_url( $favicon, $content );

		if ( is_ssl() ) {
			$favicon = preg_replace( '|^http:|', 'https:', $favicon );
		}

		$requester = new Requester( $favicon );
		$response  = $requester->request();
		if ( is_wp_error( $response ) ) {
			return;
		}

		$status_code = $requester->get_status_code();
		if ( 200 !== $status_code && 304 !== $status_code ) {
			return;
		}

		return $favicon;
	}

	/**
	 * Return thumbnail of the page you want to blog card.
	 *
	 * @param string $content Content for thumbnail extraction.
	 * @return string
	 */
	protected function _get_thumbnail( $content ) {
		preg_match( '/<meta [^>]*?property=["\']og:image["\'][^\/>]*? content=["\']([^"\']+?)["\'].*?\/?>/si', $content, $reg );
		if ( empty( $reg[1] ) ) {
			return;
		}

		$thumbnail = $reg[1];
		$thumbnail = $this->_relative_path_to_url( $thumbnail, $content );

		if ( is_ssl() ) {
			$thumbnail = preg_replace( '|^http:|', 'https:', $thumbnail );
		}

		$requester = new Requester( $thumbnail );
		$response  = $requester->request();
		if ( is_wp_error( $response ) ) {
			return;
		}

		$status_code = $requester->get_status_code();
		if ( 200 !== $status_code && 304 !== $status_code ) {
			return;
		}

		return $thumbnail;
	}

	/**
	 * Return url that converted from relative path.
	 *
	 * @param string $path    The file path to be converted to a URL.
	 * @param string $content Content for URL extraction.
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
	 * Return status code of the page you want to blog card.
	 *
	 * @return int
	 */
	public function get_status_code() {
		return (int) $this->status_code;
	}

	/**
	 * Return content type of the page you want to blog card.
	 *
	 * @return int
	 */
	public function get_content_type() {
		return strtolower( $this->content_type );
	}

	/**
	 * Return page title of the page you want to blog card.
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Return URL of the page you want to blog card.
	 *
	 * @return string
	 */
	public function get_permalink() {
		return $this->permalink;
	}

	/**
	 * Return page description of the page you want to blog card.
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Return domain of the page you want to blog card.
	 *
	 * @return string
	 */
	public function get_domain() {
		return $this->domain;
	}

	/**
	 * Return favicon of the page you want to blog card.
	 *
	 * @return string
	 */
	public function get_favicon() {
		return $this->favicon;
	}

	/**
	 * Return thumbnail of the page you want to blog card.
	 *
	 * @return string
	 */
	public function get_thumbnail() {
		return $this->thumbnail;
	}
}
