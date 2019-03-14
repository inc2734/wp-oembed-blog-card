'use strict';

export class Card {
  constructor(card) {
    const query = {
      action: WP_OEMBED_BLOG_CARD.action,
      url: card.querySelector('.js-wp-oembed-blog-card__link').getAttribute('href'),
    };

    const queryString = Object.entries(query).map((e) => `${encodeURIComponent(e[0])}=${encodeURIComponent(e[1])}`).join('&');
    const url = `${WP_OEMBED_BLOG_CARD.endpoint}?${queryString}`;

    const xhr = new XMLHttpRequest();

    xhr.onreadystatechange = () => {
      if (4 === xhr.readyState) {
        if (200 === xhr.status || 304 === xhr.status) {
          card.innerHTML = xhr.responseText;
        } else {
          console.log(`Blog card request failed. HttpStatus: ${xhr.statusText}`);
        }
      }
    };

    xhr.open('GET', url);
    xhr.send();
  }
}
