import forEachHtmlNodes from '@inc2734/for-each-html-nodes';
import { Card } from './module/_card.js';

const cards = document.querySelectorAll('.js-wp-oembed-blog-card');

let delay = 0;

forEachHtmlNodes(
  cards,
  (card) => {
    setTimeout(
      () => {
        const cardObj = new Card(card);
        cardObj.request();
      },
      delay
    );
    delay += 1000;
  }
);
