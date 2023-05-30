<?php
class OEmbed_Blog_Card_File_Cache_Test extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();

		$directory = \Inc2734\WP_OEmbed_Blog_Card\App\Model\FileCache::_rmdir();
	}

	public function tear_down() {
		parent::tear_down();
	}

	/**
	 * @test
	 */
	public function no_cache() {
		$cache = \Inc2734\WP_OEmbed_Blog_Card\App\Model\FileCache::get( 'https://2inc.org' );
		$this->assertFalse( $cache );
	}

	/**
	 * @test
	 */
	public function has_cache() {
		$parser = new \Inc2734\WP_OEmbed_Blog_Card\App\Model\Parser( 'https://2inc.org' );

		$cache = [
			'title' => $parser->get_title(),
		];

		$refresh = \Inc2734\WP_OEmbed_Blog_Card\App\Model\FileCache::refresh( 'https://2inc.org', $cache );
		$cache   = \Inc2734\WP_OEmbed_Blog_Card\App\Model\FileCache::get( 'https://2inc.org' );
		$this->assertEquals( 'モンキーレンチ', $cache['title'] );
	}
}
