'use strict';

import forEachHtmlNodes from '@inc2734/for-each-html-nodes';
import {Card} from './module/_card.js';

const cards = document.querySelectorAll('.js-wp-oembed-blog-card');

if (typeof IntersectionObserver !== 'undefined') {
  const observer = new IntersectionObserver(
    (entries, object) => {
      entries.forEach(
        (entry, i) => {
          if (! entry.isIntersecting) {
            return;
          }

          new Card(entry.target);
          object.unobserve(entry.target);
        }
      );
    },
    {
      rootMargin: '100px',
    }
  );

  forEachHtmlNodes(cards, (card) => observer.observe(card));
} else {
  forEachHtmlNodes(cards, (card) => new Card(card));
}
