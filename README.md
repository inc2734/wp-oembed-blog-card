# WP oEmbed Blog Card

![CI](https://github.com/inc2734/wp-oembed-blog-card/workflows/CI/badge.svg)
[![Latest Stable Version](https://poser.pugx.org/inc2734/wp-oembed-blog-card/v/stable)](https://packagist.org/packages/inc2734/wp-oembed-blog-card)
[![License](https://poser.pugx.org/inc2734/wp-oembed-blog-card/license)](https://packagist.org/packages/inc2734/wp-oembed-blog-card)

## Install
```
$ composer require inc2734/wp-oembed-blog-card
```

## How to use
```
<?php
new \Inc2734\WP_OEmbed_Blog_Card\Bootstrap();
```

Then just copy and paste the URL into the article!

- Data used for blog cards will be cached.
- The cache is updated when you open the post edit screen.
- On the display screen url is converted to blog card with ajax.

## Filter hooks
### inc2734_wp_oembed_blog_card_block_editor_template
```
/**
 * Customize template for block editor
 *
 * @param string $template
 * @param string $url
 * @return string
 */
add_filter(
	'inc2734_wp_oembed_blog_card_block_editor_template',
	function( $template, $url ) {
		return $template;
	},
	10,
	2
);
```

### inc2734_wp_oembed_blog_card_loading_template
```
/**
 * Customize template for loading
 *
 * @param string $template
 * @param string $url
 * @return string
 */
add_filter(
	'inc2734_wp_oembed_blog_card_loading_template',
	function( $template, $url ) {
		return $template;
	},
	10,
	2
);
```

### inc2734_wp_oembed_blog_card_url_template
```
/**
 * Customize url template
 *
 * @param string $template
 * @param string $url
 * @return string
 */
add_filter(
	'inc2734_wp_oembed_blog_card_url_template',
	function( $template, $url ) {
		return $template;
	},
	10,
	2
);
```

### inc2734_wp_oembed_blog_card_blog_card_template

```
/**
 * Customize blog card template
 *
 * @param string $template
 * @param array $cache
 * @return string
 */
add_filter(
	'inc2734_wp_oembed_blog_card_blog_card_template',
	function( $template, $cache ) {
		return $template;
	},
	10,
	2
);
```

### inc2734_wp_oembed_blog_card_cache_directory

```
/**
 * Customize cache directory
 *
 * @param string $directory
 * @return string
 */
add_filter(
	'inc2734_wp_oembed_blog_card_cache_directory',
	function( $directory ) {
		return $directory;
	}
);
```
