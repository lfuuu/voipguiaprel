'use strict';

import fetchData from './dataHandler';
import nodeCard from './nodeCard';

// setInterval(() => {
//   dataHandler();
// }, 3000);
let eridanusData;
fetchData().then((response) => {
  eridanusData = response;
  console.log(eridanusData)
});

// render elements with reactjs
require('./healthyBar.js');

// --- dev helpers

$(document).on('keypress', function (e) {
  var keyPressed = e.which || e.keyCode || 0;

  if (keyPressed === 102) {
    $('.foreground-cards').toggleClass('fg-hidden');
  }
});

// --- dev helpers

var healthyCarousel = function () {

  var nodesArray = itemsContainer.children().toArray().reverse(),
      fullItemHeight = itemHeight + itemMargin * 2,
      j = 0,
      carousel = function (negativeDirection) {

        var currentOffset = +(itemsContainer.css('height').slice(0, -2)),
            newOffset = currentOffset + 1 + (-2) * negativeDirection *0;

        // console.log(newOffset)

        itemsContainer.css('height', '' + newOffset + 'px');

        if ( !(currentOffset % (fullItemHeight) ) ) {

          // console.log(nodesArray)
          if (negativeDirection) {
            j--;
            if (j < 1) j += itemsNumber; // erase counter to prevent huge numbers
          }

          if (j !== 0) {

            if (negativeDirection) {
              itemsContainer.append($(nodesArray[(j - 1) % itemsNumber]).clone());

              itemsContainer.children().first().remove();

              itemsContainer.css('height', '' + (newOffset - fullItemHeight) + 'px');
            } else {
              itemsContainer.prepend($(nodesArray[j % itemsNumber]).clone());

              itemsContainer.children().last().remove();

              itemsContainer.css('height', '' + (newOffset - fullItemHeight) + 'px');
            }

          } else {

            if (negativeDirection) {
              // appending first node at the end in the first cycle
              itemsContainer.append($(nodesArray[itemsNumber - 1]).clone());
            } else {
              // prepending last node at the beginning in the first cycle
              itemsContainer.prepend($(nodesArray[0]).clone());
            }

          }

          console.log(j)
          if (!negativeDirection) {
            j++;
            if (j % itemsNumber === 1) j = 1; // erase counter to prevent huge numbers
          }


        }

      };

  itemsContainer.css({
    height: itemsContainer.height() + (fullItemHeight) + 'px',
    top: '-' + (fullItemHeight) + 'px'
  });

  // var interval = setInterval(carousel, 30);

  $('.healthy-bar').one('mouseover', function () {
    // clearInterval(interval);
    // console.log(1)
    $('.healthy-bar').on('wheel', function (e) {
      // console.log(e.originalEvent.deltaY);
      var delta = e.originalEvent.deltaY,
          i = 0;

      if (delta > 0) {
        // scroll down
        itemsContainer.css({
          'justify-content': 'flex-end',
          top: '-' + (fullItemHeight) + 'px',
          bottom: 'auto'
        });
        for (i = 0; i < 5; i++) {
          carousel(false);
        }

      } else {
        // scroll up
        itemsContainer.css({
          'justify-content': 'flex-start',
          top: 'auto',
          bottom: '-' + (fullItemHeight) + 'px'
        });
        for (i = 0; i < 5; i++) {
          carousel(true);
        }

      }

    })
    // document.getElementsByClassName('healthy-bar')[0].addEventListener('wheel', function (e) {
    //   console.log(e);
    // })
  });
  $('.healthy-bar').on('mouseleave', function () {
    // $('.healthy-bar').off('wheel');
    // console.log(2)
    // interval = setInterval(carousel, 30);
    // $('.healthy-bar').one('mouseover', function () {
    //   // clearInterval(interval);
    //   // console.log(1)
    //   $('.healthy-bar').on('wheel', function (e) {
    //     // console.log(e);
    //   })
    // });
  });

};

var itemsNumber = $('.healthy-bar-item').length,
    itemsContainer = $('.healthy-bar > div'),
    itemHeight = $('.healthy-bar-item').innerHeight(),
    itemMargin = 6;

if (itemsNumber * (itemMargin * 2 + itemHeight) > window.innerHeight) {

  healthyCarousel();

} else {

  itemsContainer.css({
    top: '50%',
    'margin-top': '-' + itemsContainer.height() / 2 + 'px',
    visibility: 'visible'
  });

}

