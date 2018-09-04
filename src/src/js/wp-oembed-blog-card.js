'use script';

jQuery(($) => {
  const cards = $('.js-wp-oembed-blog-card');

  cards.each((i, e) => {
    const card = $(e);

    $.get(WP_OEMBED_BLOG_CARD.endpoint, {
      action: WP_OEMBED_BLOG_CARD.action,
      url: card.find('.js-wp-oembed-blog-card__link').attr('href'),
    }, (response) => {
      if (response) {
        card.replaceWith(response);
      }
    });
  });
});
