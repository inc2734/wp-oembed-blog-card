<?php
class OEmbed_Blog_Card_Cache_Test extends WP_UnitTestCase {

	public function setup() {
		parent::setup();

		$directory = \Inc2734\WP_OEmbed_Blog_Card\App\Model\Cache::rmdir();
	}

	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * @test
	 */
	public function no_cache() {
		$cache = \Inc2734\WP_OEmbed_Blog_Card\App\Model\Cache::get( 'https://2inc.org' );
		$this->assertFalse( $cache );
	}

	/**
	 * @test
	 */
	public function has_cache() {
		$refresh = \Inc2734\WP_OEmbed_Blog_Card\App\Model\Cache::refresh( 'https://2inc.org' );
		$cache   = \Inc2734\WP_OEmbed_Blog_Card\App\Model\Cache::get( 'https://2inc.org' );
		$this->assertEquals( 'モンキーレンチ', $cache['title'] );
	}

	/**
	 * Copy template to upload directory
	 *
	 * @param string $file_name
	 * @return string Uploaded template path
	 */
	protected function _create_page( $file_name ) {
		$template_path = __DIR__ . '/templates/' . $file_name;
		if ( ! file_exists( $template_path ) ) {
			throw new Exception( 'Test template not found. ' . $template_path );
		}

		return 'https://rawgit.com/inc2734/wp-oembed-blog-card/master/tests/templates/' . $file_name;
	}
}