// NOT USED
// делаем рандомную пульсацию рамки критических элементов
var criticalItemsPulse = function () {

  var i = Math.trunc(Math.random() * 10),
      seedArray = [0, 0, 0, 0, 0, 0, 0, 0, 1, 1];

  if (!seedArray[i]) return;

  var pulseItemIndex = Math.trunc(Math.random() * 4),
      item = $('.red-card')[pulseItemIndex];

  $(item).addClass('pulse-now');
  setTimeout(function () {
    $(item).removeClass('pulse-now');
  }, 2500);

};
// setInterval(criticalItemsPulse, 100);

// функции слайда карточек
var itemSlideOutside = function (item) {
      $(item).removeClass('transporting');
      setTimeout(function () {
        $(item).children().show();
      }, 700);
    },
    itemSlideInside = function (item) {
      setTimeout(function () {
        $(item).addClass('transporting');
      }, 200);``
      $(item).children().hide();
    };

// placing background cards
// if background cards number > 4, grouping and cascading
var displayBackgroundCards = function () {

  $.each($('.warning-area .background-cards'), function (i, val) {

    var backgroundArea = $(val),
        cardsArray = backgroundArea.children(),
        cardsNumber = cardsArray.length,
        cardHeight = cardsArray.first().outerHeight(),
        cardOffset = (backgroundArea.height() - cardHeight) / (cardsNumber - 1);

    $.each(cardsArray, function (i, val) {

      if (cardsNumber > 4) {

        $(val).css({
          top: i * cardOffset + (-1) * (i % 3) * 60 + 'px',
          filter: 'blur(' + (cardsNumber - i) + 'px)',
          left: (-1) * (- (i % 3 - 1) ) * 30 + 'px',
          right: (-1) * (i % 3 - 1) * 30 + 'px'
        });

      } else {

        $(val).css({
          top: i * cardOffset + 'px',
          filter: 'blur(' + (cardsNumber - i) + 'px)',
          left: 0,
          right: 0
        });

      }

    });

  });

};

// rendering problematic items inside card
var renderProblematicItems = function (card) {

  var problematicArea = card.find('.problematic-items'),
      problematicItemsArray = problematicArea.children().clone().toArray().reverse(),
      displayArea = card.find('.problematic-items-display'),
      displayAreaHeight = card.height(),
      displayObject = [];

  /////////////
  var tempArray = [],
      tempHeight = 0,
      tempIterator = 1;

  // grouping items for display inside card, based on available height
  while (problematicItemsArray.length > 0) {

    var localItem = $(problematicArea.children().toArray().reverse()[problematicItemsArray.length - 1]),
        addToObject = function (id, data) {
          displayObject.push({
            id: id,
            data: data,
            height: tempHeight
          });
          tempArray = [];
          tempHeight = 0;
          tempIterator++;
        };

    tempHeight += localItem.outerHeight(true);

    if (tempHeight > displayAreaHeight) {
      tempHeight -= localItem.outerHeight(true);
      // добавляем в объект для отображения
      addToObject(tempIterator, tempArray);
      continue;
    }

    tempArray.push(problematicItemsArray.pop());

  }

  if (tempArray.length) {
    addToObject(tempIterator, tempArray);
  }
  /////////////
  tempIterator = 0;

  var problematicItemsCarousel = function () {

    if (!card.parent().length || card.parent().hasClass('background-cards') || displayObject.length < 2 ) {

      displayArea.css({
        top: '50%',
        'margin-top': '-' + (displayObject[0].height / 2) + 'px',
        display: 'block'
      });

      displayArea.html(displayObject[0].data);

      clearInterval(interval);

    } else {

      setTimeout(function () {
        displayArea.fadeOut();
      }, 2500);

      displayArea.html(displayObject[tempIterator].data);

      displayArea.css({
        top: '50%',
        'margin-top': '-' + (displayObject[tempIterator].height / 2) + 'px'
      });

      displayArea.fadeIn();

      tempIterator++;
      if (tempIterator === displayObject.length) {
        tempIterator = 0;
      }
    }

  };

  problematicItemsCarousel(); // first run goes idle for background cards, because of no interval specified to clear
  var interval = setInterval(problematicItemsCarousel, 3000);

};

displayBackgroundCards();
$.each($('.card'), function (i, val) {
  renderProblematicItems($(val));
});


