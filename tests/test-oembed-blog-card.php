<?php
class OEmbed_Blog_Card_Test extends WP_UnitTestCase {

	public function setup() {
		parent::setup();
	}

	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * @test
	 */
	public function _wp_embed_handler() {
		$Blog_Card = new Inc2734\WP_OEmbed_Blog_Card\OEmbed_Blog_Card();
		$template  = $Blog_Card->_wp_embed_handler( 'https://2inc.org', null, null, null );
		$this->assertSame( 0, preg_match( "/[\r\n]/", $template ) );
	}
}
