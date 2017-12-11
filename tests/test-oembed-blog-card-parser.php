<?php
class OEmbed_Blog_Card_Parser_Test extends WP_UnitTestCase {

	public function setup() {
		parent::setup();
		include_once( __DIR__ . '/../src/app/model/parser.php' );
	}

	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * @test
	 */
	public function get_status_code() {
		$Parser = new Inc2734_WP_OEmbed_Blog_Card_Parser( home_url( '/404' ) );
		$this->assertEquals( 404, $Parser->get_status_code() );

		$Parser = new Inc2734_WP_OEmbed_Blog_Card_Parser( home_url( '/' ) );
		$this->assertEquals( 200, $Parser->get_status_code() );
	}

	/**
	 * @test
	 */
	public function get_title() {
		$uploaded_template_path = $this->_create_page( 'title.html' );
		$Parser = new Inc2734_WP_OEmbed_Blog_Card_Parser( $uploaded_template_path );
		$this->assertEquals( 'Title', $Parser->get_title() );

		$uploaded_template_path = $this->_create_page( 'ogp-title.html' );
		$Parser = new Inc2734_WP_OEmbed_Blog_Card_Parser( $uploaded_template_path );
		$this->assertEquals( 'OGP Title', $Parser->get_title() );
	}

	/**
	 * @test
	 */
	public function get_permalink() {
		$uploaded_template_path = $this->_create_page( 'ogp-url.html' );
		$Parser = new Inc2734_WP_OEmbed_Blog_Card_Parser( $uploaded_template_path );
		$this->assertEquals( 'http://example.org/ogp-url.html', $Parser->get_permalink() );
	}

	/**
	 * @test
	 */
	public function get_description() {
		$uploaded_template_path = $this->_create_page( 'description.html' );
		$Parser = new Inc2734_WP_OEmbed_Blog_Card_Parser( $uploaded_template_path );
		$this->assertEquals( 'Description', $Parser->get_description() );

		$uploaded_template_path = $this->_create_page( 'ogp-description.html' );
		$Parser = new Inc2734_WP_OEmbed_Blog_Card_Parser( $uploaded_template_path );
		$this->assertEquals( 'OGP Description', $Parser->get_description() );
	}

	/**
	 * @test
	 */
	public function get_domain() {
		$uploaded_template_path = $this->_create_page( 'ogp-url.html' );
		$Parser = new Inc2734_WP_OEmbed_Blog_Card_Parser( $uploaded_template_path );
		$this->assertEquals( 'example.org', $Parser->get_domain() );
	}

	/**
	 * @test
	 */
	public function get_favicon() {
		$uploaded_template_path = $this->_create_page( 'icon.html' );
		$Parser = new Inc2734_WP_OEmbed_Blog_Card_Parser( $uploaded_template_path );
		$this->assertNull( $Parser->get_favicon() ); // @todo http://example.org/favicon.ico not exist...

		$uploaded_template_path = $this->_create_page( 'shortcut-icon.html' );
		$Parser = new Inc2734_WP_OEmbed_Blog_Card_Parser( $uploaded_template_path );
		$this->assertNull( $Parser->get_favicon() ); // @todo http://example.org/favicon.ico not exist...
	}

	/**
	 * @test
	 */
	public function get_thumbnail() {
		$uploaded_template_path = $this->_create_page( 'ogp-image.html' );
		$Parser = new Inc2734_WP_OEmbed_Blog_Card_Parser( $uploaded_template_path );
		$this->assertNull( $Parser->get_thumbnail() ); // @todo http://example.org/favicon.ico not exist...
	}

	/**
	 * Copy template to upload directory
	 *
	 * @param string $file_name
	 * @return string Uploaded template path
	 */
	protected function _create_page( $file_name ) {
		$wp_upload_dir = wp_upload_dir();
		$upload_dir    = $wp_upload_dir['basedir'];

		$template_path = __DIR__ . '/templates/' . $file_name;
		if ( ! file_exists( $template_path ) ) {
			throw new Exception( 'Test template not found. ' . $template_path );
		}

		$data = file_get_contents( $template_path );
		$uploaded_template_path = $upload_dir . '/ogp-url.html';

		$is_created = file_put_contents( $uploaded_template_path, $data );
		if ( ! $is_created ) {
			throw new Exception( 'Test template can\'t be created. ' . $uploaded_template_path );
		}

		return $uploaded_template_path;
	}
}
