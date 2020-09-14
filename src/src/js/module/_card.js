'use strict';

export class Card {
  constructor(card) {
    this.card = card;
    this.query = {
      url: card.querySelector('.js-wp-oembed-blog-card__link').getAttribute('href'),
    };
  }

  request() {
    const queryString = `url=${encodeURIComponent(this.query.url)}`;
    const url = -1 === WP_OEMBED_BLOG_CARD.endpoint.indexOf( '?' )
      ? `${WP_OEMBED_BLOG_CARD.endpoint}/response/?${queryString}`
      : `${WP_OEMBED_BLOG_CARD.endpoint}/response/&${queryString}`;
    const xhr = new XMLHttpRequest();

    xhr.onreadystatechange = () => {
      if (4 === xhr.readyState) {
        if (200 === xhr.status || 304 === xhr.status) {
          this.card.outerHTML = xhr.responseText;
        } else {
          console.log(`Blog card request failed. HttpStatus: ${xhr.statusText}`);
        }
      }
    };

    xhr.open('GET', url);
    xhr.send();
  }
}
