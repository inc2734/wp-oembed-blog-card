<?php
/**
 * @package inc2734/wp-oembed-blog-card
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Inc2734\WP_OEmbed_Blog_Card;

use Inc2734\WP_OEmbed_Blog_Card\App\Model\Parser;

class OEmbed_Blog_Card {

	public function __construct() {
		if ( isset( $_SERVER['REMOTE_ADDR'] ) && isset( $_SERVER['SERVER_ADDR'] ) && $_SERVER['REMOTE_ADDR'] === $_SERVER['SERVER_ADDR'] ) {
			return;
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
		add_action( 'wp_enqueue_scripts', [ $this, '_enqueue_scripts' ] );
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

		$this->_delete_cache_infrequently( $cache, $url );

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
			return $this->_strip_newlines( $this->_get_default_template( $url ) );
		} else {
			return $this->_strip_newlines( $this->_get_template( $url ) );
		}
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
			return $this->_get_blog_card_template( $cache );
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
		return sprintf( '<p><a href="%1$s" target="_blank">%1$s</a></p>', $url );
	}

	/**
	 * Return blog card template
	 *
	 * @return string
	 */
	protected function _get_blog_card_template( $cache ) {
		if ( 0 === strpos( $cache['permalink'], home_url() ) ) {
			$target = '_self';
		} else {
			$target = '_blank';
		}

		ob_start();
		?>
		<div class="wp-oembed-blog-card">
			<a href="<?php echo esc_url( $cache['permalink'] ); ?>" target="<?php echo esc_attr( $target ); ?>">
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
		return ob_get_clean();
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
	 * Enqueue scripts
	 *
	 * @return void
	 */
	public function _enqueue_scripts() {
		$relative_path = '/vendor/inc2734/wp-oembed-blog-card/src/assets/js/wp-oembed-blog-card.js';
		$src  = get_template_directory_uri() . $relative_path;
		$path = get_template_directory() . $relative_path;

		if ( ! file_exists( $path ) ) {
			return;
		}

		wp_enqueue_script(
			'wp-oembed-blog-card',
			$src,
			[ 'jquery' ],
			filemtime( $path ),
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
	 * @return void
	 */
	protected function _delete_cache_infrequently( $cache, $url ) {
		if ( $cache && empty( $cache['title'] ) && 3 < rand( 1, 10 ) ) {
			delete_transient( $this->_get_meta_key( $url ) );
		}
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
}
