# WP oEmbed Blog Card

[![Build Status](https://travis-ci.org/inc2734/wp-oembed-blog-card.svg?branch=master)](https://travis-ci.org/inc2734/wp-oembed-blog-card)
[![Latest Stable Version](https://poser.pugx.org/inc2734/wp-oembed-blog-card/v/stable)](https://packagist.org/packages/inc2734/wp-oembed-blog-card)
[![License](https://poser.pugx.org/inc2734/wp-oembed-blog-card/license)](https://packagist.org/packages/inc2734/wp-oembed-blog-card)

## Install
```
$ composer require inc2734/wp-oembed-blog-card
```

## How to use
```
<?php
// When Using composer auto loader
$Blog_Card = new Inc2734\WP_OEmbed_Blog_Card\OEmbed_Blog_Card();

// When not Using composer auto loader
// include_once( get_theme_file_path( '/vendor/inc2734/wp-oembed-blog-card/src/wp-oembed-blog-card.php' ) );
// $Blog_Card = new Inc2734_WP_OEmbed_Blog_Card();
```

```
// Using default styles (.scss)
@import 'vendor/inc2734/wp-oembed-blog-card/src/assets/scss/wp-oembed-blog-card';
```

Then just copy and paste the URL into the article!
