<?php
class OEmbed_Blog_Card_Cache_Test extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
	}

	public function tear_down() {
		parent::tear_down();
	}

	public static function set_transient_cache_object() {
		return '\Inc2734\WP_OEmbed_Blog_Card\App\Model\TransientCache';
	}

	/**
	 * @test
	 */
	public function no_cache() {
		$cache = \Inc2734\WP_OEmbed_Blog_Card\App\Model\Cache::get( 'https://2inc.org' );
		$this->assertFalse( $cache );

		add_filter( 'inc2734_wp_oembed_blog_card_cache_object', [ '\OEmbed_Blog_Card_Cache_Test', 'set_transient_cache_object' ] );
		$cache = \Inc2734\WP_OEmbed_Blog_Card\App\Model\Cache::get( 'https://2inc.org' );
		$this->assertFalse( $cache );
		remove_filter( 'inc2734_wp_oembed_blog_card_cache_object', [ '\OEmbed_Blog_Card_Cache_Test', 'set_transient_cache_object' ] );
	}

	/**
	 * @test
	 */
	public function has_cache() {
		$parser = new \Inc2734\WP_OEmbed_Blog_Card\App\Model\Parser( 'https://2inc.org' );

		$cache = [
			'title' => $parser->get_title(),
		];

		$refresh = \Inc2734\WP_OEmbed_Blog_Card\App\Model\Cache::refresh( 'https://2inc.org', $cache );
		$cache   = \Inc2734\WP_OEmbed_Blog_Card\App\Model\Cache::get( 'https://2inc.org' );
		$this->assertEquals( 'モンキーレンチ', $cache['title'] );

		add_filter( 'inc2734_wp_oembed_blog_card_cache_object', [ '\OEmbed_Blog_Card_Cache_Test', 'set_transient_cache_object' ] );
		$refresh = \Inc2734\WP_OEmbed_Blog_Card\App\Model\Cache::refresh( 'https://2inc.org', $cache );
		$cache   = \Inc2734\WP_OEmbed_Blog_Card\App\Model\Cache::get( 'https://2inc.org' );
		$this->assertEquals( 'モンキーレンチ', $cache['title'] );
		remove_filter( 'inc2734_wp_oembed_blog_card_cache_object', [ '\OEmbed_Blog_Card_Cache_Test', 'set_transient_cache_object' ] );
	}
}
