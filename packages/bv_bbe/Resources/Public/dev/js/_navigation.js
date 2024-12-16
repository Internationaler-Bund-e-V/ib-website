/**
 * initialzie Navigation
 */
function initializeNavigation() {
  console.log("Navigation loaded...");

  // Add dropdown arrow to items with childrens
  $('.menu-item-has-children > a')
    .after('<span class="dropdown-btn"></span>');

  // Header
  if ($('.aheto-header').length) {
    let $body = $('body'),
      $header = $('.aheto-header'),
      $hamburger = $('.hamburger'),
      $hamburgerAside = $('.js-hamburger-aside'),
      $aside = $('.js-aside'),
      $asideClose = $('.js-aside-close'),
      $menu;

    $hamburger.on("click", function (ev) {
      console.log("click....");
      // Make body not scrollable
      if ($(this)
        .hasClass('js-hamburger--body-over')) {
        $body.toggleClass('over-hidden');
      }

      $('html,body')
        .animate({
          scrollTop: $('[class*="nav-wrap"]')
            .offset().top
        },
          'slow');

      // Hamburger animation
      $(this)
        .toggleClass('is-active');

      // Open menu
      $menu = $(this)
        .closest('.aheto-header')
        .find('.js-menu');
      $header.toggleClass('is-open');
      if ($(this)
        .hasClass('is-active')) {
        $menu.slideToggle('fast', function () {
        });
      } else {
        $menu.slideToggle('fast', function () {
          if ('none' == $menu.css('display')) {
            $menu.removeAttr('style');
          }
        });
      }
      $('.js-toggle')
        .slideToggle('fast', function () {
          if ('none' == $(this)
            .css('display')) {
            $(this)
              .removeAttr('style');
          }
        });
    });

    $hamburgerAside.click(function (ev) {
      $aside.toggle(0, function () {
        $(this)
          .toggleClass('js-aside-opened');
      });
    });

    $asideClose.click(function (ev) {
      $aside.toggleClass('js-aside-opened');
      setTimeout(function () {
        $aside.hide(0);
      }, 500);
    });

    // Move logo to the center of menu items
    if ($('.js-center-logo').length) {

      let logoIsMoved = false;

      function moveLogoToMenu() {
        let $logo = $('.js-center-logo');
        let menuLength = $('.aheto-header .main-menu > .menu-item').length;
        if (0 == menuLength % 2) {
          $logo.insertAfter('.aheto-header .main-menu > .menu-item:nth-child(' + Math.floor(menuLength / 2) + ')');
        }
        logoIsMoved = true;
      }

      function moveLogoFromMenu() {
        let $logo = $('.js-center-logo');
        $logo.prependTo('.js-logo-initial');
        logoIsMoved = false;
      }

      function moveLogo() {
        if (1024 < window.innerWidth & !logoIsMoved) {
          moveLogoToMenu();
        }
        if (1024 >= window.innerWidth & logoIsMoved) {
          moveLogoFromMenu();
        }
      }

      moveLogo();

      $(window)
        .resize(function () {
          moveLogo();
        });
    }

    if ($('.js-dropdown-btn').length) {
      let $dropBtn = $('.dropdown-btn');
      $dropBtn.click(function (ev) {
        $(this)
          .toggleClass('is-active');
        $(this)
          .parent()
          .find('> .sub-menu')
          .slideToggle('fast');
      });
    }

    // Shop header
    if ($('.js-shop-header').length) {
      let $shopHeader = $('.js-shop-header'),
        $shopHamburger = $('.js-shop-hamburger'),
        $shopAside = $('.js-shop-aside'),
        $shopAsideClose = $('.js-shop-aside-close'),
        $shopAsideOverlay = $('.js-shop-aside-overlay'),
        $dropBtn = $('.dropdown-btn');

      $shopHamburger.click(function (ev) {
        $shopHeader.toggleClass('is-open');
        $(this)
          .toggleClass('is-active');
        $shopAside.toggle(0)
          .toggleClass('is-open');
        $shopAsideOverlay.toggle(0)
          .toggleClass('is-open');
        $shopHeader.toggleClass('is-open');
      });

      $shopAsideClose.click(function (ev) {
        closeShopAside();
      });

      $shopAsideOverlay.click(function (ev) {
        closeShopAside();
      });

      function closeShopAside() {
        $shopHeader.removeClass('is-open');
        $shopHamburger.removeClass('is-active');
        $shopAside.removeClass('is-open');
        $shopAsideOverlay.removeClass('is-open');
        setTimeout(function () {
          $shopAside.toggle(0);
          $shopAsideOverlay.toggle(0);
        }, 500);
        $shopHeader.removeClass('is-open');
      }

      $dropBtn.click(function (ev) {
        if (1024 >= winH) {
          $(this)
            .removeClass('is-active');
          $(this)
            .parent()
            .find('> .sub-menu')
            .slideToggle('fast');
        }
      });
    }
  }



}

export function initNavigation() {
  initializeNavigation();
}


