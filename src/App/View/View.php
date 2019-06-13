<?php
/**
 * @package inc2734/wp-oembed-blog-card
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Inc2734\WP_OEmbed_Blog_Card\App\View;

use Inc2734\WP_OEmbed_Blog_Card\App\Model\Cache;

class View {

	/**
	 * Render template for block editor
	 *
	 * @param string $url
	 * @return string
	 */
	public static function get_block_template( $url ) {
		$template = static::get_template( $url );
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

		return static::_strip_newlines( apply_filters( 'wp_oembed_blog_card_gutenberg_template', $template, $url ) );
	}

	/**
	 * Render pre blog card template
	 *
	 * @param string $url
	 * @return string
	 */
	public static function get_pre_blog_card_template( $url ) {
		if ( ! $url ) {
			return;
		}

		if ( 0 === strpos( $url, home_url() ) ) {
			$target = '_self';
		} else {
			$target = '_blank';
		}

		return static::_strip_newlines(
			apply_filters(
				'wp_oembed_blog_card_loading_template',
				sprintf(
					'<div class="js-wp-oembed-blog-card">
						<a class="js-wp-oembed-blog-card__link" href="%1$s" target="%2$s">%1$s</a>
					</div>',
					esc_url( $url ),
					esc_attr( $target )
				),
				$url
			)
		);
	}

	/**
	 * Return blog card template
	 *
	 * @param string $url
	 * @return string
	 */
	public static function get_template( $url ) {
		if ( ! $url ) {
			return;
		}

		$cache = Cache::get( $url );

		return static::_strip_newlines(
			! empty( $cache['title'] )
				? static::get_blog_card_template( $url, $cache )
				: static::get_url_template( $url )
		);
	}

	/**
	 * Return url template
	 *
	 * @param string $url
	 * @return string
	 */
	public static function get_url_template( $url ) {
		return static::_strip_newlines(
			apply_filters(
				'wp_oembed_blog_card_url_template',
				sprintf(
					'<p class="wp-oembed-blog-card-url-template">
						<a href="%1$s" target="_blank">%1$s</a>
					</p>',
					esc_url( $url )
				),
				$url
			)
		);
	}

	/**
	 * Return blog card template
	 *
	 * @param string $url
	 * @param array $cache
	 * @return string
	 */
	public static function get_blog_card_template( $url, $cache ) {
		if ( 0 === strpos( $url, home_url() ) ) {
			$target = '_self';
		} else {
			$target = '_blank';
		}

		$cached_time = isset( $cache['cached_time'] ) ? date_i18n( 'd/m/y H:i:s', $cache['cached_time'] ) : null;

		ob_start();
		?>
		<div class="wp-oembed-blog-card" data-cached-time="<?php echo esc_attr( $cached_time ); ?>">
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
		return static::_strip_newlines( apply_filters( 'wp_oembed_blog_card_blog_card_template', ob_get_clean(), $cache ) );
	}

	/**
	 * Remove newlines
	 *
	 * @param string $string
	 * @return string
	 */
	protected static function _strip_newlines( $string ) {
		return str_replace( array( "\r", "\n", "\t" ), '', $string );
	}
}
