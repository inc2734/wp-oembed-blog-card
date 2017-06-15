<?php
$includes = array(
	'/app/model',
);
foreach ( $includes as $include ) {
	foreach ( glob( __DIR__ . $include . '/*.php' ) as $file ) {
		require_once( $file );
	}
}

class Inc2734_WP_oEmbed_Blog_Card {

	public function __construct() {
		$oembed    = _wp_oembed_get_object();
		$whitelist = array_keys( $oembed->providers );
		foreach ( $whitelist as $key => $value ) {
			$value = preg_replace( '@^#(.+)#i$@', '$1', $value );
			$whitelist[$key] = $value;
		}
		$regex = '@^(?!.*(' . join( '|', $whitelist ) . ')).*$@i';
		wp_embed_register_handler( 'wp_oembed_blog_card', $regex, array( $this, '_wp_embed_handler' ) );

		add_action( 'save_post', array( $this, '_save_post' ) );
		if ( has_filter( 'the_content', 'wpautop' ) ) {
			add_filter( 'the_content', array( $this, '_fix_wpautop' ) );
		}
		add_action( 'admin_enqueue_scripts', array( $this, '_admin_enqueue_scripts' ) );
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
			'@(<div class="c-blog-card"><a href=".+?" target="_blank">)</p>@',
			'$1',
			$content
		);

		$content = preg_replace(
			'@(<div class="c-blog-card__domain">.+?</div>\s*?)<p>(</a></div>)@',
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
		$custom = get_post_custom( $post_id );
		foreach ( $custom as $meta_key => $meta_value ) {
			if ( preg_match( '/^_wp_oembed_blog_card_/', $meta_key ) ) {
				delete_post_meta( $post_id, $meta_key );
			}
		}
	}

	/**
	 * Enqueue assets
	 *
	 * @return void
	 */
	public function _admin_enqueue_scripts() {
		$theme = wp_get_theme();
		wp_enqueue_style(
			get_stylesheet(),
			get_template_directory_uri() . '/assets/css/admin.min.css',
			array(),
			$theme->get( 'Version' )
		);
	}

	/**
	 * Return blog card template
	 *
	 * @param string $url
	 * @return string
	 */
	protected function _get_template( $url ) {
		global $post;

		if ( ! $url ) {
			return;
		}

		$cache = get_post_meta( $post->ID, $this->_get_meta_key( $url ), true );
		if ( ! $cache || ! is_array( $cache ) ) {
			$cache = array();
		}

		if ( ! $cache ) {
			$Parser = new Inc2734_WP_oEmbed_Blog_Card_Parser( $url );

			if ( '200' !== $Parser->get_status_code() && '301' !== $Parser->get_status_code() ) {
				if ( get_post_meta( $post->ID, $this->_get_meta_key( $url ), true ) ) {
					delete_post_meta( $post->ID, $this->_get_meta_key( $url ) );
				}
				return;
			}

			$cache['permalink']   = $Parser->get_permalink();
			$cache['thumbnail']   = $Parser->get_thumbnail();
			$cache['title']       = $Parser->get_title();
			$cache['description'] = $Parser->get_description();
			$cache['favicon']     = $Parser->get_favicon();
			$cache['domain']      = $Parser->get_domain();

			update_post_meta( $post->ID, $this->_get_meta_key( $url ), $cache );
		}

		ob_start();
		?>
		<div class="c-blog-card">
			<a href="<?php echo esc_url( $cache['permalink'] ); ?>" target="_blank">
				<?php if ( $cache['thumbnail'] ) : ?>
					<div class="c-blog-card__figure">
						<img src="<?php echo esc_url( $cache['thumbnail'] ); ?>" alt="">
					</div>
				<?php endif; ?>
				<div class="c-blog-card__body">
					<div class="c-blog-card__title">
						<?php echo esc_html( $cache['title'] ); ?>
					</div>
					<div class="c-blog-card__description">
						<?php
						if ( function_exists( 'mb_strimwidth' ) ) {
							echo esc_html( mb_strimwidth( $cache['description'], 0, 160, '…', 'utf-8' ) );
						} else {
							echo esc_html( $cache['description'] );
						}
						?>
					</div>
				</div>
				<div class="c-blog-card__domain">
					<?php if ( $cache['favicon'] ) : ?>
						<img class="c-blog-card__favicon" src="<?php echo esc_url( $cache['favicon'] ); ?>" alt="">
					<?php endif; ?>
					<?php echo esc_html( $cache['domain'] ); ?>
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
