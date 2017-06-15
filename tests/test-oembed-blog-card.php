<?php
class oEmbed_Blog_Card_Test extends WP_UnitTestCase {

	public function setup() {
		parent::setup();
		include_once( __DIR__ . '/../src/wp-oembed-blog-card.php' );
	}

	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * @test
	 */
	public function _wp_embed_handler() {
		$Blog_Card = new Inc2734_WP_oEmbed_Blog_Card();
		$template  = $Blog_Card->_wp_embed_handler( 'https://2inc.org', null, null, null );
		$this->assertSame( 0, preg_match( "/[\r\n]/", $template ) );
	}

	/**
	 * @test
	 */
	public function _save_post() {
		$post_id = $this->factory->post->create();
		update_post_meta( $post_id, '_wp_oembed_blog_card_', 'dummy' );
		$this->assertEquals( 'dummy', get_post_meta( $post_id, '_wp_oembed_blog_card_', true ) );

		$Blog_Card = new Inc2734_WP_oEmbed_Blog_Card();
		$Blog_Card->_save_post( $post_id );
		$this->assertEquals( '', get_post_meta( $post_id, '_wp_oembed_blog_card_', true ) );
	}
}
