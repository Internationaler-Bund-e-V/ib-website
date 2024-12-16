import Swiper from 'swiper/swiper-bundle';
function initializeNewsSlider() {
  const newsSwiper = new Swiper('.Bvbbe-Swiper', {
    slidesPerView: 3,
    spaceBetween: 30,
    loop: true,
    preventInteractionOnTransition: true,
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
    navigation: {
      nextEl: ".swiper-button-next",
      prevEl: ".swiper-button-prev",
    },
    breakpoints: {
      // when window width is >= 320px
      320: {
        slidesPerView: 1,
        spaceBetween: 20
      },
      // when window width is >= 480px
      480: {
        slidesPerView: 2,
        spaceBetween: 30
      },
      // when window width is >= 640px
      640: {
        slidesPerView: 3,
        spaceBetween: 40
      }
    }
  });
}



export function initNewsSlider() {
  initializeNewsSlider();
}