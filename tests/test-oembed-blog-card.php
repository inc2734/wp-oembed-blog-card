<?php
class OEmbed_Blog_Card_Test extends WP_UnitTestCase {

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
			'<p><iframe loading="lazy" title="Snow Monkey ミートアップ 〜Snow Monkey について考える会〜" width="500" height="281" src="https://www.youtube.com/embed/s2tDDSBAHsQ?feature=oembed" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></p>',
			trim( apply_filters( 'the_content', '[embed]https://www.youtube.com/watch?v=s2tDDSBAHsQ[/embed]' ) )
		);
	}
}
