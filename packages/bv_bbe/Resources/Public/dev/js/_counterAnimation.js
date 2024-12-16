import { isScrolledIntoView } from "./_helperFunctions";
/**
* initialzie Counter Animation
*/
function initializeCounterAnimation() {
  // Counter (animation)
  if ($('.js-counter').length) {

    let $counter = $('.js-counter'),
      counterView = false;

    function counterInView() {
      if (!counterView) {
        if (isScrolledIntoView($counter)) {
          counterView = true;
          var counterArr = [];
          $('.js-counter')
            .each(function () {
              counterArr.push($(this)
                .html());
            });
          $counter.each(function () {
            $(this)
              .prop('Counter', 0)
              .animate({
                Counter: $(this)
                  .text()
              }, {
                duration: 2500,
                easing: 'swing',
                step: function (now) {
                  if ($(this)
                    .hasClass('js-percent')) {
                    $(this)
                      .text(Math.floor(now) + '%');
                  } else if ($(this)
                    .hasClass('js-k')) {
                    $(this)
                      .text(commaSeparateNumber(Math.floor(now) + 'k'));
                  } else if ($(this)
                    .hasClass('js-plus')) {
                    $(this)
                      .text('+' + Math.floor(now)
                        .toLocaleString('en'));
                  } else if ($(this)
                    .hasClass('js-whole')) {
                    $(this)
                      .text(Math.floor(now));
                  } else {
                    $(this)
                      .text(commaSeparateNumber(Math.floor(now)));
                  }
                },
                complete: function () {
                  if ($(this)
                    .hasClass('js-percent') | $(this)
                      .hasClass('js-k') | $(this)
                        .hasClass('js-plus')) {
                    counterArr.shift();
                  } else if ($(this)
                    .hasClass('js-whole')) {
                    $(this)
                      .text(counterArr.shift());
                  } else {
                    $(this)
                      .text(commaSeparateNumber(counterArr.shift()));
                  }
                }
              });
          });
        }
      }
    }

    counterInView();

    $(window)
      .scroll(function () {
        counterInView();
      });

    function commaSeparateNumber(val) {
      while (/(\d+)(\d{3})/.test(val.toString())) {
        val = val.toString()
          .replace(/(\d+)(\d{3})/, '$1' + ',' + '$2');
      }
      return val;
    }
  }
}


export function initCounterAnimation() {
  initializeCounterAnimation();
}
