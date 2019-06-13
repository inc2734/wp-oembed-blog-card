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
	public function wordpress_site_link() {
		new Inc2734\WP_OEmbed_Blog_Card\Bootstrap();

		$this->assertEquals(
			'<div class="js-wp-oembed-blog-card"><a class="js-wp-oembed-blog-card__link" href="https://2inc.org" target="_blank">https://2inc.org</a></div>',
			trim( apply_filters( 'the_content', '[embed]https://2inc.org[/embed]' ) )
		);
	}

	/**
	 * @test
	 */
	public function youtube_link() {
		new Inc2734\WP_OEmbed_Blog_Card\Bootstrap();

		$this->assertEquals(
			'<p><iframe title="【1-1】WP Multibyte Patch プラグインの有効化 / WordPress の推奨設定 - WordPress テーマ Snow Monkey の使い方" width="500" height="281" src="https://www.youtube.com/embed/X2vhH4slaJA?feature=oembed" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></p>',
			trim( apply_filters( 'the_content', '[embed]https://www.youtube.com/watch?v=X2vhH4slaJA[/embed]' ) )
		);
	}
}
