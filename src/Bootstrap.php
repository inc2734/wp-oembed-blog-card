<?php
/**
 * @package inc2734/wp-oembed-blog-card
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Inc2734\WP_OEmbed_Blog_Card;

use Inc2734\WP_OEmbed_Blog_Card\App\Model\Parser;
use Inc2734\WP_OEmbed_Blog_Card\App\Setup;

class Bootstrap {

	public function __construct() {
		if ( $this->_is_admin_request() ) {
			return false;
		}

		if ( $this->_is_wordpress_request() ) {
			return false;
		}

		$oembed    = _wp_oembed_get_object();
		$whitelist = array_keys( $oembed->providers );
		foreach ( $whitelist as $key => $value ) {
			$value = preg_replace( '@^#(.+)#i$@', '$1', $value );
			$whitelist[ $key ] = $value;
		}
		$regex = '@^(?!.*(' . join( '|', $whitelist ) . ')).*$@i';
		wp_embed_register_handler( 'wp_oembed_blog_card', $regex, array( $this, '_wp_embed_handler' ) );

		add_action( 'wp_ajax_wp_oembed_blog_card_render', [ $this, '_wp_oembed_blog_card_render' ] );
		add_action( 'wp_ajax_nopriv_wp_oembed_blog_card_render', [ $this, '_wp_oembed_blog_card_render' ] );

		new Setup\Assets();
		new Setup\Gutenberg();
	}

	/**
	 * Embed handler for blog card
	 *
	 * @param array $matches
	 * @param array $attr
	 * @param string $url
	 * @param string $rawattr
	 * @return string
	 */
	public function _wp_embed_handler( $matches, $attr, $url, $rawattr ) {
		$cache = get_transient( $this->_get_meta_key( $url ) );
		if ( ! $cache || ! is_array( $cache ) ) {
			$cache = [];
		}

		/**
		 * $this->_delete_cache_infrequently( $cache, $url );
		 */

		if ( ! $cache || is_admin() ) {
			$parser = new Parser( $url );

			$cache['permalink']   = $parser->get_permalink();
			$cache['thumbnail']   = $parser->get_thumbnail();
			$cache['title']       = $parser->get_title();
			$cache['description'] = $parser->get_description();
			$cache['favicon']     = $parser->get_favicon();
			$cache['domain']      = $parser->get_domain();

			if ( empty( $cache['title'] ) ) {
				$expiration = HOUR_IN_SECONDS;
			} else {
				$expiration = YEAR_IN_SECONDS;
			}

			set_transient( $this->_get_meta_key( $url ), $cache, $expiration );
		}

		if ( ! is_admin() ) {
			$server = wp_unslash( $_SERVER );
			if ( isset( $server['REQUEST_URI'] ) && false !== strpos( $server['REQUEST_URI'], '/wp-json/oembed/1.0/proxy?url=' ) ) {
				return $this->_strip_newlines( $this->_get_gutenberg_template( $url ) );
			} else {
				return $this->_strip_newlines( $this->_get_default_template( $url ) );
			}
		} else {
			return $this->_strip_newlines( $this->_get_template( $url ) );
		}
	}

	/**
	 * Render template for gutenberg
	 *
	 * @param string $url
	 * @return string
	 */
	protected function _get_gutenberg_template( $url ) {
		$template = $this->_get_template( $url );
		$template = str_replace( '<a ', '<span ', $template );
		$template = str_replace( '</a>', '</span>', $template );

		// @codingStandardsIgnoreStart
		$template .= sprintf(
			'<link rel="stylesheet" href="%1$s">',
			esc_url_raw( get_template_directory_uri() . '/vendor/inc2734/wp-oembed-blog-card/src/assets/css/gutenberg-embed.min.css' )
		);
		$template .= sprintf(
			'<link rel="stylesheet" href="%1$s">',
			esc_url_raw( get_template_directory_uri() . '/vendor/inc2734/wp-oembed-blog-card/src/assets/css/app.min.css' )
		);
		// @codingStandardsIgnoreEnd

		return apply_filters( 'wp_oembed_blog_card_gutenberg_template', $template, $url );
	}

	/**
	 * Render default template used by shortcode
	 *
	 * @param string $url
	 * @return string
	 */
	protected function _get_default_template( $url ) {
		if ( ! $url ) {
			return;
		}

		if ( 0 === strpos( $url, home_url() ) ) {
			$target = '_self';
		} else {
			$target = '_blank';
		}
		return sprintf(
			'<div class="js-wp-oembed-blog-card">
				<a class="js-wp-oembed-blog-card__link" href="%1$s" target="%2$s">%1$s</a>
			</div>',
			esc_url( $url ),
			esc_attr( $target )
		);
	}

	/**
	 * Return blog card template
	 *
	 * @param string $url
	 * @return string
	 */
	protected function _get_template( $url ) {
		if ( ! $url ) {
			return;
		}

		$cache = get_transient( $this->_get_meta_key( $url ) );

		if ( $cache['title'] ) {
			return $this->_get_blog_card_template( $url, $cache );
		} else {
			return $this->_get_url_template( $url );
		}
	}

	/**
	 * Return url template
	 *
	 * @param string $url
	 * @return string
	 */
	protected function _get_url_template( $url ) {
		return apply_filters(
			'wp_oembed_blog_card_url_template',
			sprintf( '<p><a href="%1$s" target="_blank">%1$s</a></p>', $url ),
			$url
		);
	}

	/**
	 * Return blog card template
	 *
	 * @param string $url
	 * @param array $cache
	 * @return string
	 */
	protected function _get_blog_card_template( $url, $cache ) {
		if ( 0 === strpos( $url, home_url() ) ) {
			$target = '_self';
		} else {
			$target = '_blank';
		}

		ob_start();
		?>
		<div class="wp-oembed-blog-card">
			<a href="<?php echo esc_url( $url ); ?>" target="<?php echo esc_attr( $target ); ?>">
				<?php if ( $cache['thumbnail'] ) : ?>
					<div class="wp-oembed-blog-card__figure">
						<img src="<?php echo esc_url( $cache['thumbnail'] ); ?>" alt="">
					</div>
				<?php endif; ?>
				<div class="wp-oembed-blog-card__body">
					<div class="wp-oembed-blog-card__title">
						<?php echo esc_html( $cache['title'] ); ?>
					</div>
					<div class="wp-oembed-blog-card__description">
						<?php
						if ( function_exists( 'mb_strimwidth' ) ) {
							echo esc_html( mb_strimwidth( $cache['description'], 0, 160, '…', 'utf-8' ) );
						} else {
							echo esc_html( $cache['description'] );
						}
						?>
					</div>
					<div class="wp-oembed-blog-card__domain">
						<?php if ( $cache['favicon'] ) : ?>
							<img class="wp-oembed-blog-card__favicon" src="<?php echo esc_url( $cache['favicon'] ); ?>" alt="">
						<?php endif; ?>
						<?php echo esc_html( $cache['domain'] ); ?>
					</div>
				</div>
			</a>
		</div>
		<?php
		return apply_filters( 'wp_oembed_blog_card_blog_card_template', ob_get_clean(), $cache );
	}

	/**
	 * Render blog card with ajax
	 *
	 * @SuppressWarnings(PHPMD.ExitExpression)
	 * @return void
	 */
	public function _wp_oembed_blog_card_render() {
		if ( empty( $_GET['url'] ) ) {
			return;
		}

		header( 'Content-Type: text/html; charset=utf-8' );
		$url = esc_url_raw( wp_unslash( $_GET['url'] ) );
		echo wp_kses_post( $this->_strip_newlines( $this->_get_template( $url ) ) );
		die();
	}

	/**
	 * Get post meta key for blog card
	 *
	 * @see https://qiita.com/koriym/items/efc1c419e4b7772b65c0
	 * @param string $url
	 * @return string
	 */
	protected function _get_meta_key( $url ) {
		$hash = base64_encode( pack( 'H*', sha1( $url ) ) );
		return '_wpoembc_' . $hash;
	}

	/**
	 * Delete cache infrequently
	 *
	 * @param array $cache
	 * @param string $url
	 * @return boolean
	 */
	protected function _delete_cache_infrequently( $cache, $url ) {
		if ( $cache && empty( $cache['title'] ) && 1 > rand( 1, 100 ) ) {
			delete_transient( $this->_get_meta_key( $url ) );
			return true;
		}
		return false;
	}

	/**
	 * Remove newlines
	 *
	 * @param string $string
	 * @return string
	 */
	protected function _strip_newlines( $string ) {
		return str_replace( array( "\r", "\n", "\t" ), '', $string );
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
			if ( false === strpos( $request_uri, 'action=wp_oembed_blog_card_render' ) ) {
				return true;
			}
		}

		if ( false !== strpos( $request_uri, '/wp-cron.php' ) ) {
			return true;
		}

		if ( false !== strpos( $request_uri, '/wp-json/' ) ) {
			if ( false === strpos( $request_uri, '/wp-json/oembed/' ) ) {
				return true;
			}
		}

		return false;
	}
}
