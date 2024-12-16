(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";

$(document).ready(function () {
  $(document).foundation(); //slickslider

  $('.SrbSlider').slick({
    dots: true,
    prevArrow: $('.SrbSliderNavigationArrowsPrev'),
    nextArrow: $('.SrbSliderNavigationArrowsNext'),
    appendDots: $('.SrbSliderNavigationDots')
  }); //slickslider news

  $(".SrbNewsListSlider").each(function (index) {
    var newssliderid = $(this).attr('id');
    var slider = $('#' + newssliderid + ' .SrbNewsSlider');
    $(slider).slick({
      infinite: true,
      slidesToShow: 3,
      slidesToScroll: 1,
      dots: true,
      appendDots: $('#' + newssliderid + ' .SrbNewsSliderNavigationDots'),
      prevArrow: $('#' + newssliderid + ' .SrbNewsSliderNavigationArrowsPrev'),
      nextArrow: $('#' + newssliderid + ' .SrbNewsSliderNavigationArrowsNext'),
      responsive: [{
        breakpoint: 769,
        settings: {
          slidesToShow: 2
        }
      }, {
        breakpoint: 640,
        settings: {
          slidesToShow: 1
        }
      }]
    });
  }); //lightbox options

  lightbox.option({
    'albumLabel': "Bild %1 von %2"
  }); //accordion dataprivacy

  $('.ib-collapsible-trigger').on('click', function () {
    var collapsibleID = $(this).data('ibcollapsibleid');
    $("#ib-collapsible-content-" + collapsibleID).slideToggle(300);
    $("#ib-collapsible-" + collapsibleID + " i").toggleClass('fa-chevron-right fa-chevron-down');
  });
});

},{}]},{},[1]);
