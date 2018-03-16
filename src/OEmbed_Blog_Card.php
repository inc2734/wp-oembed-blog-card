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

		add_action( 'save_post', array( $this, '_save_post' ) );
		if ( has_filter( 'the_content', 'wpautop' ) ) {
			add_filter( 'the_content', array( $this, '_fix_wpautop' ) );
		}
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
		return $this->_strip_newlines( $this->_get_template( $url ) );
	}

	/**
	 * Fix wpautop()
	 *
	 * @param string $content
	 * @return string
	 */
	public function _fix_wpautop( $content ) {
		$content = preg_replace(
			'@(<div class="wp-oembed-blog-card"><a href=".+?" target=".+?">)</p>@',
			'$1',
			$content
		);

		$content = preg_replace(
			'@(<div class="wp-oembed-blog-card__domain">.+?</div>\s*?)<p>(</a></div>)@',
			'$1$2',
			$content
		);

		return $content;
	}

	/**
	 * Remove blog card cache when post saving
	 *
	 * @param int $post_id
	 * @return void
	 */
	public function _save_post( $post_id ) {
		$custom    = get_post_custom( $post_id );
		$meta_keys = array_keys( $custom );
		foreach ( $meta_keys as $meta_key ) {
			if ( preg_match( '/^_wp_oembed_blog_card_/', $meta_key ) ) {
				delete_post_meta( $post_id, $meta_key );
			}
		}
	}

	/**
	 * Return blog card template
	 *
	 * @param string $url
	 * @return string
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 */
	protected function _get_template( $url ) {
		global $post;

		if ( ! isset( $post->ID ) ) {
			return;
		}

		if ( ! $url ) {
			return;
		}

		$cache = get_post_meta( $post->ID, $this->_get_meta_key( $url ), true );
		if ( ! $cache || ! is_array( $cache ) ) {
			$cache = array();
		}

		if ( ! $cache ) {
			$parser = new Parser( $url );

			if ( 200 != $parser->get_status_code() && 304 != $parser->get_status_code() ) {
				if ( get_post_meta( $post->ID, $this->_get_meta_key( $url ), true ) ) {
					delete_post_meta( $post->ID, $this->_get_meta_key( $url ) );
				}
				return;
			}

			$cache['permalink']   = $parser->get_permalink();
			$cache['thumbnail']   = $parser->get_thumbnail();
			$cache['title']       = $parser->get_title();
			$cache['description'] = $parser->get_description();
			$cache['favicon']     = $parser->get_favicon();
			$cache['domain']      = $parser->get_domain();

			update_post_meta( $post->ID, $this->_get_meta_key( $url ), $cache );
		}

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
					<div class="wp-oembed-blog-card__figure" style="background-image: url(<?php echo esc_url( $cache['thumbnail'] ); ?>)">
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
	 * Get post meta key for blog card
	 *
	 * @param string $url
	 * @return string
	 */
	protected function _get_meta_key( $url ) {
		return '_wp_oembed_blog_card_' . urlencode( $url );
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
