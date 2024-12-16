import { initNavigation } from "./_navigation";
import { initCounterAnimation } from "./_counterAnimation";
import Swiper from 'swiper/swiper-bundle';
import { initNewsSlider } from "./_newsSlider";
import { initChangeImgToBg } from "./_backgroundImage";
import { initObfuscateEmail } from "./_obfuscateEmail";


$(document).ready(function () {

  initNavigation();
  initCounterAnimation();
  initNewsSlider();
  initChangeImgToBg('.js-bg');
  initObfuscateEmail();
  const swiper = new Swiper('.BvbbeHeadSlider', {
    slidesPerView: 1,
    autoplay: {
      delay: 1500,
    },
    effect: 'fade',
    fadeEffect: {
      crossFade: true
    },
    speed: 3000
  });
});

