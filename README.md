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
$Blog_Card = new Inc2734\WP_OEmbed_Blog_Card\Bootstrap();
```

Then just copy and paste the URL into the article!

- Data used for blog cards will be cached.
- The cache is updated when you open the post edit screen.
- On the display screen url is converted to blog card with ajax.
