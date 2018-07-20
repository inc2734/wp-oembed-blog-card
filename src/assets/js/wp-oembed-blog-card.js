jQuery(function($) {
  var cards = $('.js-wp-oembed-blog-card');
  cards.each(function(i, e) {
    var card = $(e);
    $.get(WP_OEMBED_BLOG_CARD.endpoint, {
      action: WP_OEMBED_BLOG_CARD.action,
      url: card.find('.js-wp-oembed-blog-card__link').attr('href')
    }, function(response) {
      if (response) {
        card.replaceWith(response);
      }
    });
  });
});
