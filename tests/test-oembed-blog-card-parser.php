<?php
class OEmbed_Blog_Card_Parser_Test extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
	}

	public function tear_down() {
		parent::tear_down();
	}

	/**
	 * @test
	 */
	public function get_content_type() {
		$Parser = new Inc2734\WP_OEmbed_Blog_Card\App\Model\Parser( 'https://github.com/inc2734/wp-oembed-blog-card' );
		$this->assertEquals( 'text/html; charset=utf-8', $Parser->get_content_type() );
	}

	/**
	 * @test
	 */
	public function get_title() {
		$uploaded_template_path = $this->_create_page( 'title.html' );
		$Parser = new Inc2734\WP_OEmbed_Blog_Card\App\Model\Parser( $uploaded_template_path );
		$this->assertEquals( 'Title', $Parser->get_title() );

		$uploaded_template_path = $this->_create_page( 'ogp-title.html' );
		$Parser = new Inc2734\WP_OEmbed_Blog_Card\App\Model\Parser( $uploaded_template_path );
		$this->assertEquals( 'OGP Title', $Parser->get_title() );
	}

	/**
	 * @test
	 */
	public function get_permalink() {
		$uploaded_template_path = $this->_create_page( 'ogp-url.html' );
		$Parser = new Inc2734\WP_OEmbed_Blog_Card\App\Model\Parser( $uploaded_template_path );
		$this->assertEquals( 'http://example.org/ogp-url.html', $Parser->get_permalink() );
	}

	/**
	 * @test
	 */
	public function get_description() {
		$uploaded_template_path = $this->_create_page( 'description.html' );
		$Parser = new Inc2734\WP_OEmbed_Blog_Card\App\Model\Parser( $uploaded_template_path );
		$this->assertEquals( 'Description', $Parser->get_description() );

		$uploaded_template_path = $this->_create_page( 'ogp-description.html' );
		$Parser = new Inc2734\WP_OEmbed_Blog_Card\App\Model\Parser( $uploaded_template_path );
		$this->assertEquals( 'OGP Description', $Parser->get_description() );
	}

	/**
	 * @test
	 */
	public function get_domain() {
		$uploaded_template_path = $this->_create_page( 'ogp-url.html' );
		$Parser = new Inc2734\WP_OEmbed_Blog_Card\App\Model\Parser( $uploaded_template_path );
		$this->assertEquals( 'example.org', $Parser->get_domain() );
	}

	/**
	 * @test
	 */
	public function get_favicon() {
		$uploaded_template_path = $this->_create_page( 'icon.html' );
		$Parser = new Inc2734\WP_OEmbed_Blog_Card\App\Model\Parser( $uploaded_template_path );
		$this->assertEquals( 'https://rawgit.com/favicon.ico', $Parser->get_favicon() );

		$uploaded_template_path = $this->_create_page( 'shortcut-icon.html' );
		$Parser = new Inc2734\WP_OEmbed_Blog_Card\App\Model\Parser( $uploaded_template_path );
		$this->assertEquals( 'https://rawgit.com/favicon.ico', $Parser->get_favicon() );
	}

	/**
	 * @test
	 */
	public function get_thumbnail() {
		$uploaded_template_path = $this->_create_page( 'ogp-image.html' );
		$Parser = new Inc2734\WP_OEmbed_Blog_Card\App\Model\Parser( $uploaded_template_path );
		$this->assertNull( $Parser->get_thumbnail() ); // @todo http://example.org/thumb.jpg not exist...
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
