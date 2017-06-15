<?php
namespace Inc2734\WP_oEmbed_Blog_Card;

class oEmbed_Blog_Card {

	public function __construct() {
		include_once( __DIR__ . '/wp-oembed-blog-card.php' );
		new \Inc2734_WP_oEmbed_Blog_Card();
	}
}