// cards slide functions
var cardIn = function (card, pastCard) {

      // from foreground to background
      if (pastCard) {
        pastCard.addClass('card-in');
        $('.warning-area .background-cards').prepend(pastCard);

        pastCard.find('.problematic-items-display').css({
          opacity: 1
        });

        setTimeout(function () {

          displayBackgroundCards();

          renderProblematicItems(pastCard);
          pastCard.removeClass('card-in');

        }, 200);
      }

      // from background to foreground
      if (card) {
        card.attr('style', '').addClass('card-in');
        $('.warning-area .foreground-cards').append(card);

        // setTimeout, getting rid of browser css optimizing during reflow
        // 100 === 400ms from slideUp - .3s from css
        setTimeout(function () {

          renderProblematicItems(card);
          card.removeClass('card-in');

        }, 100);
      }

    },
    cardOut = function (card) {

      var pastCard = card.clone();

      card.addClass('card-out');

      if (!newCardFlag) {
        card.slideUp(400, function () {
          card.detach();
          card.removeClass('card-out');
        });

        var newCard = $('.warning-area .background-cards').children().last().clone();

        setTimeout(function () {
          $('.warning-area .background-cards').children().last().detach().removeClass('card-out');
        }, 200);
        $('.warning-area .background-cards').children().last().addClass('card-out');

        cardIn(newCard, pastCard);
      } else {
        setTimeout(function () {
          card.detach();
          card.removeClass('card-out');
          addCard(cardTemplate);
        }, 400);
        newCardFlag--;
        cardIn(null, pastCard);
      }

    };

setInterval(function () {
  var card = $('.warning-area .foreground-cards').children().first();

  cardOut($(card));
}, 6000);

///////////////////////////////////////////////////////////////////
// adding new card

let cardTemplate = nodeCard;
// var cardTemplate = '<div class="red-card card" id="card1">\n' +
//     '                <div class="ill-item-before-diagonal-line"></div>\n' +
//     '                <div class="ill-item-before-horizontal-line"></div>\n' +
//     '                <span class="node-title" style="font-size: 21px; ">reg101.mcntelecom.ru</span>\n' +
//     '                <div class="node-header-info">\n' +
//     '                    <span class="runtime">runtime: <span class="hours">00</span><span class="time-pulse">:</span><span class="minutes">25</span></span> | <span>calls: 5134</span>\n' +
//     '                </div>\n' +
//     '                <div class="problematic-items">\n' +
//     '                    <div class="problematic-item">\n' +
//     '                        <span class="item-name">ApihostRadiusStatus</span>\n' +
//     '                        <br>\n' +
//     '                        <span class="message" title="Apihost received RADIUS request 0 seconds ago from server_id:99">Apihost received RADIUS request 0 seconds ago from server_id:99</span>\n' +
//     '                    </div>\n' +
//     '                    <div class="problematic-item">\n' +
//     '                        <span class="item-name">CallSyncStatus</span>\n' +
//     '                        <br>\n' +
//     '                        <span class="message" title="Delay is 2370 sec">Delay is 2370 sec</span>\n' +
//     '                    </div>\n' +
//     '                    <div class="problematic-item">\n' +
//     '                        <span class="item-name">publicApiWorks</span>\n' +
//     '                        <br>\n' +
//     '                        <span class="message" title="Public API NOT AVAILABLE">Public API NOT AVAILABLE</span>\n' +
//     '                    </div>\n' +
//     '                    <div class="problematic-item">\n' +
//     '                        <span class="item-name">ApihostRadiusStatus</span>\n' +
//     '                        <br>\n' +
//     '                        <span class="message" title="Apihost received RADIUS request 0 seconds ago from server_id:99">Apihost received RADIUS request 0 seconds ago from server_id:99</span>\n' +
//     '                    </div>\n' +
//     '                    <div class="problematic-item">\n' +
//     '                        <span class="item-name">CallSyncStatus</span>\n' +
//     '                        <br>\n' +
//     '                        <span class="message" title="Delay is 2370 sec">Delay is 2370 sec</span>\n' +
//     '                    </div>\n' +
//     '                    <div class="problematic-item">\n' +
//     '                        <span class="item-name">publicApiWorks</span>\n' +
//     '                        <br>\n' +
//     '                        <span class="message" title="Public API NOT AVAILABLE">Public API NOT AVAILABLE</span>\n' +
//     '                    </div>\n' +
//     '                </div>\n' +
//     '                <div class="problematic-items-display"></div>\n' +
//     '            </div>';

var newCardFlag = 0;

var addCard = function (card) {
  $('.warning-area .foreground-cards').prepend(card);
};